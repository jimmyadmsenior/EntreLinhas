<?php
/**
 * enviar_email_nativo.php
 * 
 * Script para enviar e-mails usando a função mail() nativa do PHP
 * Alternativa para o SendGrid quando necessário
 */

/**
 * Envia um e-mail usando a função mail() do PHP
 * 
 * @param string $to_email E-mail do destinatário
 * @param string $subject Assunto do e-mail
 * @param string $html_message Mensagem em HTML
 * @param string $from_email E-mail do remetente
 * @param string $from_name Nome do remetente
 * @return array Resultado da operação
 */
function enviar_email_nativo($to_email, $subject, $html_message, $from_email = 'jimmycastilho555@gmail.com', $from_name = 'EntreLinhas') {
    // Gerar um boundary para o e-mail multipart
    $boundary = md5(time());
    
    // Cabeçalhos
    $headers = "From: {$from_name} <{$from_email}>\r\n";
    $headers .= "Reply-To: {$from_email}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    
    // Criar versão em texto puro
    $plain_message = strip_tags($html_message);
    
    // Corpo do e-mail
    $body = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($plain_message)) . "\r\n";
    
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($html_message)) . "\r\n";
    
    $body .= "--{$boundary}--\r\n";
    
    // Tentar enviar o e-mail
    try {
        $result = mail($to_email, $subject, $body, $headers);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'E-mail enviado com sucesso via função mail() nativa.',
                'http_code' => 200
            ];
        } else {
            $error = error_get_last();
            return [
                'success' => false,
                'message' => 'Falha ao enviar e-mail: ' . ($error ? $error['message'] : 'Erro desconhecido'),
                'http_code' => 0
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Exceção ao enviar e-mail: ' . $e->getMessage(),
            'http_code' => 0
        ];
    }
}

// Se o script for executado diretamente, permitir testes de linha de comando
if (basename(__FILE__) == basename($_SERVER['PHP_SELF']) || (isset($argc) && $argc > 0)) {
    // Verificar argumentos da linha de comando
    if (isset($argv) && $argc < 3) {
        echo "Uso: php enviar_email_nativo.php [destinatario] [assunto] [mensagem]\n";
        echo "  destinatario: Endereço de e-mail para envio\n";
        echo "  assunto: Assunto do e-mail\n";
        echo "  mensagem: Texto da mensagem (pode ser HTML)\n";
        exit(1);
    }
    
    // Dados do e-mail via linha de comando
    $to = isset($argv[1]) ? $argv[1] : '';
    $subject = isset($argv[2]) ? $argv[2] : '';
    $message = isset($argv[3]) ? $argv[3] : '';
    
    // Se as informações vierem da linha de comando
    if (!empty($to) && !empty($subject) && !empty($message)) {
        echo "Enviando e-mail para {$to}...\n";
        
        // Enviar o e-mail
        $result = enviar_email_nativo($to, $subject, $message);
        
        // Exibir resultado
        if ($result['success']) {
            echo "✅ " . $result['message'] . "\n";
        } else {
            echo "❌ " . $result['message'] . "\n";
        }
    }
}
?>
