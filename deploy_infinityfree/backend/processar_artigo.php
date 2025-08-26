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
require_once '../backend/email_integration.php';

// Verificar o tipo de ação (enviar ou editar)
$acao = isset($_POST['acao']) ? $_POST['acao'] : 'enviar';
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar os campos
    $titulo = !empty($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $conteudo = !empty($_POST['conteudo']) ? trim($_POST['conteudo']) : '';
    $categoria = !empty($_POST['categoria']) ? trim($_POST['categoria']) : '';
    
    // Validar dados
    if (empty($titulo)) {
        $mensagem = "Por favor, insira um título para o artigo.";
        $tipo_mensagem = 'danger';
    } elseif (empty($conteudo)) {
        $mensagem = "Por favor, insira o conteúdo do artigo.";
        $tipo_mensagem = 'danger';
    } elseif (empty($categoria)) {
        $mensagem = "Por favor, selecione uma categoria.";
        $tipo_mensagem = 'danger';
    } else {
        // Processar upload de imagem se existir
        $imagem_path = "";
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagem']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Verificar extensão do arquivo
            if (!in_array(strtolower($filetype), $allowed)) {
                $mensagem = "Tipo de arquivo não permitido. Apenas JPG, JPEG, PNG e GIF são aceitos.";
                $tipo_mensagem = 'danger';
            } else {
                // Gerar nome único para o arquivo
                $new_filename = uniqid() . '.' . $filetype;
                $upload_dir = "../uploads/artigos/";
                
                // Criar diretório se não existir
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $upload_path = $upload_dir . $new_filename;
                
                // Mover arquivo para o diretório de uploads
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                    $imagem_path = $upload_path;
                } else {
                    $mensagem = "Erro ao fazer upload da imagem.";
                    $tipo_mensagem = 'danger';
                }
            }
        }
        
        // Se não houver erro, preparar dados do artigo
        if (empty($mensagem)) {
            // Preparar dados do artigo
            $artigo = [
                'titulo' => $titulo,
                'conteudo' => $conteudo,
                'categoria' => $categoria,
                'id_usuario' => $_SESSION['id'],
                'imagem' => $imagem_path
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
            
            try {
                // Criar diretório de logs se não existir
                if (!is_dir('../logs')) {
                    mkdir('../logs', 0777, true);
                }
                
                // Registrar a tentativa de envio
                error_log("[" . date('Y-m-d H:i:s') . "] Tentando enviar notificação sobre artigo ID: " . $artigo['id'], 3, "../logs/email_notify.log");
                
                // Usar a nova integração de e-mail
                $artigo['status'] = 'pendente';
                // Enviar notificação diretamente
                $notificacao_enviada = notificar_admins_novo_artigo($artigo, $autor_nome);
                
                // Registrar no log se a notificação foi enviada
                if ($notificacao_enviada) {
                    error_log("[" . date('Y-m-d H:i:s') . "] E-mail de notificação enviado para administradores sobre o artigo ID: " . $artigo['id'], 3, "../logs/email_notify.log");
                } else {
                    error_log("[" . date('Y-m-d H:i:s') . "] Falha ao enviar e-mail de notificação para administradores sobre o artigo ID: " . $artigo['id'], 3, "../logs/email_notify.log");
                }
            } catch (Exception $e) {
                error_log("[" . date('Y-m-d H:i:s') . "] Exceção ao enviar notificação: " . $e->getMessage(), 3, "../logs/email_notify.log");
                $notificacao_enviada = false;
            }
            
            // Redirecionar para página de sucesso
            $_SESSION['mensagem'] = $resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'success';
            $_SESSION['artigo_enviado'] = true; // Marcar que um artigo foi enviado com sucesso
            
            // Registrar informações de debug sobre o envio de notificação
            error_log("[DEBUG] Notificação de artigo: " . ($notificacao_enviada ? "ENVIADA" : "FALHOU"));
            error_log("[DEBUG] ID do artigo: " . $artigo['id']);
            error_log("[DEBUG] Autor: " . $autor_nome);
            
            // Redirecionar para a página de sucesso
            header("Location: ../PAGES/envio-sucesso.php");
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
