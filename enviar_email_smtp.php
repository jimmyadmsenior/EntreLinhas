<?php
/**
 * enviar_email_smtp.php
 * 
 * Script para enviar e-mails usando SMTP direto (sem dependências)
 * Usa fsockopen para se conectar diretamente ao servidor SMTP
 */

/**
 * Envia um e-mail usando conexão direta SMTP
 * 
 * @param string $to_email E-mail do destinatário
 * @param string $subject Assunto do e-mail
 * @param string $html_message Mensagem em HTML
 * @param array $config Configurações do servidor SMTP
 * @return array Resultado da operação
 */
function enviar_email_smtp($to_email, $subject, $html_message, $config = []) {
    // Configurações padrão
    $config = array_merge([
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'jimmycastilho555@gmail.com',
        'smtp_password' => 'minhasenha', // Substitua pela senha correta
        'from_email' => 'jimmycastilho555@gmail.com',
        'from_name' => 'EntreLinhas',
        'timeout' => 30,
        'debug' => true
    ], $config);
    
    // Mensagem de depuração
    function debug_message($message, $debug = true) {
        if ($debug) {
            echo $message . "\n";
        }
    }
    
    // Abrir conexão com o servidor SMTP
    debug_message("Conectando ao servidor SMTP {$config['smtp_host']}:{$config['smtp_port']}...", $config['debug']);
    $socket = fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, $config['timeout']);
    
    if (!$socket) {
        return [
            'success' => false,
            'message' => "Não foi possível conectar ao servidor SMTP: $errstr ($errno)",
            'method' => 'smtp_direto'
        ];
    }
    
    // Definir timeout para leitura/escrita
    stream_set_timeout($socket, $config['timeout']);
    
    // Ler resposta inicial do servidor
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    
    if (substr($response, 0, 3) != '220') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Servidor SMTP não retornou saudação adequada: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Enviar comando EHLO
    debug_message("CLIENTE: EHLO {$_SERVER['SERVER_NAME']}", $config['debug']);
    fputs($socket, "EHLO {$_SERVER['SERVER_NAME']}\r\n");
    
    // Processar respostas do EHLO (pode ter várias linhas)
    do {
        $response = fgets($socket, 515);
        debug_message("SERVIDOR: $response", $config['debug']);
    } while (substr($response, 3, 1) == '-');
    
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Falha no EHLO: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Iniciar TLS se necessário
    if ($config['smtp_port'] == 587) {
        debug_message("CLIENTE: STARTTLS", $config['debug']);
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        debug_message("SERVIDOR: $response", $config['debug']);
        
        if (substr($response, 0, 3) != '220') {
            fclose($socket);
            return [
                'success' => false,
                'message' => "Falha ao iniciar TLS: $response",
                'method' => 'smtp_direto'
            ];
        }
        
        // Atualizar conexão para TLS
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        // Repetir EHLO após TLS
        debug_message("CLIENTE: EHLO {$_SERVER['SERVER_NAME']}", $config['debug']);
        fputs($socket, "EHLO {$_SERVER['SERVER_NAME']}\r\n");
        
        // Processar respostas do EHLO
        do {
            $response = fgets($socket, 515);
            debug_message("SERVIDOR: $response", $config['debug']);
        } while (substr($response, 3, 1) == '-');
        
        if (substr($response, 0, 3) != '250') {
            fclose($socket);
            return [
                'success' => false,
                'message' => "Falha no EHLO após TLS: $response",
                'method' => 'smtp_direto'
            ];
        }
    }
    
    // Autenticação
    debug_message("CLIENTE: AUTH LOGIN", $config['debug']);
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    
    if (substr($response, 0, 3) != '334') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Falha ao iniciar autenticação: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Enviar nome de usuário (base64)
    debug_message("CLIENTE: [username em base64]", $config['debug']);
    fputs($socket, base64_encode($config['smtp_username']) . "\r\n");
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    
    if (substr($response, 0, 3) != '334') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Falha ao enviar usuário: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Enviar senha (base64)
    debug_message("CLIENTE: [password em base64]", $config['debug']);
    fputs($socket, base64_encode($config['smtp_password']) . "\r\n");
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    
    if (substr($response, 0, 3) != '235') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Falha na autenticação: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Comando MAIL FROM
    debug_message("CLIENTE: MAIL FROM: <{$config['from_email']}>", $config['debug']);
    fputs($socket, "MAIL FROM: <{$config['from_email']}>\r\n");
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Falha no MAIL FROM: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Comando RCPT TO
    debug_message("CLIENTE: RCPT TO: <{$to_email}>", $config['debug']);
    fputs($socket, "RCPT TO: <{$to_email}>\r\n");
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Falha no RCPT TO: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Iniciar dados
    debug_message("CLIENTE: DATA", $config['debug']);
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    
    if (substr($response, 0, 3) != '354') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Falha no comando DATA: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Construir o cabeçalho e corpo da mensagem
    $boundary = md5(time());
    $plain_message = strip_tags($html_message);
    
    $headers = "From: {$config['from_name']} <{$config['from_email']}>\r\n";
    $headers .= "To: <{$to_email}>\r\n";
    $headers .= "Subject: {$subject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    
    $message_body = "--{$boundary}\r\n";
    $message_body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message_body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message_body .= chunk_split(base64_encode($plain_message)) . "\r\n";
    
    $message_body .= "--{$boundary}\r\n";
    $message_body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message_body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message_body .= chunk_split(base64_encode($html_message)) . "\r\n";
    
    $message_body .= "--{$boundary}--\r\n";
    
    $email_message = $headers . "\r\n" . $message_body . "\r\n.\r\n";
    
    // Enviar dados do e-mail
    debug_message("CLIENTE: [Enviando dados do e-mail]", $config['debug']);
    fputs($socket, $email_message);
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        return [
            'success' => false,
            'message' => "Falha ao enviar e-mail: $response",
            'method' => 'smtp_direto'
        ];
    }
    
    // Encerrar conexão
    debug_message("CLIENTE: QUIT", $config['debug']);
    fputs($socket, "QUIT\r\n");
    $response = fgets($socket, 515);
    debug_message("SERVIDOR: $response", $config['debug']);
    fclose($socket);
    
    return [
        'success' => true,
        'message' => "E-mail enviado com sucesso para {$to_email}",
        'method' => 'smtp_direto'
    ];
}

