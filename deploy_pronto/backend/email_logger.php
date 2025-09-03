<?php
/**
 * Email Logger - Sistema de registro de emails
 * 
 * Este script integra o sistema de logs de email para EntreLinhas,
 * permitindo registrar todas as tentativas de envio de email no banco de dados
 * e garantir que elas sejam visualizadas no dashboard de notificações.
 */

// Incluir arquivo de configuração
require_once __DIR__ . '/config.php';

// Verificar se a tabela existe, senão criar
function ensure_email_log_table_exists() {
    global $conn;
    
    if (!isset($conn) || $conn->connect_error) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Falha ao conectar ao banco de dados: " . $conn->connect_error);
            return false;
        }
    }
    
    $sql = "SHOW TABLES LIKE 'email_log'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return true;
    }
    
    // Criar tabela se não existir
    $sql_create = "CREATE TABLE IF NOT EXISTS email_log (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        artigo_id INT(11) NULL,
        destinatario VARCHAR(255) NOT NULL,
        assunto VARCHAR(255) NOT NULL,
        conteudo TEXT,
        status_envio ENUM('sucesso', 'falha', 'simulado') NOT NULL,
        metodo_envio VARCHAR(20) DEFAULT 'sendgrid',
        data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        detalhes TEXT NULL
    )";
    
    if ($conn->query($sql_create) === TRUE) {
        error_log("Tabela email_log criada com sucesso");
        return true;
    } else {
        error_log("Erro ao criar tabela email_log: " . $conn->error);
        return false;
    }
}

/**
 * Registra uma tentativa de envio de email no banco de dados
 * 
 * @param int $artigo_id ID do artigo relacionado (pode ser null)
 * @param string $destinatario Email do destinatário
 * @param string $assunto Assunto do email
 * @param string $conteudo Conteúdo HTML do email
 * @param string $status Status do envio: sucesso, falha, simulado
 * @param string $metodo Método de envio usado
 * @param string $detalhes Detalhes adicionais (opcional)
 * @return bool Sucesso ou falha no registro
 */
function log_email($artigo_id, $destinatario, $assunto, $conteudo, $status, $metodo, $detalhes = "") {
    global $conn;
    
    // Garantir que a tabela existe
    ensure_email_log_table_exists();
    
    // Verificar se $conn está disponível, senão criar nova conexão
    if (!isset($conn) || $conn->connect_error) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Erro ao conectar ao banco de dados para log de email: " . $conn->connect_error);
            return false;
        }
    }
    
    // Preparar consulta SQL
    $sql = "INSERT INTO email_log 
            (artigo_id, destinatario, assunto, conteudo, status_envio, metodo_envio, detalhes)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Verificar se a preparação foi bem-sucedida
    if ($stmt === false) {
        error_log("Erro ao preparar consulta para log de email: " . $conn->error);
        return false;
    }
    
    // Vincular parâmetros
    $stmt->bind_param("issssss", 
        $artigo_id, 
        $destinatario, 
        $assunto, 
        $conteudo, 
        $status, 
        $metodo,
        $detalhes
    );
    
    // Executar
    $resultado = $stmt->execute();
    
    // Verificar resultado
    if ($resultado === false) {
        error_log("Erro ao inserir log de email: " . $stmt->error);
    }
    
    $stmt->close();
    return $resultado;
}

/**
 * Verifica se estamos em ambiente de desenvolvimento
 * 
 * @return bool True se estiver em ambiente de desenvolvimento
 */
function is_development_environment() {
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    return $server_name == 'localhost' || 
           strpos($server_name, '127.0.0.1') !== false || 
           strpos($server_name, '192.168.') === 0;
}

/**
 * Recupera o log de email por ID
 * 
 * @param int $id ID do registro de log
 * @return array|null Dados do log ou null se não encontrado
 */
function get_email_log($id) {
    global $conn;
    
    // Verificar se $conn está disponível, senão criar nova conexão
    if (!isset($conn) || $conn->connect_error) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Erro ao conectar ao banco de dados: " . $conn->connect_error);
            return null;
        }
    }
    
    $sql = "SELECT * FROM email_log WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Erro ao preparar consulta: " . $conn->error);
        return null;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $log = $result->fetch_assoc();
        $stmt->close();
        return $log;
    }
    
    $stmt->close();
    return null;
}

/**
 * Tenta reenviar um email baseado no registro de log
 * 
 * @param int $log_id ID do registro de log
 * @return bool Resultado do envio
 */
function reenviar_email($log_id) {
    $log = get_email_log($log_id);
    
    if (!$log) {
        return false;
    }
    
    // Verificar se temos o arquivo do SendGrid
    $sendgrid_file = __DIR__ . '/sendgrid_email.php';
    if (file_exists($sendgrid_file)) {
        require_once $sendgrid_file;
        
        if (function_exists('sendEmail')) {
            $resultado = sendEmail(
                $log['destinatario'], 
                $log['assunto'], 
                $log['conteudo']
            );
            
            if ($resultado) {
                // Atualizar registro para sucesso
                update_email_log_status($log_id, 'sucesso', 'Reenviado manualmente');
                return true;
            } else {
                // Atualizar detalhes do erro
                update_email_log_status($log_id, 'falha', 'Falha ao reenviar manualmente');
                return false;
            }
        }
    }
    
    // Fallback para mail() nativo
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: EntreLinhas <noreply@entrelinhas.com>\r\n";
    
    $resultado = mail(
        $log['destinatario'],
        $log['assunto'],
        $log['conteudo'],
        $headers
    );
    
    if ($resultado) {
        update_email_log_status($log_id, 'sucesso', 'Reenviado via mail() nativo');
    } else {
        update_email_log_status($log_id, 'falha', 'Falha ao reenviar via mail() nativo');
    }
    
    return $resultado;
}

