<?php
// Arquivo de formulário para recuperação de senha

// Inicializar a sessão
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Definir variáveis e inicializar com valores vazios
$email = "";
$email_err = "";
$success_msg = "";

// Processar dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validar email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, insira um e-mail.";
    } else {
        $email = trim($_POST["email"]);
        
        // Verificar se o email existe
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variáveis à declaração preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Definir parâmetros
            $param_email = $email;
            
            // Tentar executar a declaração preparada
            if (mysqli_stmt_execute($stmt)) {
                // Armazenar resultado
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Gerar token de recuperação
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Salvar token no banco de dados
                    $update_sql = "UPDATE usuarios SET reset_token = ?, reset_expiry = ? WHERE email = ?";
                    
                    if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                        // Vincular variáveis à declaração preparada como parâmetros
                        mysqli_stmt_bind_param($update_stmt, "sss", $token, $expiry, $email);
                        
                        // Tentar executar a declaração preparada
                        if (mysqli_stmt_execute($update_stmt)) {
                            // Enviar email de recuperação
                            $reset_link = "http://seusite.com.br/PAGES/redefinir-senha.php?email=" . urlencode($email) . "&token=" . $token;
                            
                            $to = $email;
                            $subject = "Recuperação de Senha - EntreLinhas";
                            
                            $message = "
                            <html>
                            <head>
                                <title>Recuperação de Senha</title>
                            </head>
                            <body>
                                <h2>Recuperação de Senha - EntreLinhas</h2>
                                <p>Recebemos uma solicitação para redefinir sua senha no EntreLinhas.</p>
                                <p>Se você não fez esta solicitação, ignore este e-mail.</p>
                                <p>Para redefinir sua senha, clique no link abaixo:</p>
                                <p><a href='{$reset_link}'>{$reset_link}</a></p>
                                <p>Este link é válido por 1 hora.</p>
                                <p>Atenciosamente,<br>Equipe EntreLinhas</p>
                            </body>
                            </html>
                            ";
                            
                            // Cabeçalhos para envio de e-mail em HTML
                            $headers = "MIME-Version: 1.0\r\n";
                            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                            $headers .= "From: EntreLinhas <noreply@entrelinhas.com.br>\r\n";
                            
                            // Tentar enviar e-mail
                            if (mail($to, $subject, $message, $headers)) {
                                $success_msg = "Um e-mail com instruções para recuperar sua senha foi enviado para " . $email;
                            } else {
                                $email_err = "Não foi possível enviar o e-mail de recuperação. Tente novamente mais tarde.";
                            }
                        } else {
                            $email_err = "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                        }
                        
                        mysqli_stmt_close($update_stmt);
                    }
                } else {
                    $email_err = "Não foi encontrada nenhuma conta com este endereço de e-mail.";
                }
            } else {
                $email_err = "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            
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
    <title>Recuperar Senha - EntreLinhas</title>
    <meta name="description" content="Recupere sua senha do EntreLinhas.">
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
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
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
                <a href="cadastro.php" class="btn btn-secondary">Cadastrar</a>
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
            <h1 class="form-title">Recuperar Senha</h1>
            
            <?php 
            if (!empty($success_msg)) {
                echo '<div class="alert alert-success">' . $success_msg . '</div>';
            }
            ?>
            
            <p class="text-center mb-4">
                Digite seu e-mail cadastrado e enviaremos instruções para recuperar sua senha.
            </p>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="recovery-form">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="<?php echo (!empty($email_err)) ? 'error' : ''; ?>">
                    <?php if (!empty($email_err)) { ?>
                        <div class="error-message"><?php echo $email_err; ?></div>
                    <?php } ?>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">Enviar Link de Recuperação</button>
            </form>
            
            <p class="text-center mt-3">
                <a href="login.php">Voltar para o Login</a>
            </p>
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
                    <li><i class="fas fa-map-marker-alt"></i> Rua Israel, 100 - Jardim Panorama, Salto - SP</li>
                    <li><i class="fas fa-phone"></i> (11) 4029-8635</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
