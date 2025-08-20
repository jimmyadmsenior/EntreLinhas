<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado, senão redirecionar para a página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Definir variáveis e inicializar com valores vazios
$titulo = $conteudo = $categoria = "";
$titulo_err = $conteudo_err = $categoria_err = $imagem_err = "";
$upload_status = "";

// Processar dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validar título
    if (empty(trim($_POST["titulo"]))) {
        $titulo_err = "Por favor, insira um título para o artigo.";
    } elseif (strlen(trim($_POST["titulo"])) > 255) {
        $titulo_err = "O título não pode ter mais de 255 caracteres.";
    } else {
        $titulo = trim($_POST["titulo"]);
    }
    
    // Validar conteúdo
    if (empty(trim($_POST["conteudo"]))) {
        $conteudo_err = "Por favor, insira o conteúdo do artigo.";
    } else {
        $conteudo = trim($_POST["conteudo"]);
    }
    
    // Validar categoria
    if (empty(trim($_POST["categoria"]))) {
        $categoria_err = "Por favor, selecione uma categoria.";
    } else {
        $categoria = trim($_POST["categoria"]);
    }
    
    // Processar upload de imagem
    $target_dir = "../assets/images/artigos/";
    
    // Criar diretório se não existir
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imagem = "";
    $upload_ok = true;
    
    if(isset($_FILES["imagem"]) && $_FILES["imagem"]["name"]) {
        $target_file = $target_dir . basename($_FILES["imagem"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Verificar se o arquivo é uma imagem real
        $check = getimagesize($_FILES["imagem"]["tmp_name"]);
        if($check === false) {
            $imagem_err = "O arquivo não é uma imagem válida.";
            $upload_ok = false;
        }
        
        // Verificar o tamanho do arquivo
        if ($_FILES["imagem"]["size"] > 5000000) { // 5MB
            $imagem_err = "O arquivo é muito grande. O tamanho máximo é de 5MB.";
            $upload_ok = false;
        }
        
        // Permitir apenas certos formatos de arquivo
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $imagem_err = "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
            $upload_ok = false;
        }
        
        // Gerar um nome único para a imagem
        $novo_nome = uniqid() . "." . $imageFileType;
        $target_file = $target_dir . $novo_nome;
        
        // Tentar fazer o upload do arquivo
        if ($upload_ok) {
            if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
                $imagem = $novo_nome;
            } else {
                $imagem_err = "Houve um erro ao fazer o upload do arquivo.";
                $upload_ok = false;
            }
        }
    }
    
    // Verificar erros de entrada antes de inserir no banco de dados
    if (empty($titulo_err) && empty($conteudo_err) && empty($categoria_err) && empty($imagem_err)) {
        
        // Prepare uma declaração de inserção
        $sql = "INSERT INTO artigos (titulo, conteudo, categoria, imagem, id_usuario, data_criacao, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pendente')";
         
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variáveis à declaração preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "ssssi", $param_titulo, $param_conteudo, $param_categoria, $param_imagem, $param_id_usuario);
            
            // Definir parâmetros
            $param_titulo = $titulo;
            $param_conteudo = $conteudo;
            $param_categoria = $categoria;
            $param_imagem = $imagem;
            $param_id_usuario = $_SESSION["id"];
            
            // Tentar executar a declaração preparada
            if (mysqli_stmt_execute($stmt)) {
                $artigo_id = mysqli_insert_id($conn);
                
                // Enviar e-mail de notificação para o administrador
                $to = 'jimmycastilho555@gmail.com';
                $subject = 'Novo Artigo Pendente - EntreLinhas';
                
                $message = "
                <html>
                <head>
                    <title>Novo Artigo Pendente</title>
                </head>
                <body>
                    <h2>Um novo artigo foi enviado para aprovação</h2>
                    <p><strong>Título:</strong> {$titulo}</p>
                    <p><strong>Autor:</strong> {$_SESSION['nome']} ({$_SESSION['email']})</p>
                    <p><strong>Categoria:</strong> {$categoria}</p>
                    <p><strong>Data de envio:</strong> " . date("d/m/Y H:i:s") . "</p>
                    <p>Para revisar e aprovar este artigo, acesse o painel de administração.</p>
                    <p><a href='http://seusite.com.br/admin/artigos.php?id={$artigo_id}'>Clique aqui para revisar o artigo</a></p>
                </body>
                </html>
                ";
                
                // Cabeçalhos para envio de e-mail em HTML
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: EntreLinhas <noreply@entrelinhas.com.br>\r\n";
                
                // Tentar enviar e-mail
                mail($to, $subject, $message, $headers);
                
                // Redirecionar para a página de sucesso
                header("location: envio-sucesso.html");
                exit();
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }

            // Fechar declaração
            mysqli_stmt_close($stmt);
        }
    }
    
    // Fechar conexão
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Artigo - EntreLinhas</title>
    <meta name="description" content="Envie seu artigo para o jornal digital EntreLinhas.">
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
        .error {
            border-color: var(--error) !important;
        }
        .error-message {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }
        .form-container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        textarea.form-control {
            min-height: 300px;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            margin-top: 1rem;
            display: none;
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
                <li><a href="sobre.html">Sobre</a></li>
                <li><a href="escola.html">A Escola</a></li>
                <li><a href="contato.html">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                        </button>
                        <div class="dropdown-menu">
                            <a href="perfil.php">Meu Perfil</a>
                            <a href="meus-artigos.php">Meus Artigos</a>
                            <a href="enviar-artigo.php">Enviar Artigo</a>
                            <a href="../backend/logout.php">Sair</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Entrar</a>
                    <a href="cadastro.php" class="btn btn-primary">Cadastrar</a>
                <?php endif; ?>
                
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
        <div class="form-container fade-in">
            <h1 class="form-title">Enviar Novo Artigo</h1>
            <p class="text-center mb-4">Compartilhe seu conhecimento, sua opinião ou suas histórias com a comunidade EntreLinhas.</p>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="article-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título do Artigo</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo $titulo; ?>" class="<?php echo (!empty($titulo_err)) ? 'error' : ''; ?>" required>
                    <?php if (!empty($titulo_err)) { ?>
                        <div class="error-message"><?php echo $titulo_err; ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" class="<?php echo (!empty($categoria_err)) ? 'error' : ''; ?>" required>
                        <option value="" <?php echo (empty($categoria)) ? 'selected' : ''; ?> disabled>Selecione uma categoria</option>
                        <option value="Educação" <?php echo ($categoria == "Educação") ? 'selected' : ''; ?>>Educação</option>
                        <option value="Cultura" <?php echo ($categoria == "Cultura") ? 'selected' : ''; ?>>Cultura</option>
                        <option value="Esportes" <?php echo ($categoria == "Esportes") ? 'selected' : ''; ?>>Esportes</option>
                        <option value="Ciência" <?php echo ($categoria == "Ciência") ? 'selected' : ''; ?>>Ciência</option>
                        <option value="Tecnologia" <?php echo ($categoria == "Tecnologia") ? 'selected' : ''; ?>>Tecnologia</option>
                        <option value="Saúde" <?php echo ($categoria == "Saúde") ? 'selected' : ''; ?>>Saúde</option>
                        <option value="Meio Ambiente" <?php echo ($categoria == "Meio Ambiente") ? 'selected' : ''; ?>>Meio Ambiente</option>
                        <option value="Política" <?php echo ($categoria == "Política") ? 'selected' : ''; ?>>Política</option>
                        <option value="Opinião" <?php echo ($categoria == "Opinião") ? 'selected' : ''; ?>>Opinião</option>
                    </select>
                    <?php if (!empty($categoria_err)) { ?>
                        <div class="error-message"><?php echo $categoria_err; ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label for="imagem">Imagem de Destaque (Opcional)</label>
                    <input type="file" id="imagem" name="imagem" accept="image/*" class="<?php echo (!empty($imagem_err)) ? 'error' : ''; ?>">
                    <small>Tamanho máximo: 5MB. Formatos aceitos: JPG, JPEG, PNG, GIF.</small>
                    <?php if (!empty($imagem_err)) { ?>
                        <div class="error-message"><?php echo $imagem_err; ?></div>
                    <?php } ?>
                    <img id="imagePreview" class="image-preview" src="#" alt="Prévia da imagem">
                </div>
                
                <div class="form-group">
                    <label for="conteudo">Conteúdo do Artigo</label>
                    <textarea id="conteudo" name="conteudo" class="form-control <?php echo (!empty($conteudo_err)) ? 'error' : ''; ?>" required><?php echo $conteudo; ?></textarea>
                    <?php if (!empty($conteudo_err)) { ?>
                        <div class="error-message"><?php echo $conteudo_err; ?></div>
                    <?php } ?>
                    <small>Dica: Você pode formatar seu texto usando marcações HTML básicas.</small>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="terms" name="terms" required>
                        Confirmo que este conteúdo é original ou tenho permissão para publicá-lo, e concordo com os <a href="termos.html" target="_blank">termos de uso</a>.
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">Enviar para Aprovação</button>
            </form>
            
            <div class="mt-4">
                <p class="text-center info-text">
                    <i class="fas fa-info-circle"></i> Seu artigo será revisado por nossa equipe antes de ser publicado.
                </p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>EntreLinhas</h3>
                <p>Um jornal digital colaborativo onde vozes diversas se encontram para compartilhar conhecimento, histórias e experiências.</p>
            </div>
            
            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="index.html">Início</a></li>
                    <li><a href="artigos.html">Artigos</a></li>
                    <li><a href="sobre.html">Sobre</a></li>
                    <li><a href="escola.html">A Escola</a></li>
                    <li><a href="contato.html">Contato</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contato</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-envelope"></i> jimmycastilho555@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Av. Marechal Rondon, 3000 - Jardim Bandeirantes, Salto - SP</li>
                    <li><i class="fas fa-phone"></i> (11) 4029-1234</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script>
        // Prévia da imagem
        document.getElementById('imagem').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const imagePreview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    </script>
</body>
</html>
