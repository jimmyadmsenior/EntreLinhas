<?php
// Incluir arquivo de gerenciamento de sessões
require_once "../backend/session.php";

// Verificar o status do login
$status = [
    "logged_in" => is_logged_in(),
    "is_admin" => is_admin(),
    "user_name" => get_user_name(),
    "user_email" => get_user_email(),
    "user_id" => get_user_id()
];

// Enviar resposta JSON
header('Content-Type: application/json');
echo json_encode($status);
?>
