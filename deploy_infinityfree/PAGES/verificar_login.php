<?php
// Iniciar sessão
session_start();

// Definir cabeçalho para JSON
header('Content-Type: application/json');

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Preparar resposta
$resposta = [
    'logado' => $usuario_logado,
    'dados' => []
];

// Se estiver logado, adicionar dados do usuário
if ($usuario_logado) {
    $resposta['dados'] = [
        'id' => $_SESSION["id"],
        'nome' => $_SESSION["nome"],
        'email' => $_SESSION["email"],
        'tipo' => $_SESSION["tipo"]
    ];
}

// Enviar resposta
echo json_encode($resposta);
?>
