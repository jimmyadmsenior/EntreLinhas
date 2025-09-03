<?php
// Arquivo para processar operações de imagens
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../PAGES/login.php");
    exit;
}

// Incluir arquivos necessários
require_once 'config.php';
require_once 'artigos.php';
require_once 'imagens_artigos.php';

// Verificar se os dados necessários foram enviados
if (!isset($_POST['acao']) || !isset($_POST['artigo_id'])) {
    $_SESSION['mensagem'] = "Dados incompletos para processar a imagem.";
    $_SESSION['tipo_mensagem'] = 'danger';
    header("Location: ../PAGES/index.php");
    exit;
}

$acao = $_POST['acao'];
$artigo_id = intval($_POST['artigo_id']);

// Verificar se o artigo existe e se o usuário tem permissão
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
    $_SESSION['mensagem'] = "Você não tem permissão para modificar este artigo.";
    $_SESSION['tipo_mensagem'] = 'danger';
    header("Location: ../PAGES/index.php");
    exit;
}

// Processar a ação solicitada
switch ($acao) {
    case 'adicionar':
        // Adicionar novas imagens ao artigo
        if (!isset($_FILES['imagens'])) {
            $_SESSION['mensagem'] = "Nenhuma imagem foi enviada.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        $imagens = $_FILES['imagens'];
        
        // Processar upload de imagens
        $upload_resultado = processarMultiplasImagens($imagens);
        
        if (!$upload_resultado['status']) {
            $_SESSION['mensagem'] = $upload_resultado['mensagem'];
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        // Associar imagens ao artigo
        if (!associarImagensAoArtigo($conn, $artigo_id, $upload_resultado['caminhos'])) {
            $_SESSION['mensagem'] = "Erro ao associar imagens ao artigo.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        // Se o artigo ainda não tem imagem principal, definir a primeira imagem como principal
        if (empty($artigo['imagem']) && !empty($upload_resultado['caminhos'])) {
            $sql_update = "UPDATE artigos SET imagem = ? WHERE id = ?";
            if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                $primeira_imagem = $upload_resultado['caminhos'][0];
                mysqli_stmt_bind_param($stmt_update, "si", $primeira_imagem, $artigo_id);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
            }
        }
        
        $_SESSION['mensagem'] = "Imagens adicionadas com sucesso!";
        $_SESSION['tipo_mensagem'] = 'success';
        break;
        
    case 'remover':
        // Remover uma imagem do artigo
        if (!isset($_POST['imagem_id'])) {
            $_SESSION['mensagem'] = "ID da imagem não foi fornecido.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        $imagem_id = intval($_POST['imagem_id']);
        
        // Verificar se é a última imagem do artigo
        if (count($artigo['imagens']) <= 1) {
            $_SESSION['mensagem'] = "Não é possível remover a última imagem do artigo. Adicione outra imagem primeiro.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        // Verificar se é a imagem principal do artigo
        $imagem_a_remover = null;
        foreach ($artigo['imagens'] as $img) {
            if ($img['id'] == $imagem_id) {
                $imagem_a_remover = $img;
                break;
            }
        }
        
        if (!$imagem_a_remover) {
            $_SESSION['mensagem'] = "Imagem não encontrada.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        $is_imagem_principal = ($artigo['imagem'] == $imagem_a_remover['caminho']);
        
        // Remover a imagem
        if (!removerImagemArtigo($conn, $imagem_id)) {
            $_SESSION['mensagem'] = "Erro ao remover a imagem.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        // Se era a imagem principal, atualizar para outra imagem
        if ($is_imagem_principal) {
            // Obter o artigo atualizado com as imagens restantes
            $artigo_atualizado = obterArtigo($conn, $artigo_id);
            
            if (!empty($artigo_atualizado['imagens'])) {
                $nova_imagem_principal = $artigo_atualizado['imagens'][0]['caminho'];
                
                $sql_update = "UPDATE artigos SET imagem = ? WHERE id = ?";
                if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                    mysqli_stmt_bind_param($stmt_update, "si", $nova_imagem_principal, $artigo_id);
                    mysqli_stmt_execute($stmt_update);
                    mysqli_stmt_close($stmt_update);
                }
            }
        }
        
        $_SESSION['mensagem'] = "Imagem removida com sucesso!";
        $_SESSION['tipo_mensagem'] = 'success';
        break;
        
    case 'atualizar_descricao':
        // Atualizar a descrição de uma imagem
        if (!isset($_POST['imagem_id']) || !isset($_POST['descricao'])) {
            $_SESSION['mensagem'] = "Dados incompletos para atualizar a descrição.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        $imagem_id = intval($_POST['imagem_id']);
        $descricao = trim($_POST['descricao']);
        
        if (!atualizarDescricaoImagem($conn, $imagem_id, $descricao)) {
            $_SESSION['mensagem'] = "Erro ao atualizar a descrição da imagem.";
            $_SESSION['tipo_mensagem'] = 'danger';
            header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
            exit;
        }
        
        $_SESSION['mensagem'] = "Descrição atualizada com sucesso!";
        $_SESSION['tipo_mensagem'] = 'success';
        break;
        
    default:
        $_SESSION['mensagem'] = "Ação inválida.";
        $_SESSION['tipo_mensagem'] = 'danger';
}

// Redirecionar de volta para a página de gerenciamento de imagens
header("Location: ../PAGES/gerenciar-imagens.php?artigo_id=" . $artigo_id);
exit;
?>