// Se o script for executado diretamente, permitir testes
if (basename(__FILE__) == basename($_SERVER['PHP_SELF']) || (isset($argc) && $argc > 0)) {
    // Verificar argumentos da linha de comando
    if (isset($argv) && $argc < 3) {
        echo "Uso: php enviar_email_smtp.php [destinatario] [assunto] [mensagem]\n";
        echo "  destinatario: Endereço de e-mail para envio\n";
        echo "  assunto: Assunto do e-mail\n";
        echo "  mensagem: Texto da mensagem (pode ser HTML)\n";
        exit(1);
    }
    
    // Dados do e-mail via linha de comando
    $to = isset($argv[1]) ? $argv[1] : '';
    $subject = isset($argv[2]) ? $argv[2] : '';
    $message = isset($argv[3]) ? $argv[3] : '';
    
    // Configurar o SMTP (ajuste conforme necessário)
    $smtp_config = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'jimmycastilho555@gmail.com',
        'smtp_password' => 'suasenhaaqui', // Substitua pela senha real
        'debug' => true
    ];
    
    // Enviar o e-mail
    if (!empty($to) && !empty($subject) && !empty($message)) {
        echo "Enviando e-mail SMTP para {$to}...\n";
        $result = enviar_email_smtp($to, $subject, $message, $smtp_config);
        
        // Exibir resultado
        if ($result['success']) {
            echo "✅ " . $result['message'] . "\n";
        } else {
            echo "❌ " . $result['message'] . "\n";
        }
    }
}
?>
