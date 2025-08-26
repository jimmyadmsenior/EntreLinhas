<?php
// Processa a recuperação de senha
session_start();

// Verificar se o usuário já está logado
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: ../index.php");
    exit;
}

// Incluir arquivo de configuração
require_once "../backend/config.php";
require_once "../backend/usuarios.php";

// Definir variáveis e inicializar com valores vazios
$email = "";
$email_err = "";
$message = "";

// Processando dados do formulário quando o formulário é enviado
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validar email
    if(empty(trim($_POST["email"]))){
        $email_err = "Por favor, informe seu e-mail.";
    } else{
        $email = trim($_POST["email"]);
        
        // Verificar se o formato do email é válido
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $email_err = "Formato de e-mail inválido.";
        }
    }
    
    // Verificar erros de entrada antes de enviar o email de recuperação
    if(empty($email_err)){
        // Gerar token de recuperação e enviar email
        $resultado = gerarTokenRecuperacao($conn, $email);
        $message = $resultado['mensagem'];
    }
    
    // Fechar conexão
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - EntreLinhas</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-group .help-block {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .btn-primary {
            background-color: #000;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #333;
        }
        
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .links {
            margin-top: 20px;
            text-align: center;
        }
        
        .links a {
            color: #000;
            margin: 0 10px;
            text-decoration: none;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <h1>EntreLinhas</h1>
            <p>O jornal da SESI Salto</p>
        </div>
        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="escola.html">Nossa Escola</a></li>
                <li><a href="conhecimentos.html">Conhecimentos</a></li>
                <li><a href="conselho-classe.html">Conselho de Classe</a></li>
                <?php if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="registro.php">Cadastre-se</a></li>
                <?php else: ?>
                    <li><a href="perfil.php">Meu Perfil</a></li>
                    <li><a href="enviar-artigo.php">Enviar Artigo</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="form-container">
            <h2>Recuperar Senha</h2>
            <p>Informe o e-mail associado à sua conta para receber um link de recuperação de senha.</p>

            <?php 
            if(!empty($message)){
                if(strpos($message, "sucesso") !== false) {
                    echo '<div class="alert alert-success">' . $message . '</div>';
                } else {
                    echo '<div class="alert alert-danger">' . $message . '</div>';
                }
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="help-block"><?php echo $email_err; ?></span>
                </div>    
                <div class="form-group">
                    <input type="submit" class="btn-primary" value="Enviar">
                </div>
                <div class="links">
                    <p>Lembrou sua senha? <a href="login.php">Voltar para o login</a>.</p>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?php echo date("Y"); ?> EntreLinhas - O jornal da SESI Salto. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
