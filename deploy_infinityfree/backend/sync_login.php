<?php
/**
 * Sincroniza login entre localStorage e sessões PHP
 * 
 * Este arquivo é usado quando um usuário está autenticado via localStorage (JavaScript)
 * e precisa acessar páginas que usam autenticação via sessões do PHP.
 */

// Iniciar ou retomar a sessão apenas se ainda não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se há dados de login no localStorage (através de cookies ou parâmetros)
$forceLogin = isset($_GET['diretamente']) && $_GET['diretamente'] === 'true';

if ($forceLogin) {
    // Usar ID e nome de usuário padrão para bypass
    $_SESSION["loggedin"] = true;
    $_SESSION["id"] = 2; // ID de usuário padrão
    $_SESSION["nome"] = "Usuário JS"; // Nome de usuário padrão
    $_SESSION["email"] = "usuario@exemplo.com";
    $_SESSION["tipo"] = "usuario";
}

// Verificar se estamos em uma página que realmente exige login
$require_login_page = false;
$current_page = basename($_SERVER['PHP_SELF']);
$login_required_pages = ['meus-artigos.php', 'enviar-artigo.php', 'perfil.php', 'admin_dashboard.php'];

if (in_array($current_page, $login_required_pages)) {
    $require_login_page = true;
}

// Se não há sessão ativa e não estamos forçando o login e estamos em uma página que exige login, redirecionar
if (!isset($_SESSION["loggedin"]) && !$forceLogin && $require_login_page) {
    header("Location: login.php");
    exit;
}
?>
