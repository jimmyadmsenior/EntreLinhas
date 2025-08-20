<?php
// Arquivo para processar exclusão de artigos
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../PAGES/login.php");
    exit;
}

// Incluir arquivos necessários
require_once 'config.php';
require_once 'artigos.php';

// Verificar se os dados necessários foram enviados
if (!isset($_POST['artigo_id'])) {
    $_SESSION['mensagem'] = "Dados incompletos para processar a exclusão.";
    $_SESSION['tipo_mensagem'] = 'danger';
    header("Location: ../PAGES/index.php");
    exit;
}

$artigo_id = intval($_POST['artigo_id']);

// Verificar se o artigo existe e se o usuário tem permissão para excluí-lo
$artigo = obterArtigo($conn, $artigo_id);

if (!$artigo) {
    $_SESSION['mensagem'] = "Artigo não encontrado.";
    $_SESSION['tipo_mensagem'] = 'danger';
    header("Location: ../PAGES/index.php");
    exit;
}

// Verificar se é um administrador ou o autor do artigo
$is_admin = isset($_SESSION['email']) && $_SESSION['email'] === ADMIN_EMAIL;
$is_autor = $_SESSION['id'] === $artigo['id_usuario'];

if (!$is_admin && !$is_autor) {
    $_SESSION['mensagem'] = "Você não tem permissão para excluir este artigo.";
    $_SESSION['tipo_mensagem'] = 'danger';
    header("Location: ../PAGES/index.php");
    exit;
}

// Excluir o artigo
if (excluirArtigo($conn, $artigo_id)) {
    $_SESSION['mensagem'] = "Artigo excluído com sucesso!";
    $_SESSION['tipo_mensagem'] = 'success';
} else {
    $_SESSION['mensagem'] = "Erro ao excluir o artigo.";
    $_SESSION['tipo_mensagem'] = 'danger';
}

// Redirecionar de volta para a página apropriada
if ($is_admin) {
    header("Location: ../PAGES/admin.php");
} else {
    header("Location: ../PAGES/meus-artigos.php");
}
exit;
?>
