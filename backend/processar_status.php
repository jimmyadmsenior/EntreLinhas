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
require_once 'email_notification.php';

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
    
    // Obter informações do artigo e do autor para enviar e-mail
    $sql = "SELECT a.*, u.nome as autor_nome, u.email as autor_email 
            FROM artigos a 
            JOIN usuarios u ON a.id_usuario = u.id 
            WHERE a.id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $artigo_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $resultado = mysqli_stmt_get_result($stmt);
            
            if ($artigo_dados = mysqli_fetch_assoc($resultado)) {
                // Notificar o autor sobre a aprovação/rejeição
                $comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';
                notificar_autor_status_artigo(
                    $artigo_dados['autor_email'],
                    $artigo_dados['autor_nome'],
                    $artigo_dados,
                    ($status === 'aprovado'),
                    $comentario
                );
            }
        }
        
        mysqli_stmt_close($stmt);
    }
} else {
    $_SESSION['mensagem'] = "Erro ao atualizar o status do artigo.";
    $_SESSION['tipo_mensagem'] = 'danger';
}

// Redirecionar de volta para o painel administrativo
header("Location: ../PAGES/admin.php");
exit;
?>