/**
 * Atualiza o status de um registro de log
 * 
 * @param int $log_id ID do registro de log
 * @param string $status Novo status
 * @param string $detalhes Detalhes adicionais
 * @return bool Resultado da atualização
 */
function update_email_log_status($log_id, $status, $detalhes = "") {
    global $conn;
    
    // Verificar se $conn está disponível, senão criar nova conexão
    if (!isset($conn) || $conn->connect_error) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Erro ao conectar ao banco de dados: " . $conn->connect_error);
            return false;
        }
    }
    
    $sql = "UPDATE email_log SET status_envio = ?, detalhes = CONCAT(detalhes, '\n', ?) WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Erro ao preparar consulta: " . $conn->error);
        return false;
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $detalhes_with_timestamp = "[{$timestamp}] {$detalhes}";
    
    $stmt->bind_param("ssi", $status, $detalhes_with_timestamp, $log_id);
    $resultado = $stmt->execute();
    
    $stmt->close();
    return $resultado;
}

// Inicializar a tabela de logs ao carregar este arquivo
ensure_email_log_table_exists();

// Definir constante para indicar que o email logger está disponível
if (!defined('EMAIL_LOGGER_LOADED')) {
    define('EMAIL_LOGGER_LOADED', true);
    error_log("Sistema de log de emails carregado com sucesso");
}

/**
 * Função auxiliar para facilitar o registro de notificações de artigo
 * 
 * @param array $artigo Dados do artigo
 * @param array $destinatarios Lista de e-mails dos destinatários
 * @param string $tipo_notificacao Tipo de notificação (novo_artigo, status_alterado, etc)
 * @return bool Resultado do registro
 */
function registrar_notificacao_artigo($artigo, $destinatarios, $tipo_notificacao = "novo_artigo") {
    // Verificar parâmetros
    if (!is_array($artigo) || empty($artigo['id']) || empty($artigo['titulo'])) {
        error_log("Dados do artigo inválidos ao registrar notificação");
        return false;
    }
    
    if (!is_array($destinatarios) || count($destinatarios) === 0) {
        error_log("Lista de destinatários vazia ao registrar notificação");
        return false;
    }
    
    // Determinar assunto e conteúdo conforme o tipo
    switch ($tipo_notificacao) {
        case 'novo_artigo':
            $assunto = "EntreLinhas: Novo artigo para aprovação - {$artigo['titulo']}";
            $conteudo = "<html><body>";
            $conteudo .= "<h2>Um novo artigo foi enviado para aprovação</h2>";
            $conteudo .= "<p><strong>Título:</strong> {$artigo['titulo']}</p>";
            $conteudo .= "<p><strong>Data de envio:</strong> " . date("d/m/Y H:i:s") . "</p>";
            if (!empty($artigo['conteudo'])) {
                $conteudo .= "<p><strong>Resumo:</strong> " . substr(strip_tags($artigo['conteudo']), 0, 200) . "...</p>";
            }
            $conteudo .= "<p>Para revisar e aprovar este artigo, acesse o <a href='http://entrelinhas.infinityfreeapp.com/PAGES/admin_dashboard.php'>Painel de Administração</a>.</p>";
            $conteudo .= "</body></html>";
            break;
            
        case 'status_alterado':
            $status_texto = !empty($artigo['status']) ? $artigo['status'] : 'atualizado';
            $assunto = "EntreLinhas: Status do artigo alterado - {$artigo['titulo']}";
            $conteudo = "<html><body>";
            $conteudo .= "<h2>O status de um artigo foi alterado</h2>";
            $conteudo .= "<p><strong>Título:</strong> {$artigo['titulo']}</p>";
            $conteudo .= "<p><strong>Novo status:</strong> {$status_texto}</p>";
            $conteudo .= "<p><strong>Data de atualização:</strong> " . date("d/m/Y H:i:s") . "</p>";
            $conteudo .= "<p>Para visualizar este artigo, acesse o <a href='http://entrelinhas.infinityfreeapp.com/PAGES/artigo.php?id={$artigo['id']}'>link do artigo</a>.</p>";
            $conteudo .= "</body></html>";
            break;
            
        default:
            $assunto = "EntreLinhas: Notificação sobre artigo - {$artigo['titulo']}";
            $conteudo = "<html><body>";
            $conteudo .= "<h2>Notificação do EntreLinhas</h2>";
            $conteudo .= "<p><strong>Artigo:</strong> {$artigo['titulo']}</p>";
            $conteudo .= "<p><strong>Data:</strong> " . date("d/m/Y H:i:s") . "</p>";
            $conteudo .= "</body></html>";
    }
    
    // Registrar no log para cada destinatário
    $sucesso = true;
    $status = is_development_environment() ? 'simulado' : 'sucesso';
    $metodo = is_development_environment() ? 'simulado' : 'sendgrid';
    $detalhes = is_development_environment() 
              ? "Email simulado em ambiente de desenvolvimento ({$tipo_notificacao})" 
              : "Notificação registrada ({$tipo_notificacao})";
    
    foreach ($destinatarios as $email) {
        $resultado = log_email(
            $artigo['id'],
            $email,
            $assunto,
            $conteudo,
            $status,
            $metodo,
            $detalhes
        );
        
        if (!$resultado) {
            $sucesso = false;
        }
    }
    
    return $sucesso;
}
?>

