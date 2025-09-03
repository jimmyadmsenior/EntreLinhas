<?php
// Arquivo para diagnosticar problemas com envio de e-mails
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Criar pasta de logs se não existir
if (!is_dir('logs')) {
    mkdir('logs', 0777, true);
}

// Configurar log de erros
ini_set('log_errors', 1);
ini_set('error_log', 'logs/email_debug.log');
error_log("===== INÍCIO DE TESTE DE EMAIL: " . date('Y-m-d H:i:s') . " =====");

echo "<h1>Diagnóstico de Envio de E-mails</h1>";

// Verificar configurações do PHP para e-mail
echo "<h2>Configurações do PHP para E-mail</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "mail() function exists: " . (function_exists('mail') ? "Yes" : "No") . "\n";
echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "SMTP: " . ini_get('SMTP') . "\n";
echo "smtp_port: " . ini_get('smtp_port') . "\n";
echo "mail.add_x_header: " . ini_get('mail.add_x_header') . "\n";
echo "mail.force_extra_parameters: " . ini_get('mail.force_extra_parameters') . "\n";
echo "</pre>";

// Verificar se temos permissão para criar arquivos na pasta logs
echo "<h2>Permissões de Escrita</h2>";
$test_file = "logs/test_write.tmp";
$result = @file_put_contents($test_file, "Teste de escrita");
if ($result === false) {
    echo "<p style='color: red'>Não foi possível escrever no arquivo de teste. Verifique as permissões.</p>";
} else {
    echo "<p style='color: green'>Permissões de escrita OK.</p>";
    @unlink($test_file);
}

// Testar envio básico de e-mail
echo "<h2>Teste Básico de E-mail</h2>";

$to = "jimmycastilho555@gmail.com";
$subject = "Teste de Email EntreLinhas - " . date('Y-m-d H:i:s');
$message = "Este é um e-mail de teste enviado em " . date('Y-m-d H:i:s');
$headers = "From: EntreLinhas <noreply@entrelinhas.com>\r\n";

error_log("[EMAIL TEST] Tentando enviar e-mail para: $to");
error_log("[EMAIL TEST] Assunto: $subject");
error_log("[EMAIL TEST] Headers: $headers");

$mail_result = @mail($to, $subject, $message, $headers);
if ($mail_result) {
    echo "<p style='color: green'>Função mail() retornou TRUE - O e-mail parece ter sido enviado.</p>";
    error_log("[EMAIL TEST] Função mail() retornou TRUE");
} else {
    echo "<p style='color: red'>Função mail() retornou FALSE - Falha ao enviar e-mail.</p>";
    error_log("[EMAIL TEST] Função mail() retornou FALSE");
    
    // Verificar último erro
    $error = error_get_last();
    if ($error) {
        echo "<p>Último erro: " . htmlspecialchars($error['message']) . "</p>";
        error_log("[EMAIL TEST] Último erro: " . $error['message']);
    }
}

// Teste HTML mail
echo "<h2>Teste de E-mail HTML</h2>";

$subject = "Teste HTML Email EntreLinhas - " . date('Y-m-d H:i:s');
$html_message = "
<html>
<head>
<title>Teste HTML</title>
</head>
<body>
<h2>Teste de E-mail HTML</h2>
<p>Este é um e-mail HTML de teste enviado em " . date('Y-m-d H:i:s') . "</p>
</body>
</html>
";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: EntreLinhas <noreply@entrelinhas.com>\r\n";

$html_mail_result = @mail($to, $subject, $html_message, $headers);
if ($html_mail_result) {
    echo "<p style='color: green'>Teste HTML: Função mail() retornou TRUE.</p>";
    error_log("[EMAIL TEST] Teste HTML: Função mail() retornou TRUE");
} else {
    echo "<p style='color: red'>Teste HTML: Função mail() retornou FALSE.</p>";
    error_log("[EMAIL TEST] Teste HTML: Função mail() retornou FALSE");
}

// Soluções Alternativas
echo "<h2>Soluções Alternativas</h2>";
echo "<h3>Opção 1: Usar biblioteca PHPMailer</h3>";
echo "<p>A biblioteca PHPMailer é uma solução robusta para envio de e-mails em PHP, com suporte a SMTP autenticado.</p>";
echo "<pre>
# Passos para instalar PHPMailer:

1. Instale o Composer (gerenciador de dependências PHP)
2. Execute no terminal: composer require phpmailer/phpmailer
3. Implemente o código PHP como no exemplo abaixo:

```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function enviarEmail(\$para, \$assunto, \$mensagem) {
    \$mail = new PHPMailer(true);
    try {
        //Server settings
        \$mail->isSMTP();
        \$mail->Host       = 'smtp.gmail.com'; // Servidor SMTP
        \$mail->SMTPAuth   = true;
        \$mail->Username   = 'seu_email@gmail.com';
        \$mail->Password   = 'sua_senha_app'; // Use senha de aplicativo para Gmail
        \$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        \$mail->Port       = 587;

        //Recipients
        \$mail->setFrom('noreply@entrelinhas.com', 'EntreLinhas');
        \$mail->addAddress(\$para);

        //Content
        \$mail->isHTML(true);
        \$mail->Subject = \$assunto;
        \$mail->Body    = \$mensagem;

        \$mail->send();
        return true;
    } catch (Exception \$e) {
        error_log('Erro ao enviar email: ' . \$mail->ErrorInfo);
        return false;
    }
}
```
</pre>";

echo "<h3>Opção 2: Usar serviço de API de e-mail</h3>";
echo "<p>Serviços como SendGrid, Mailgun ou Amazon SES oferecem APIs robustas para envio de e-mail:</p>";
echo "<pre>
# Exemplo com SendGrid:

1. Crie uma conta no SendGrid e obtenha uma API key
2. Instale a biblioteca: composer require sendgrid/sendgrid
3. Implemente o código:

```php
require 'vendor/autoload.php';

function enviarEmail(\$para, \$assunto, \$mensagem) {
    \$email = new \\SendGrid\\Mail\\Mail(); 
    \$email->setFrom('noreply@entrelinhas.com', 'EntreLinhas');
    \$email->setSubject(\$assunto);
    \$email->addTo(\$para);
    \$email->addContent('text/html', \$mensagem);
    
    \$sendgrid = new \\SendGrid('SUA_API_KEY');
    
    try {
        \$response = \$sendgrid->send(\$email);
        return \$response->statusCode() == 202;
    } catch (Exception \$e) {
        error_log('Erro ao enviar email: ' . \$e->getMessage());
        return false;
    }
}
```
</pre>";

echo "<h3>Opção 3: Configurar o SMTP no Windows</h3>";
echo "<p>Para ambiente de desenvolvimento local no Windows:</p>";
echo "<ol>
<li>Instale um servidor SMTP local como o <a href='https://github.com/rnwood/smtp4dev' target='_blank'>smtp4dev</a></li>
<li>Configure o php.ini com os seguintes valores:
<pre>
[mail function]
SMTP = localhost
smtp_port = 25
sendmail_from = noreply@entrelinhas.com
</pre>
</li>
<li>Reinicie o servidor PHP</li>
</ol>";

error_log("===== FIM DE TESTE DE EMAIL: " . date('Y-m-d H:i:s') . " =====");
?>
