<?php
// Iniciar sessão
session_start();

// Se o usuário já estiver logado, redirecione para a página principal
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.html");
    exit;
}

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Definir variáveis e inicializar com valores vazios
$email = $senha = "";
$email_err = $senha_err = $login_err = "";

// Processar dados do formulário quando o formulário é enviado
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Verificar se o e-mail está vazio
    if(empty(trim($_POST["email"]))){
        $email_err = "Por favor, insira seu e-mail.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Verificar se a senha está vazia
    if(empty(trim($_POST["senha"]))){
        $senha_err = "Por favor, insira sua senha.";
    } else{
        $senha = trim($_POST["senha"]);
    }
    
    // Validar credenciais
    if(empty($email_err) && empty($senha_err)){
        // Preparar uma declaração select
        $sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Vincular variáveis à declaração preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Definir parâmetros
            $param_email = $email;
            
            // Tentar executar a declaração preparada
            if(mysqli_stmt_execute($stmt)){
                // Armazenar resultado
                mysqli_stmt_store_result($stmt);
                
                // Verificar se o e-mail existe, se sim, verificar a senha
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Vincular variáveis de resultado
                    mysqli_stmt_bind_result($stmt, $id, $nome, $email, $hashed_password);
                    
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($senha, $hashed_password)){
                            // Senha está correta, iniciar uma nova sessão
                            session_start();
                            
                            // Armazenar dados em variáveis de sessão
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nome"] = $nome;
                            $_SESSION["email"] = $email;                            
                            
                            // Redirecionar usuário para página de boas-vindas
                            header("location: index.html");
                        } else{
                            // Senha não é válida, exibir mensagem de erro genérica
                            $login_err = "E-mail ou senha inválidos.";
                        }
                    }
                } else{
                    // E-mail não existe, exibir mensagem de erro genérica
                    $login_err = "E-mail ou senha inválidos.";
                }
            } else{
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
    <title>Login - EntreLinhas</title>
    <meta name="description" content="Faça login no EntreLinhas para acessar sua conta e gerenciar seus artigos.">
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
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
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
            <h1 class="form-title">Entrar no EntreLinhas</h1>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="login-form">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="<?php echo (!empty($email_err)) ? 'error' : ''; ?>">
                    <?php if (!empty($email_err)) { ?>
                        <div class="error-message"><?php echo $email_err; ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="<?php echo (!empty($senha_err)) ? 'error' : ''; ?>">
                    <?php if (!empty($senha_err)) { ?>
                        <div class="error-message"><?php echo $senha_err; ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="remember" name="remember">
                        Lembrar de mim
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">Entrar</button>
            </form>
            
            <p class="text-center mt-3">
                <a href="esqueci-senha.php">Esqueci minha senha</a>
            </p>
            
            <p class="text-center mt-1">
                Não tem uma conta? <a href="cadastro.php">Cadastre-se agora</a>
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
</body>
</html>
