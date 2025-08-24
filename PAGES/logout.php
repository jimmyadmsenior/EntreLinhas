<?php
// Inicializar a sessão
session_start();
 
// Desativar todas as variáveis da sessão
$_SESSION = array();

// Se for necessário cancelar o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
 
// Destruir a sessão
session_destroy();

// Limpar cookies de autenticação
setcookie("userLoggedIn", "", time() - 3600, "/");
setcookie("userName", "", time() - 3600, "/");
setcookie("userEmail", "", time() - 3600, "/");
setcookie("userType", "", time() - 3600, "/");
setcookie("userId", "", time() - 3600, "/");
setcookie("php_auth", "", time() - 3600, "/");

// Adicionar script para limpar localStorage também
echo "<script>
    if (window.localStorage) {
        localStorage.removeItem('userLoggedIn');
        localStorage.removeItem('userName');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('userType');
        localStorage.removeItem('userId');
    }
</script>";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saindo... - EntreLinhas</title>
    <meta http-equiv="refresh" content="2;url=../index.php">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }
        .logout-container {
            max-width: 400px;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h1>Saindo do EntreLinhas</h1>
        <div class="loader"></div>
        <p>Você está sendo desconectado do sistema...</p>
        <p>Redirecionando para a página inicial em instantes.</p>
    </div>

    <script src="../assets/js/auth.js"></script>
    <script>
        // Limpar dados do usuário do localStorage
        document.addEventListener('DOMContentLoaded', function() {
            clearUserData();
        });
    </script>
</body>
</html>
