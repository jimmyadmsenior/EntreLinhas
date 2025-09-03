<?php
/**
 * Session Helper - Gerencia sessões PHP evitando iniciar múltiplas vezes
 * 
 * Evita o aviso "Notice: session_start(): Ignoring session_start() because a session is already active"
 * ao iniciar a sessão apenas quando necessário.
 */

// Função para iniciar sessão de forma segura
function session_start_safe() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Iniciar a sessão automaticamente quando este arquivo for incluído
session_start_safe();

// Função para verificar se o usuário está logado
function is_logged_in() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Função para verificar se o usuário é um administrador
function is_admin() {
    return is_logged_in() && isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "admin";
}

// Função para redirecionar se o usuário não estiver logado
function require_login($redirect_to = "login.php") {
    if (!is_logged_in()) {
        header("Location: $redirect_to");
        exit;
    }
}

// Função para redirecionar se o usuário não for administrador
function require_admin($redirect_to = "index.php") {
    if (!is_admin()) {
        header("Location: $redirect_to");
        exit;
    }
}
?>
