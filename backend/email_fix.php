<?php
/**
 * Solução temporária para problemas de e-mail
 * Este arquivo substitui o comportamento padrão de envio de email em ambiente de desenvolvimento
 */

// Verificar se estamos em ambiente de desenvolvimento
function is_development_env() {
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    return $server_name == 'localhost' || 
           strpos($server_name, '127.0.0.1') !== false || 
           strpos($server_name, '192.168.') === 0;
}

// Função para registrar tentativas de envio de email em log
function log_email_attempt($subject, $to, $body, $success = true) {
    $log_dir = dirname(__DIR__) . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/email_simulate.log';
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SIMULADO' : 'FALHA';
    
    $log_message = "[{$timestamp}] [{$status}] Para: {$to}, Assunto: {$subject}\n";
    $log_message .= "Conteúdo: " . substr(strip_tags($body), 0, 150) . "...\n\n";
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Sobrescrever função de notificação para ambiente de desenvolvimento
function notificar_admins_novo_artigo($artigo, $autor) {
    if (is_development_env()) {
        // Em ambiente de desenvolvimento, apenas simular o envio
        error_log('[EMAIL SIMULADO] Notificação sobre novo artigo: ' . $artigo['titulo'] . ' por ' . $autor);
        
        // Criar mensagem que seria enviada
        $assunto = "EntreLinhas: Novo artigo para aprovação - {$artigo['titulo']}";
        $mensagem = "<html><body>";
        $mensagem .= "<h2>Um novo artigo foi enviado para aprovação</h2>";
        $mensagem .= "<p><strong>Título:</strong> {$artigo['titulo']}</p>";
        $mensagem .= "<p><strong>Autor:</strong> {$autor}</p>";
        $mensagem .= "<p><strong>Data de envio:</strong> " . date("d/m/Y H:i:s") . "</p>";
        $mensagem .= "<p><strong>Resumo:</strong> " . substr(strip_tags($artigo['conteudo']), 0, 200) . "...</p>";
        $mensagem .= "<p>Este é um e-mail simulado em ambiente de desenvolvimento.</p>";
        $mensagem .= "</body></html>";
        
        // Registrar no log
        log_email_attempt($assunto, ADMIN_EMAIL, $mensagem);
        
        return true;
    }
    
    // Em produção, usar a função original (se existir)
    if (function_exists('notificar_admins_artigo_original')) {
        return notificar_admins_artigo_original($artigo, $autor);
    }
    
    // Se a função original não existe, usar a implementação padrão
    return false;
}

// Sobrescrever as funções do SendGrid em ambiente de desenvolvimento
if (is_development_env() && !function_exists('sendEmail_original')) {
    // Salvar referência para função original de envio, se existir
    if (function_exists('sendEmail')) {
        function sendEmail_original($to, $subject, $html_content, $plain_content = '') {
            return sendEmail($to, $subject, $html_content, $plain_content);
        }
    }
    
    // Substituir função de envio por uma simulada
    function sendEmail($to, $subject, $html_content, $plain_content = '') {
        error_log("[EMAIL SIMULADO SendGrid] Para: {$to}, Assunto: {$subject}");
        
        // Registrar no log
        log_email_attempt($subject, $to, $html_content);
        
        return true; // Simular sucesso
    }
}

// Mensagem no log indicando que a solução foi carregada
error_log('[' . date('Y-m-d H:i:s') . '] Solução de e-mail carregada para ambiente de desenvolvimento');
?>
