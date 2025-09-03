<?php
/**
 * Integração de Email para o processamento de artigos
 * 
 * Este arquivo contém funções que integram o envio de e-mails 
 * ao fluxo de processamento de artigos.
 */

// Incluir os arquivos necessários
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/email_notification.php';

/**
 * Função para enviar notificação após o processamento de um artigo
 * 
 * @param array $artigo Dados do artigo processado
 * @param int $usuario_id ID do usuário autor
 * @return bool Resultado do envio (true=sucesso, false=falha)
 */
function enviar_notificacao_artigo_processado($artigo, $usuario_id) {
    // Registrar no log
    error_log("Preparando para enviar notificação para o artigo ID: {$artigo['id']}");
    
    // Obter dados do usuário autor
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Erro de conexão com o banco de dados: " . $conn->connect_error);
        return false;
    }
    
    // Consultar dados do usuário
    $stmt = $conn->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Usuário não encontrado para o ID: {$usuario_id}");
        $conn->close();
        return false;
    }
    
    $usuario = $result->fetch_assoc();
    $conn->close();
    
    // Enviar notificação para os administradores
    $resultado = notificar_admins_novo_artigo($artigo, $usuario['nome']);
    
    error_log("Resultado da notificação para artigo ID {$artigo['id']}: " . 
              ($resultado ? "Sucesso" : "Falha"));
    
    return $resultado;
}

/**
 * Função para enviar notificação quando o status de um artigo é alterado
 * 
 * @param int $artigo_id ID do artigo
 * @param string $novo_status Novo status do artigo
 * @param string $comentario Comentário opcional do administrador
 * @return bool Resultado do envio (true=sucesso, false=falha)
 */
function notificar_mudanca_status_artigo($artigo_id, $novo_status, $comentario = '') {
    // Registrar no log
    error_log("Preparando para enviar notificação de mudança de status para o artigo ID: {$artigo_id}");
    
    // Obter dados do artigo e do autor
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Erro de conexão com o banco de dados: " . $conn->connect_error);
        return false;
    }
    
    // Consultar dados do artigo
    $stmt = $conn->prepare("SELECT a.titulo, a.usuario_id, u.nome, u.email 
                           FROM artigos a
                           INNER JOIN usuarios u ON a.usuario_id = u.id
                           WHERE a.id = ?");
    $stmt->bind_param("i", $artigo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Artigo não encontrado para o ID: {$artigo_id}");
        $conn->close();
        return false;
    }
    
    $dados = $result->fetch_assoc();
    $conn->close();
    
    // Preparar dados para a notificação
    $artigo = [
        'id' => $artigo_id,
        'titulo' => $dados['titulo']
    ];
    
    $aprovado = ($novo_status == 'aprovado');
    
    // Enviar notificação para o autor
    $resultado = notificar_autor_status_artigo(
        $dados['email'],
        $dados['nome'],
        $artigo,
        $aprovado,
        $comentario
    );
    
    error_log("Resultado da notificação de mudança de status para artigo ID {$artigo_id}: " . 
              ($resultado ? "Sucesso" : "Falha"));
    
    return $resultado;
}

/**
 * Hook para processar envio de e-mail após salvar um artigo
 * 
 * @param array $artigo Dados do artigo
 * @param int $usuario_id ID do usuário
 * @return bool Resultado do envio de e-mail
 */
function hook_after_artigo_salvo($artigo, $usuario_id) {
    // Verificar se é um artigo novo (status inicial pendente)
    if (isset($artigo['status']) && $artigo['status'] == 'pendente') {
        return enviar_notificacao_artigo_processado($artigo, $usuario_id);
    }
    return false;
}

/**
 * Hook para processar envio de e-mail após alteração de status
 * 
 * @param int $artigo_id ID do artigo
 * @param string $novo_status Novo status do artigo
 * @param string $comentario Comentário opcional do administrador
 * @return bool Resultado do envio de e-mail
 */
function hook_after_status_alterado($artigo_id, $novo_status, $comentario = '') {
    // Enviar notificação apenas se o status for aprovado ou rejeitado
    if ($novo_status == 'aprovado' || $novo_status == 'rejeitado') {
        return notificar_mudanca_status_artigo($artigo_id, $novo_status, $comentario);
    }
    return false;
}
?>
