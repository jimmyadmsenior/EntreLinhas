<?php
// Arquivo de logout - encerrar a sessão do usuário

// Inicializar a sessão
session_start();

// Destruir todas as variáveis da sessão
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

// Redirecionar para a página de logout personalizada
header("location: ../PAGES/logout.php");
exit;
?>
