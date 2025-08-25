<?php
/**
 * Implementação de e-mail usando serviços de API Web
 * 
 * Este arquivo contém uma abordagem alternativa para envio de e-mails
 * utilizando serviços de API web como Mailgun, SendGrid ou outros,
 * que são mais confiáveis que a função mail() do PHP em ambientes
 * de produção.
 */

/**
 * INSTRUÇÕES PARA IMPLEMENTAÇÃO:
 * 
 * 1) Escolha um serviço de envio de e-mails:
 *    - Mailgun (https://www.mailgun.com) - Oferece 10.000 e-mails grátis por mês
 *    - SendGrid (https://sendgrid.com) - Oferece 100 e-mails grátis por dia
 *    - Amazon SES (https://aws.amazon.com/ses) - Preços baixos por volume
 * 
 * 2) Registre-se no serviço escolhido e obtenha suas credenciais de API
 * 
 * 3) Instale a biblioteca cliente apropriada via Composer:
 *    - Para Mailgun: composer require mailgun/mailgun-php symfony/http-client nyholm/psr7
 *    - Para SendGrid: composer require sendgrid/sendgrid
 * 
 * 4) Atualize a constante API_SERVICE abaixo para o serviço que você escolheu
 * 
 * 5) Configure suas credenciais de API nas constantes abaixo
 * 
 * 6) Substitua as chamadas para a função mail() nativa por chamadas para
 *    as funções deste arquivo em todo o projeto
 */

// Configurações Globais
define('API_SERVICE', 'LOG'); // Opções: 'MAILGUN', 'SENDGRID', 'LOG'
define('API_KEY', 'sua_api_key_aqui');
define('DOMAIN', 'seu_dominio.com'); // Para Mailgun
define('FROM_EMAIL', 'noreply@entrelinhas.com');
define('FROM_NAME', 'EntreLinhas');

// Função para registrar tentativas de envio de e-mail em ambiente de desenvolvimento
function logEmail($to, $subject, $body, $result = null) {
    // Verificar se o diretório de logs existe
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] TO: {$to} | SUBJECT: {$subject}\n";
    $log_entry .= "RESULT: " . ($result === null ? "ATTEMPTED" : ($result ? "SUCCESS" : "FAILED")) . "\n";
    $log_entry .= "BODY: " . substr(strip_tags($body), 0, 500) . "...\n";
    $log_entry .= "------------------------------------------------\n";
    
    file_put_contents($log_dir . '/email_api.log', $log_entry, FILE_APPEND);
}

/**
 * Envia e-mail usando o serviço configurado
 *
 * @param string $to Endereço de e-mail do destinatário
 * @param string $subject Assunto do e-mail
 * @param string $body Corpo do e-mail (HTML)
 * @param string $plain_text Versão em texto plano do corpo (opcional)
 * @return bool Se o e-mail foi enviado com sucesso
 */
function sendMailAPI($to, $subject, $body, $plain_text = '') {
    // Gerar texto plano se não foi fornecido
    if (empty($plain_text)) {
        $plain_text = strip_tags($body);
    }
    
    // Registrar tentativa
    logEmail($to, $subject, $body);
    
    // Verificar qual serviço usar
    switch (API_SERVICE) {
        case 'MAILGUN':
            return sendMailgunEmail($to, $subject, $body, $plain_text);
        
        case 'SENDGRID':
            return sendSendgridEmail($to, $subject, $body, $plain_text);
        
        case 'LOG':
        default:
            // Em modo de log, apenas registrar a tentativa (para ambiente de desenvolvimento)
            error_log("SIMULAÇÃO DE E-MAIL: Para: {$to}, Assunto: {$subject}");
            
            // Em produção, você pode querer retornar false aqui para indicar falha
            return true;
    }
}

/**
 * Envia e-mail usando Mailgun API
 */
