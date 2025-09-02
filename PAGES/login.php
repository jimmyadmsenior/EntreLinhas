<?php
// Iniciar sessão
session_start();

// Se o usuário já estiver logado, redirecione para a página principal
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

// Incluir arquivo de configuração PDO
require_once "../config_pdo.php";
// Incluir funções auxiliares PDO
require_once "../pdo_helper.php";

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
        try {
            // Preparar uma declaração select
            $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            
            // Verificar se o e-mail existe, se sim, verificar a senha
            if($stmt->rowCount() == 1){
                // Obter os dados do usuário
                $row = $stmt->fetch();
                $id = $row['id'];
                $nome = $row['nome'];
                $email = $row['email'];
                $hashed_password = $row['senha'];
                $tipo = $row['tipo'];
                
                if(password_verify($senha, $hashed_password)){
                    // Senha está correta, iniciar uma nova sessão
                    session_start();
                    
                    // Armazenar dados em variáveis de sessão
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["nome"] = $nome;
                    $_SESSION["email"] = $email;
                    $_SESSION["tipo"] = $tipo;
                    
                    // Definir cookies para JavaScript localStorage
                    setcookie("userLoggedIn", "true", time() + 86400, "/");
                    setcookie("userName", $nome, time() + 86400, "/");
                    setcookie("userEmail", $email, time() + 86400, "/");
                    setcookie("userType", $tipo, time() + 86400, "/");
                    setcookie("userId", $id, time() + 86400, "/");
                    
                    // Redirecionar usuário com base no tipo de conta
                    if ($tipo === "admin") {
                        header("location: admin_dashboard.php");
                    } else {
                        header("location: ../index.php");
                    }
                } else {
                    // Senha não é válida, exibir mensagem de erro genérica
                    $login_err = "E-mail ou senha inválidos.";
                }
            } else {
                // E-mail não existe, exibir mensagem de erro genérica
                $login_err = "E-mail ou senha inválidos.";
            }
        } catch (PDOException $e) {
            echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            // Para depuração apenas, não use em produção:
            // error_log("Erro no login: " . $e->getMessage());
        }
    }
    
    // Com PDO, não é necessário fechar a conexão explicitamente
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
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="container">
        <div class="form-container fade-in">
            <h1 class="form-title">Entrar no EntreLinhas</h1>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>

            <form id="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'error' : ''; ?>" value="<?php echo $email; ?>">
                    </div>
                    <?php if(!empty($email_err)) { ?>
                        <span class="error-message"><?php echo $email_err; ?></span>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="senha" id="senha" class="form-control <?php echo (!empty($senha_err)) ? 'error' : ''; ?>">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if(!empty($senha_err)) { ?>
                        <span class="error-message"><?php echo $senha_err; ?></span>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <div class="checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Lembrar de mim</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" id="login-btn" class="btn btn-primary btn-full">
                        Entrar
                    </button>
                </div>
                
                <div class="form-links">
                    <a href="forgot_password.php">Esqueceu a senha?</a>
                    <span class="separator">|</span>
                    <a href="register.php">Criar uma conta</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Função para alternar visibilidade da senha
            window.togglePasswordVisibility = function() {
                const passwordInput = document.getElementById('senha');
                const icon = document.querySelector('.password-toggle i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            };
            
            // Mostrar alerta personalizado
            function showAlert(message, type = 'success') {
                const alertBox = document.createElement('div');
                alertBox.className = `alert ${type === 'success' ? 'alert-success' : 'alert-danger'} fade-in`;
                alertBox.innerHTML = message;
                
                const container = document.querySelector('.form-container');
                container.insertBefore(alertBox, document.getElementById('login-form'));
                
                setTimeout(() => {
                    alertBox.classList.add('fade-out');
                    setTimeout(() => alertBox.remove(), 500);
                }, 3000);
            }
            
            // Ajax login
            const loginForm = document.getElementById('login-form');
            const loginBtn = document.getElementById('login-btn');
            
            if (loginForm) {
                loginForm.addEventListener('submit', function(event) {
                    // O formulário será enviado normalmente, não usando AJAX para manter simplicidade
                });
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
