<?php
// Arquivo para processar o envio/exclusão de comentários
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../PAGES/login.php");
    exit;
}

// Incluir arquivos necessários
require_once 'config.php';
require_once 'comentarios.php';

// Verificar o tipo de ação
$acao = isset($_POST['acao']) ? $_POST['acao'] : 'enviar';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($acao == 'enviar') {
        // Enviar novo comentário
        if (!isset($_POST['artigo_id']) || !isset($_POST['comentario'])) {
            $_SESSION['mensagem'] = "Dados incompletos para enviar comentário.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/index.php");
            exit;
        }
        
        $comentario = [
            'id_artigo' => intval($_POST['artigo_id']),
            'id_usuario' => $_SESSION['id'],
            'comentario' => $_POST['comentario']
        ];
        
        $resultado = enviarComentario($conn, $comentario);
        
        if ($resultado['status']) {
            $_SESSION['mensagem'] = $resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'success';
        } else {
            $_SESSION['mensagem'] = $resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'danger';
        }
        
        // Redirecionar de volta para o artigo
        header("Location: ../PAGES/artigo.php?id=" . $comentario['id_artigo']);
        exit;
    } elseif ($acao == 'excluir') {
        // Excluir comentário
        if (!isset($_POST['comentario_id']) || !isset($_POST['artigo_id'])) {
            $_SESSION['mensagem'] = "Dados incompletos para excluir comentário.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/index.php");
            exit;
        }
        
        $comentario_id = intval($_POST['comentario_id']);
        $artigo_id = intval($_POST['artigo_id']);
        
        $resultado = excluirComentario($conn, $comentario_id, $_SESSION['id']);
        
        if ($resultado['status']) {
            $_SESSION['mensagem'] = $resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'success';
        } else {
            $_SESSION['mensagem'] = $resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'danger';
        }
        
        // Redirecionar de volta para o artigo
        header("Location: ../PAGES/artigo.php?id=" . $artigo_id);
        exit;
    } elseif ($acao == 'moderar') {
        // Moderar comentário (apenas para administradores)
        if (!isset($_POST['comentario_id']) || !isset($_POST['status']) || !isset($_POST['artigo_id'])) {
            $_SESSION['mensagem'] = "Dados incompletos para moderar comentário.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/admin.php");
            exit;
        }
        
        $comentario_id = intval($_POST['comentario_id']);
        $status = $_POST['status'];
        $artigo_id = intval($_POST['artigo_id']);
        
        $resultado = moderarComentario($conn, $comentario_id, $status);
        
        if ($resultado['status']) {
            $_SESSION['mensagem'] = $resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'success';
        } else {
            $_SESSION['mensagem'] = $resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'danger';
        }
        
        // Redirecionar de volta para o painel de administração ou para o artigo
        $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : "../PAGES/artigo.php?id=" . $artigo_id;
        header("Location: " . $redirect);
        exit;
    }
}

// Se chegou até aqui, redirecionar para a página inicial
header("Location: ../PAGES/index.php");
exit;
?>
