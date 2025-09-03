<?php
// Processa a redefinição de senha
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
$nova_senha = $confirmar_senha = "";
$nova_senha_err = $confirmar_senha_err = "";
$message = "";

// Verificar se os parâmetros de email e token existem
if(!isset($_GET["email"]) || !isset($_GET["token"])){
    header("location: recuperar-senha.php");
    exit;
}

$email = $_GET["email"];
$token = $_GET["token"];

// Verificar se o token é válido
if(!validarTokenRecuperacao($conn, $email, $token)){
    $message = "Link inválido ou expirado. Por favor, solicite uma nova recuperação de senha.";
} else {
    // Processando dados do formulário quando o formulário é enviado
    if($_SERVER["REQUEST_METHOD"] == "POST"){
     
        // Validar senha
        if(empty(trim($_POST["nova_senha"]))){
            $nova_senha_err = "Por favor, informe uma nova senha.";     
        } elseif(strlen(trim($_POST["nova_senha"])) < 6){
            $nova_senha_err = "A senha deve ter pelo menos 6 caracteres.";
        } else{
            $nova_senha = trim($_POST["nova_senha"]);
        }
        
        // Validar confirmação de senha
        if(empty(trim($_POST["confirmar_senha"]))){
            $confirmar_senha_err = "Por favor, confirme a senha.";     
        } else{
            $confirmar_senha = trim($_POST["confirmar_senha"]);
            if(empty($nova_senha_err) && ($nova_senha != $confirmar_senha)){
                $confirmar_senha_err = "As senhas não coincidem.";
            }
        }
        
        // Verificar erros de entrada antes de atualizar a senha
        if(empty($nova_senha_err) && empty($confirmar_senha_err)){
            // Redefinir a senha
            $resultado = redefinirSenha($conn, $email, $token, $nova_senha);
            
            if($resultado['status']){
                // Senha redefinida com sucesso
                $message = $resultado['mensagem'];
                
                // Redirecionar para a página de login após 3 segundos
                header("refresh:3;url=login.php");
            } else {
                $message = $resultado['mensagem'];
            }
        }
    }
}

// Fechar conexão
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - EntreLinhas</title>
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
            <h2>Redefinir Senha</h2>
            
            <?php 
            if(!empty($message)){
                if(strpos($message, "sucesso") !== false) {
                    echo '<div class="alert alert-success">' . $message . '</div>';
                    echo '<div class="links"><p>Você será redirecionado para a página de login...</p></div>';
                } else {
                    echo '<div class="alert alert-danger">' . $message . '</div>';
                    
                    if(strpos($message, "inválido") !== false || strpos($message, "expirado") !== false) {
                        echo '<div class="links">
                                <p><a href="recuperar-senha.php">Solicitar nova recuperação de senha</a></p>
                                <p><a href="login.php">Voltar para o login</a></p>
                            </div>';
                    }
                }
            } else { ?>
                <p>Por favor, digite sua nova senha.</p>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?email=' . $email . '&token=' . $token; ?>" method="post">
                    <div class="form-group">
                        <label>Nova senha</label>
                        <input type="password" name="nova_senha" class="form-control <?php echo (!empty($nova_senha_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nova_senha; ?>">
                        <span class="help-block"><?php echo $nova_senha_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Confirmar nova senha</label>
                        <input type="password" name="confirmar_senha" class="form-control <?php echo (!empty($confirmar_senha_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirmar_senha; ?>">
                        <span class="help-block"><?php echo $confirmar_senha_err; ?></span>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn-primary" value="Redefinir">
                    </div>
                </form>
            <?php } ?>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?php echo date("Y"); ?> EntreLinhas - O jornal da SESI Salto. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
