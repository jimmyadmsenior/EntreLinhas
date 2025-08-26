<?php
/**
 * email_universal_integration.php
 * 
 * Integração do sistema de e-mail universal com o EntreLinhas
 * Este arquivo estende a funcionalidade do sistema atual, oferecendo uma alternativa
 * mais robusta com múltiplos métodos de envio.
 */

// Incluir arquivos necessários
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../email_universal.php';

/**
 * Envia e-mail usando o sistema universal de e-mail
 * 
 * Esta função tenta usar a melhor opção disponível para envio de e-mail
 * 
 * @param string $to E-mail do destinatário
 * @param string $subject Assunto do e-mail
 * @param string $message Conteúdo do e-mail (pode ser HTML)
 * @param array $options Opções adicionais (cc, bcc, de_email, de_nome)
 * @return array Resultado do envio
 */
function send_email_universal($to, $subject, $message, $options = []) {
    // Registrar no log
    error_log("Enviando e-mail universal para: {$to}, assunto: {$subject}");
    
    // Criar instância do EmailUniversal
    $emailUniversal = new EmailUniversal();
    
    // Enviar e-mail
    $result = $emailUniversal->enviar($to, $subject, $message, $options);
    
    // Registrar resultado no log
    if ($result['sucesso']) {
        error_log("E-mail enviado com sucesso para {$to} usando método: {$result['metodo']}");
    } else {
        error_log("Erro ao enviar e-mail para {$to}: {$result['erro']}");
    }
    
    return $result;
}

/**
 * Envia notificação de status de artigo
 * 
 * @param int $artigo_id ID do artigo
 * @param string $status Novo status
 * @param string $comentario Comentário opcional
 * @return array Resultado do envio
 */
