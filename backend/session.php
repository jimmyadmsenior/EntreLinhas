<?php
// Este arquivo será incluído em todas as páginas para gerenciar sessões

// Iniciar a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de sincronização entre localStorage e sessões PHP
require_once dirname(__FILE__) . "/sync_login.php";

// Funções de gerenciamento de sessão
function is_logged_in() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

function is_admin() {
    return is_logged_in() && isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "admin";
}

function get_user_name() {
    return $_SESSION["nome"] ?? "";
}

function get_user_email() {
    return $_SESSION["email"] ?? "";
}

function get_user_id() {
    return $_SESSION["id"] ?? 0;
}

// Redirecionar se não estiver logado (para páginas que exigem login)
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

// Redirecionar se não for administrador (para páginas de administração)
function require_admin() {
    if (!is_admin()) {
        header("Location: index.php?erro=acesso_negado");
        exit;
    }
}
?>
