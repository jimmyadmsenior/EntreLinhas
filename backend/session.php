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

// Nota: As funções require_login e require_admin estão no session_helper.php
// Não definimos aqui para evitar duplicação e possíveis conflitos
?>