function notificar_status_artigo_universal($artigo_id, $status, $comentario = '') {
    global $conn;
    
    if (!isset($conn) || $conn->connect_error) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    }
    
    if ($conn->connect_error) {
        error_log("Erro de conexão com o banco de dados: " . $conn->connect_error);
        return [
            'sucesso' => false,
            'erro' => 'Erro de conexão com o banco de dados'
        ];
    }
    
    // Buscar dados do artigo
    $stmt = $conn->prepare("SELECT a.titulo, u.email, u.nome FROM artigos a 
                            JOIN usuarios u ON a.usuario_id = u.id 
                            WHERE a.id = ?");
                            
    if (!$stmt) {
        error_log("Erro na preparação da consulta: " . $conn->error);
        return [
            'sucesso' => false,
            'erro' => 'Erro na preparação da consulta'
        ];
    }
    
    $stmt->bind_param("i", $artigo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Artigo não encontrado: {$artigo_id}");
        return [
            'sucesso' => false,
            'erro' => 'Artigo não encontrado'
        ];
    }
    
    $artigo = $result->fetch_assoc();
    $stmt->close();
    
    // Montar mensagem de e-mail
    $subject = "Atualização de Status do Artigo: {$artigo['titulo']}";
    
    // Mapear status para descrições mais amigáveis
    $status_descricao = [
        'pendente' => 'Pendente de Revisão',
        'revisao' => 'Em Revisão',
        'aprovado' => 'Aprovado',
        'publicado' => 'Publicado',
        'recusado' => 'Não Aprovado',
        'correcoes' => 'Aguardando Correções'
    ];
    
    $status_texto = isset($status_descricao[$status]) ? $status_descricao[$status] : $status;
    
    // Criar o corpo do e-mail
    $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Atualização de Status</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { font-size: 12px; text-align: center; margin-top: 30px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EntreLinhas</h1>
        </div>
        <div class="content">
            <h2>Atualização de Status do Artigo</h2>
            <p>Olá ' . htmlspecialchars($artigo['nome']) . ',</p>
            <p>O status do seu artigo <strong>"' . htmlspecialchars($artigo['titulo']) . '"</strong> foi atualizado para: </p>
            <p style="font-size: 18px; font-weight: bold; color: #3498db; padding: 10px; background: #e8f4fc; text-align: center; border-radius: 5px;">' 
                . htmlspecialchars($status_texto) . '</p>';
    
    // Adicionar comentário, se existir
    if (!empty($comentario)) {
        $message .= '
            <h3>Comentário:</h3>
            <div style="background: #fff; padding: 15px; border-left: 4px solid #3498db; margin: 10px 0;">
                ' . nl2br(htmlspecialchars($comentario)) . '
            </div>';
    }
    
    $message .= '
            <p>Você pode acessar seu artigo em nossa plataforma para verificar mais detalhes.</p>
            <p>Atenciosamente,<br>Equipe EntreLinhas</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' EntreLinhas - Todos os direitos reservados</p>
        </div>
    </div>
</body>
</html>';
    
    // Enviar e-mail
    $resultado = send_email_universal($artigo['email'], $subject, $message);
    
    // Registrar o envio no log
    $log_query = "INSERT INTO email_log (artigo_id, destinatario, assunto, status_envio, metodo_envio, data_envio) 
                 VALUES (?, ?, ?, ?, ?, NOW())";
                 
    $stmt = $conn->prepare($log_query);
    if ($stmt) {
        $status_envio = $resultado['sucesso'] ? 'enviado' : 'falha';
        $metodo_envio = $resultado['metodo'] ?? 'desconhecido';
        
        $stmt->bind_param("issss", $artigo_id, $artigo['email'], $subject, $status_envio, $metodo_envio);
        $stmt->execute();
        $stmt->close();
    }
    
    return $resultado;
}

/**
 * Envia notificação de novo artigo para administradores
 * 
 * @param int $artigo_id ID do artigo
 * @return array Resultado do envio
 */
function notificar_novo_artigo_universal($artigo_id) {
    global $conn;
    
    if (!isset($conn) || $conn->connect_error) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    }
    
    if ($conn->connect_error) {
        error_log("Erro de conexão com o banco de dados: " . $conn->connect_error);
        return [
            'sucesso' => false,
            'erro' => 'Erro de conexão com o banco de dados'
        ];
    }
    
    // Buscar dados do artigo e autor
    $stmt = $conn->prepare("SELECT a.titulo, a.resumo, u.nome as autor, u.email as autor_email 
                            FROM artigos a 
                            JOIN usuarios u ON a.usuario_id = u.id 
                            WHERE a.id = ?");
                            
    if (!$stmt) {
        error_log("Erro na preparação da consulta: " . $conn->error);
        return [
            'sucesso' => false,
            'erro' => 'Erro na preparação da consulta'
        ];
    }
    
    $stmt->bind_param("i", $artigo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Artigo não encontrado: {$artigo_id}");
        return [
            'sucesso' => false,
            'erro' => 'Artigo não encontrado'
        ];
    }
    
    $artigo = $result->fetch_assoc();
    $stmt->close();
    
    // Buscar e-mails dos administradores
    $admin_emails = [];
    $admin_query = "SELECT email FROM usuarios WHERE tipo = 'admin' AND status = 'ativo'";
    $admin_result = $conn->query($admin_query);
    
    if ($admin_result && $admin_result->num_rows > 0) {
        while ($row = $admin_result->fetch_assoc()) {
            $admin_emails[] = $row['email'];
        }
    }
    
    if (empty($admin_emails)) {
        error_log("Nenhum administrador encontrado para notificar sobre o novo artigo");
        return [
            'sucesso' => false,
            'erro' => 'Nenhum administrador encontrado'
        ];
    }
    
    // Assunto do e-mail
    $subject = "Novo Artigo Submetido: {$artigo['titulo']}";
    
    // Conteúdo do e-mail
    $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Novo Artigo</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .article-info { background: #fff; padding: 15px; border-left: 4px solid #3498db; margin: 10px 0; }
        .footer { font-size: 12px; text-align: center; margin-top: 30px; color: #777; }
        .button { display: inline-block; background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EntreLinhas</h1>
        </div>
        <div class="content">
            <h2>Novo Artigo Submetido</h2>
            <p>Um novo artigo foi submetido para revisão:</p>
            
            <div class="article-info">
                <p><strong>Título:</strong> ' . htmlspecialchars($artigo['titulo']) . '</p>
                <p><strong>Autor:</strong> ' . htmlspecialchars($artigo['autor']) . ' (' . htmlspecialchars($artigo['autor_email']) . ')</p>
                <p><strong>ID do Artigo:</strong> ' . $artigo_id . '</p>
                <p><strong>Resumo:</strong><br>' . nl2br(htmlspecialchars(substr($artigo['resumo'], 0, 300))) . 
                (strlen($artigo['resumo']) > 300 ? '...' : '') . '</p>
            </div>
            
            <p>Este artigo está aguardando revisão.</p>
            <p><a href="http://localhost:8000/PAGES/admin_dashboard.php?action=revisar&id=' . $artigo_id . '" class="button">Revisar Artigo</a></p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' EntreLinhas - Todos os direitos reservados</p>
        </div>
    </div>
</body>
</html>';
    
    $resultados = [];
    
    // Enviar e-mail para cada administrador
    foreach ($admin_emails as $admin_email) {
        $resultado = send_email_universal($admin_email, $subject, $message);
        $resultados[] = $resultado;
        
        // Registrar o envio no log
        $log_query = "INSERT INTO email_log (artigo_id, destinatario, assunto, status_envio, metodo_envio, data_envio) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
                     
        $stmt = $conn->prepare($log_query);
        if ($stmt) {
            $status_envio = $resultado['sucesso'] ? 'enviado' : 'falha';
            $metodo_envio = $resultado['metodo'] ?? 'desconhecido';
            
            $stmt->bind_param("issss", $artigo_id, $admin_email, $subject, $status_envio, $metodo_envio);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Verificar se pelo menos um e-mail foi enviado com sucesso
    $success = false;
    foreach ($resultados as $resultado) {
        if ($resultado['sucesso']) {
            $success = true;
            break;
        }
    }
    
    return [
        'sucesso' => $success,
        'destinatarios' => count($admin_emails),
        'envios_sucesso' => count(array_filter($resultados, function($r) { return $r['sucesso']; })),
        'resultados' => $resultados
    ];
}

// Verificar se a tabela de log de e-mails existe
function verificar_tabela_email_log() {
    global $conn;
    
    if (!isset($conn) || $conn->connect_error) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    }
    
    if ($conn->connect_error) {
        error_log("Erro de conexão com o banco de dados: " . $conn->connect_error);
        return false;
    }
    
    // Verificar se a tabela existe
    $result = $conn->query("SHOW TABLES LIKE 'email_log'");
    
    if ($result && $result->num_rows === 0) {
        // Criar a tabela
        $sql = "CREATE TABLE email_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            artigo_id INT,
            destinatario VARCHAR(255) NOT NULL,
            assunto VARCHAR(255) NOT NULL,
            status_envio ENUM('enviado', 'falha') NOT NULL,
            metodo_envio VARCHAR(50) NOT NULL,
            data_envio DATETIME NOT NULL,
            INDEX (artigo_id),
            INDEX (destinatario),
            INDEX (data_envio)
        )";
        
        if ($conn->query($sql)) {
            error_log("Tabela email_log criada com sucesso");
            return true;
        } else {
            error_log("Erro ao criar tabela email_log: " . $conn->error);
            return false;
        }
    }
    
    return true;
}

// Chamar verificação de tabela
verificar_tabela_email_log();
