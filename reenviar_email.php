<?php
/**
 * Reenviar Email - EntreLinhas
 * 
 * Este script permite reenviar um email a partir de um registro de log
 */

// Incluir arquivos necessários
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/email_logger.php';
require_once __DIR__ . '/backend/sendgrid_email.php';

// Verificar permissão de administrador
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: PAGES/login.php?redirect=' . urlencode($_SERVER['PHP_SELF']) . '&error=Permissão negada');
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: notificacoes_dashboard.php?error=ID de e-mail não especificado');
    exit;
}

$log_id = (int)$_GET['id'];

// Obter registro de log
$log = get_email_log($log_id);

if (!$log) {
    header('Location: notificacoes_dashboard.php?error=Registro de e-mail não encontrado');
    exit;
}

// Processar reenvio
$resultado = reenviar_email($log_id);

if ($resultado) {
    header('Location: notificacoes_dashboard.php?success=E-mail reenviado com sucesso para ' . htmlspecialchars($log['destinatario']));
} else {
    header('Location: notificacoes_dashboard.php?error=Falha ao reenviar e-mail para ' . htmlspecialchars($log['destinatario']));
}
exit;
?>
