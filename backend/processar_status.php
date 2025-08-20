<?php
// Arquivo para processar alterações de status de artigos
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['email']) || $_SESSION['email'] !== ADMIN_EMAIL) {
    header("Location: ../PAGES/login.php");
    exit;
}

// Incluir arquivos necessários
require_once 'config.php';
require_once 'artigos.php';

// Verificar se os dados necessários foram enviados
if (!isset($_POST['artigo_id']) || !isset($_POST['status'])) {
    $_SESSION['mensagem'] = "Dados incompletos para processar o status.";
    $_SESSION['tipo_mensagem'] = 'danger';
    header("Location: ../PAGES/admin.php");
    exit;
}

$artigo_id = intval($_POST['artigo_id']);
$status = $_POST['status'];

// Validar o status
if (!in_array($status, ['pendente', 'aprovado', 'rejeitado'])) {
    $_SESSION['mensagem'] = "Status inválido.";
    $_SESSION['tipo_mensagem'] = 'danger';
    header("Location: ../PAGES/admin.php");
    exit;
}

// Atualizar o status do artigo
if (atualizarStatusArtigo($conn, $artigo_id, $status)) {
    $_SESSION['mensagem'] = "Status do artigo atualizado para " . ucfirst($status) . ".";
    $_SESSION['tipo_mensagem'] = 'success';
} else {
    $_SESSION['mensagem'] = "Erro ao atualizar o status do artigo.";
    $_SESSION['tipo_mensagem'] = 'danger';
}

// Redirecionar de volta para o painel administrativo
header("Location: ../PAGES/admin.php");
exit;
?>
