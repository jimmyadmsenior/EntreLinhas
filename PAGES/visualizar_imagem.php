<?php
// Incluir configuração do banco de dados
require_once "../backend/config.php";

// Verificar se há um ID na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gerenciar_imagens.php");
    exit;
}

$id = intval($_GET['id']);

// Buscar imagem no banco de dados
$sql = "SELECT id, usuario_id, titulo, descricao, imagem_base64, tipo_mime, data_upload 
        FROM imagens_artigos WHERE id = ?";

$imagem = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $resultado = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($resultado)) {
            $imagem = $row;
            // Construir o data URI completo
            $imagem['data_uri'] = 'data:' . $imagem['tipo_mime'] . ';base64,' . $imagem['imagem_base64'];
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Se a imagem não for encontrada
if (!$imagem) {
    header("Location: gerenciar_imagens.php");
    exit;
}

// Verificar se o usuário tem permissão (se é o dono da imagem)
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $imagem['usuario_id']) {
    // Você pode adaptar esta verificação conforme sua lógica de permissões
    // Por exemplo, permitir que administradores vejam todas as imagens
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
        header("Location: gerenciar_imagens.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($imagem['titulo']); ?> - EntreLinhas</title>
    <meta name="description" content="<?php echo htmlspecialchars($imagem['descricao'] ?? 'Visualização de imagem no EntreLinhas'); ?>">
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
        .image-container {
            margin: 30px auto;
            text-align: center;
        }
        .full-image {
            max-width: 100%;
            max-height: 600px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .image-info {
            margin-top: 20px;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .code-container {
            margin-top: 20px;
            position: relative;
        }
        .code {
            width: 100%;
            height: 100px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
            overflow-y: auto;
            font-size: 0.9rem;
        }
        .copy-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255,255,255,0.8);
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .usage-examples {
            margin-top: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .usage-example {
            padding: 15px;
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .usage-code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9rem;
            overflow-x: auto;
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
        <h1><?php echo htmlspecialchars($imagem['titulo']); ?></h1>
        
        <div class="image-container">
            <img src="<?php echo $imagem['data_uri']; ?>" alt="<?php echo htmlspecialchars($imagem['titulo']); ?>" class="full-image">
        </div>
        
        <div class="image-info">
            <h2>Informações da Imagem</h2>
            <p><strong>Título:</strong> <?php echo htmlspecialchars($imagem['titulo']); ?></p>
            <?php if (!empty($imagem['descricao'])): ?>
                <p><strong>Descrição:</strong> <?php echo htmlspecialchars($imagem['descricao']); ?></p>
            <?php endif; ?>
            <p><strong>Tipo de arquivo:</strong> <?php echo $imagem['tipo_mime']; ?></p>
            <p><strong>Data de upload:</strong> <?php echo date('d/m/Y H:i', strtotime($imagem['data_upload'])); ?></p>
            
            <div class="code-container">
                <h3>Código Base64 (data URI)</h3>
                <button class="copy-btn" id="copy-btn">Copiar</button>
                <div class="code" id="base64-code"><?php echo $imagem['data_uri']; ?></div>
            </div>
            
            <div class="usage-examples">
                <div class="usage-example">
                    <h3>Como usar em HTML</h3>
                    <div class="usage-code">&lt;img src="<?php echo htmlspecialchars($imagem['data_uri'], ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($imagem['titulo'], ENT_QUOTES); ?>"&gt;</div>
                </div>
                
                <div class="usage-example">
                    <h3>Como usar em CSS</h3>
                    <div class="usage-code">background-image: url('<?php echo htmlspecialchars($imagem['data_uri'], ENT_QUOTES); ?>');</div>
                </div>
            </div>
        </div>
        
        <div class="text-center" style="margin-top: 30px;">
            <a href="gerenciar_imagens.php" class="btn btn-secondary">Voltar para Gerenciar Imagens</a>
            <a href="deletar_imagem.php?id=<?php echo $imagem['id']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta imagem?')">Excluir Imagem</a>
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
        // Funcionalidade de copiar código
        const copyBtn = document.getElementById('copy-btn');
        const codeElement = document.getElementById('base64-code');
        
        copyBtn.addEventListener('click', function() {
            // Criar um elemento temporário de texto
            const textarea = document.createElement('textarea');
            textarea.value = codeElement.textContent;
            document.body.appendChild(textarea);
            
            // Selecionar e copiar
            textarea.select();
            document.execCommand('copy');
            
            // Remover o elemento temporário
            document.body.removeChild(textarea);
            
            // Feedback visual
            copyBtn.textContent = 'Copiado!';
            setTimeout(() => {
                copyBtn.textContent = 'Copiar';
            }, 2000);
        });
    });
    </script>
</body>
</html>
