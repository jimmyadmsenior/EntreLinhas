<?php
// Incluir configuração do banco de dados
require_once "../backend/config.php";
require_once "../backend/funcoes_base64.php";

// Verificar se está logado (implementar sua lógica de autenticação aqui)
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirecionar para a página de login
    header("Location: login.html");
    exit;
}

// Processar o formulário
$mensagem = '';
$erro = '';
$imagem_preview = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados do formulário
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    
    // Validar dados
    if (empty($titulo)) {
        $erro = "Por favor, informe um título para a imagem";
    } elseif (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== 0) {
        $erro = "Por favor, selecione uma imagem válida";
    } else {
        // Processar imagem
        $arquivo = $_FILES['imagem'];
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tamanho_maximo = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($arquivo['type'], $tipos_permitidos)) {
            $erro = "Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WebP.";
        } elseif ($arquivo['size'] > $tamanho_maximo) {
            $erro = "A imagem é muito grande. Tamanho máximo: 2MB";
        } else {
            // Converter para Base64
            $base64 = base64_encode(file_get_contents($arquivo['tmp_name']));
            $data_uri = 'data:' . $arquivo['type'] . ';base64,' . $base64;
            
            // Salvar no banco de dados
            $sql = "INSERT INTO imagens_artigos (usuario_id, titulo, descricao, imagem_base64, tipo_mime, data_upload) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                $usuario_id = $_SESSION['user_id'];
                
                // Vincular parâmetros
                mysqli_stmt_bind_param(
                    $stmt, 
                    "issss", 
                    $usuario_id, 
                    $titulo, 
                    $descricao, 
                    $base64, // Salva apenas o código base64, sem o prefixo data:
                    $arquivo['type']
                );
                
                if (mysqli_stmt_execute($stmt)) {
                    $mensagem = "Imagem salva com sucesso!";
                    $imagem_preview = $data_uri;
                    
                    // Limpar formulário
                    $titulo = $descricao = '';
                } else {
                    $erro = "Erro ao salvar a imagem: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $erro = "Erro na preparação da consulta: " . mysqli_error($conn);
            }
        }
    }
}

// Listar imagens do usuário
$imagens = [];
$sql = "SELECT id, titulo, descricao, CONCAT('data:', tipo_mime, ';base64,', SUBSTRING(imagem_base64, 1, 100), '...') AS preview, 
        data_upload FROM imagens_artigos WHERE usuario_id = ? ORDER BY data_upload DESC";

if ($stmt = mysqli_prepare($conn, $sql)) {
    $usuario_id = $_SESSION['user_id'];
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $resultado = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($resultado)) {
            $imagens[] = $row;
        }
    }
    
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Imagens - EntreLinhas</title>
    <meta name="description" content="Gerencie suas imagens para artigos no EntreLinhas">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .image-preview {
            max-width: 300px;
            max-height: 200px;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .image-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .image-card-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .image-card-body {
            padding: 15px;
        }
        .image-card-title {
            margin: 0;
            font-size: 1.2rem;
        }
        .image-card-footer {
            padding: 10px 15px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .image-thumb {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        .tab-btn.active {
            border-bottom: 3px solid var(--primary);
            color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.html">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.html">Início</a></li>
                <li><a href="artigos.html">Artigos</a></li>
                <li><a href="escola.html">A Escola</a></li>
                <li><a href="contato.html">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar modo escuro">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <h1>Gerenciar Imagens</h1>
        
        <div class="tabs">
            <button class="tab-btn active" data-tab="upload">Enviar Nova Imagem</button>
            <button class="tab-btn" data-tab="library">Biblioteca de Imagens</button>
        </div>
        
        <div id="upload" class="tab-content active">
            <?php if (!empty($mensagem)): ?>
                <div class="alert success">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($erro)): ?>
                <div class="alert error">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título da Imagem</label>
                    <input type="text" id="titulo" name="titulo" required value="<?php echo htmlspecialchars($titulo ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição (opcional)</label>
                    <textarea id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars($descricao ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagem">Selecione uma imagem (máx. 2MB)</label>
                    <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp" required>
                    <small>Formatos permitidos: JPEG, PNG, GIF, WebP</small>
                </div>
                
                <?php if (!empty($imagem_preview)): ?>
                    <div class="text-center">
                        <h3>Pré-visualização</h3>
                        <img src="<?php echo $imagem_preview; ?>" alt="Imagem enviada" class="image-preview">
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary">Salvar Imagem</button>
            </form>
        </div>
        
        <div id="library" class="tab-content">
            <h2>Suas Imagens</h2>
            
            <?php if (empty($imagens)): ?>
                <p>Você ainda não enviou nenhuma imagem.</p>
            <?php else: ?>
                <p>Total de imagens: <?php echo count($imagens); ?></p>
                
                <div class="images-grid">
                    <?php foreach ($imagens as $img): ?>
                        <div class="image-card">
                            <div class="image-card-header">
                                <h3 class="image-card-title"><?php echo htmlspecialchars($img['titulo']); ?></h3>
                            </div>
                            
                            <img src="<?php echo $img['preview']; ?>" alt="<?php echo htmlspecialchars($img['titulo']); ?>" class="image-thumb">
                            
                            <div class="image-card-body">
                                <p><?php echo !empty($img['descricao']) ? htmlspecialchars($img['descricao']) : 'Sem descrição'; ?></p>
                                <p><small>Enviada em: <?php echo date('d/m/Y H:i', strtotime($img['data_upload'])); ?></small></p>
                            </div>
                            
                            <div class="image-card-footer">
                                <a href="visualizar_imagem.php?id=<?php echo $img['id']; ?>" class="btn btn-sm btn-secondary">Ver</a>
                                <a href="deletar_imagem.php?id=<?php echo $img['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta imagem?')">Excluir</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-section">
            <h3>EntreLinhas</h3>
            <p>Um jornal digital colaborativo onde vozes diversas se encontram para compartilhar conhecimento, histórias e experiências.</p>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Funcionalidade das abas
        const tabs = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remover active de todas as abas
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Adicionar active à aba clicada
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Preview da imagem
        const fileInput = document.getElementById('imagem');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Criar ou atualizar preview
                        let preview = document.querySelector('.image-preview');
                        if (!preview) {
                            const div = document.createElement('div');
                            div.className = 'text-center';
                            div.innerHTML = `
                                <h3>Pré-visualização</h3>
                                <img src="${e.target.result}" alt="Pré-visualização" class="image-preview">
                            `;
                            fileInput.parentNode.after(div);
                        } else {
                            preview.src = e.target.result;
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
    </script>
</body>
</html>
