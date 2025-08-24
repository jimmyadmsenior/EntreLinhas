<?php
// Arquivo para processar o envio/edição de artigos
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Incluir arquivos necessários
require_once '../backend/config.php';
require_once '../backend/artigos.php';
require_once '../backend/email_notification.php';

// Verificar o tipo de ação (enviar ou editar)
$acao = isset($_POST['acao']) ? $_POST['acao'] : 'enviar';
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preparar dados do artigo
    $artigo = [
        'titulo' => $_POST['titulo'],
        'conteudo' => $_POST['conteudo'],
        'categoria' => $_POST['categoria'],
        'id_usuario' => $_SESSION['id']
    ];
    
    // Verificar imagens enviadas
    $imagens = isset($_FILES['imagens']) ? $_FILES['imagens'] : null;
    
    if ($acao == 'enviar') {
        // Enviar novo artigo
        $resultado = enviarArtigo($conn, $artigo, $imagens);
        
        if ($resultado['status']) {
            // Obter nome do autor para o e-mail
            $autor_nome = $_SESSION['nome'];
            
            // Enviar notificação por e-mail para os administradores
            $artigo['id'] = $resultado['artigo_id'];
            notificar_admins_novo_artigo($artigo, $autor_nome);
            
            // Redirecionar para página de sucesso
            $_SESSION['mensagem'] = $resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'success';
            header("Location: meus-artigos.php");
            exit;
        } else {
            // Exibir mensagem de erro
            $mensagem = $resultado['mensagem'];
            $tipo_mensagem = 'danger';
        }
    } elseif ($acao == 'editar') {
        // Editar artigo existente
        $artigo_id = isset($_POST['artigo_id']) ? intval($_POST['artigo_id']) : 0;
        
        if ($artigo_id > 0) {
            $resultado = editarArtigo($conn, $artigo_id, $artigo, $imagens);
            
            if ($resultado['status']) {
                // Redirecionar para página de sucesso
                $_SESSION['mensagem'] = $resultado['mensagem'];
                $_SESSION['tipo_mensagem'] = 'success';
                header("Location: meus-artigos.php");
                exit;
            } else {
                // Exibir mensagem de erro
                $mensagem = $resultado['mensagem'];
                $tipo_mensagem = 'danger';
            }
        } else {
            $mensagem = "ID do artigo inválido.";
            $tipo_mensagem = 'danger';
        }
    }
}

// Se chegou até aqui, houve um erro. Redirecionar com mensagem de erro
$_SESSION['mensagem'] = $mensagem;
$_SESSION['tipo_mensagem'] = $tipo_mensagem;

if ($acao == 'editar' && isset($_POST['artigo_id'])) {
    header("Location: editar-artigo.php?id=" . $_POST['artigo_id']);
} else {
    header("Location: enviar-artigo.php");
}
exit;
?>
