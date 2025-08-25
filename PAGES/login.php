<?php
// Iniciar sessão
session_start();

// Se o usuário já estiver logado, redirecione para a página principal
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
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
        $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
        
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
                    mysqli_stmt_bind_result($stmt, $id, $nome, $email, $hashed_password, $tipo);
                    
                    if(mysqli_stmt_fetch($stmt)){
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
            
            <form id="login-form">
                <div class="alert hidden" id="alert-container"></div>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
                    <div class="error-message" id="email-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                    <div class="error-message" id="senha-error"></div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="remember" name="remember">
                        Lembrar de mim
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-full" id="login-btn">Entrar</button>
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
    <!-- Script personalizado para a página de login -->
    <script src="../assets/js/login-page.js"></script>
    <link rel="stylesheet" href="../assets/css/alerts.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('login-form');
            const alertContainer = document.getElementById('alert-container');
            const loginBtn = document.getElementById('login-btn');
            
            function showAlert(message, type) {
                alertContainer.textContent = message;
                alertContainer.className = `alert ${type}`;
                
                // Scroll to alert
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Hide after 5 seconds
                setTimeout(() => {
                    alertContainer.className = 'alert hidden';
                }, 5000);
            }
            
            function clearFormErrors() {
                document.getElementById('email-error').textContent = '';
                document.getElementById('senha-error').textContent = '';
            }
            
            if (loginForm) {
                loginForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    clearFormErrors();
                    
                    // Obter dados do formulário
                    const formData = new FormData(loginForm);
                    formData.set('senha', document.getElementById('senha').value);
                    
                    // Alterar estado do botão
                    loginBtn.disabled = true;
                    loginBtn.textContent = 'Entrando...';
                    
                    // Fazer requisição AJAX
                    fetch('../backend/process_login.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Login bem-sucedido
                            showAlert(data.message || 'Login realizado com sucesso!', 'success');
                            
                            // Salvar dados do usuário no localStorage
                            // Criar objeto com os dados do usuário para o localStorage
                            const userData = {
                                nome: data.user_name || '',
                                email: data.user_email || '',
                                tipo: data.user_type || '',
                                id: data.user_id || ''
                            };
                            
                            // Chamar a função do script login-page.js
                            if (window.saveUserData) {
                                window.saveUserData(userData);
                            }
                            
                            // Redirecionar após um breve delay
                            setTimeout(() => {
                                window.location.href = data.redirect || 'index.html';
                            }, 1000);
                        } else {
                            // Login falhou
                            showAlert(data.message || 'Erro ao realizar login', 'error');
                            loginBtn.disabled = false;
                            loginBtn.textContent = 'Entrar';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showAlert('Erro ao conectar com o servidor', 'error');
                        loginBtn.disabled = false;
                        loginBtn.textContent = 'Entrar';
                    });
                });
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
