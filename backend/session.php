<?php
// Este arquivo será incluído em todas as páginas para gerenciar sessões
// Agora usa o session_helper.php para garantir consistência

// Incluir o helper de sessão
require_once dirname(__FILE__) . "/session_helper.php";

// Incluir arquivo de sincronização entre localStorage e sessões PHP
require_once dirname(__FILE__) . "/sync_login.php";

// Nota: As funções is_logged_in, is_admin, require_login e require_admin
// agora são fornecidas pelo session_helper.php

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