function sendMailgunEmail($to, $subject, $body, $plain_text) {
    if (!defined('API_KEY') || !defined('DOMAIN')) {
        error_log("MAILGUN API: Credenciais não definidas.");
        return false;
    }
    
    // Aqui você implementaria o código real usando a API Mailgun
    // Exemplo com cURL (você pode usar a biblioteca oficial em produção)
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, "https://api.mailgun.net/v3/" . DOMAIN . "/messages");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "api:" . API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'from' => FROM_NAME . ' <' . FROM_EMAIL . '>',
        'to' => $to,
        'subject' => $subject,
        'html' => $body,
        'text' => $plain_text
    ]);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $success = ($http_code == 200);
    
    // Registrar resultado
    logEmail($to, $subject, $body, $success);
    
    return $success;
}

/**
 * Envia e-mail usando SendGrid API
 */
function sendSendgridEmail($to, $subject, $body, $plain_text) {
    if (!defined('API_KEY')) {
        error_log("SENDGRID API: Credenciais não definidas.");
        return false;
    }
    
    // Aqui você implementaria o código real usando a API SendGrid
    // Exemplo com cURL (você pode usar a biblioteca oficial em produção)
    $data = [
        'personalizations' => [
            [
                'to' => [['email' => $to]],
                'subject' => $subject
            ]
        ],
        'from' => ['email' => FROM_EMAIL, 'name' => FROM_NAME],
        'content' => [
            ['type' => 'text/plain', 'value' => $plain_text],
            ['type' => 'text/html', 'value' => $body]
        ]
    ];
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, "https://api.sendgrid.com/v3/mail/send");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . API_KEY,
        'Content-Type: application/json'
    ]);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $success = ($http_code == 202);
    
    // Registrar resultado
    logEmail($to, $subject, $body, $success);
    
    return $success;
}

/**
 * Função para enviar notificação aos administradores sobre um novo artigo
 */
function notificarAdminsNovoArtigo($artigo, $autor) {
    // Lista de e-mails dos administradores
    $admin_emails = [
        'jimmycastilho555@gmail.com',
        // Adicione outros e-mails de administradores conforme necessário
    ];
    
    $assunto = "EntreLinhas: Novo artigo para aprovação - {$artigo['titulo']}";
    
    $mensagem = "<html><body>";
    $mensagem .= "<h2>Um novo artigo foi enviado para aprovação</h2>";
    $mensagem .= "<p><strong>Título:</strong> {$artigo['titulo']}</p>";
    $mensagem .= "<p><strong>Autor:</strong> {$autor}</p>";
    $mensagem .= "<p><strong>Data de envio:</strong> " . date("d/m/Y H:i:s") . "</p>";
    $mensagem .= "<p><strong>Resumo:</strong> " . substr(strip_tags($artigo['conteudo']), 0, 200) . "...</p>";
    $mensagem .= "<p>Para revisar e aprovar este artigo, acesse o <a href='http://localhost:8000/PAGES/admin_dashboard.php'>Painel de Administração</a>.</p>";
    $mensagem .= "</body></html>";
    
    // Versão em texto plano
    $texto_plano = "Um novo artigo foi enviado para aprovação\n\n";
    $texto_plano .= "Título: {$artigo['titulo']}\n";
    $texto_plano .= "Autor: {$autor}\n";
    $texto_plano .= "Data de envio: " . date("d/m/Y H:i:s") . "\n\n";
    $texto_plano .= "Resumo: " . substr(strip_tags($artigo['conteudo']), 0, 200) . "...\n\n";
    $texto_plano .= "Para revisar e aprovar este artigo, acesse: http://localhost:8000/PAGES/admin_dashboard.php";
    
    // Enviar e-mail para cada administrador
    $sucessos = 0;
    
    foreach ($admin_emails as $email) {
        if (sendMailAPI($email, $assunto, $mensagem, $texto_plano)) {
            $sucessos++;
        }
    }
    
    return $sucessos > 0;
}
?>
