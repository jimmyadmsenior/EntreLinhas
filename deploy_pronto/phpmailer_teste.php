<?php
// phpmailer_teste.php
// Script para testar envio de e-mails usando PHPMailer
// Nota: Este arquivo requer que o PHPMailer seja instalado via Composer

// Verificar se o Composer e o PHPMailer estão instalados
$composer_json_exists = file_exists(__DIR__ . '/composer.json');
$vendor_autoload_exists = file_exists(__DIR__ . '/vendor/autoload.php');
$phpmailer_installed = false;

// Se o autoload existir, vamos verificar se PHPMailer está disponível
if ($vendor_autoload_exists) {
    require_once __DIR__ . '/vendor/autoload.php';
    $phpmailer_installed = class_exists('PHPMailer\PHPMailer\PHPMailer');
}

// Função para instalar o PHPMailer via Composer
function instalar_phpmailer() {
    // Criar composer.json se não existir
    if (!file_exists(__DIR__ . '/composer.json')) {
        $composer_json = [
            'require' => [
                'phpmailer/phpmailer' => '^6.8'
            ]
        ];
        file_put_contents(__DIR__ . '/composer.json', json_encode($composer_json, JSON_PRETTY_PRINT));
    }
    
    // Executar composer install
    $output = [];
    $return_var = 0;
    
    exec('composer install', $output, $return_var);
    
    return [
        'sucesso' => ($return_var === 0),
        'output' => implode("\n", $output),
        'return_var' => $return_var
    ];
}

// Processar solicitações de instalação ou envio de e-mail
$message = null;
$message_type = null;
$install_output = null;
$email_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se é uma solicitação de instalação
    if (isset($_POST['instalar'])) {
        $install_result = instalar_phpmailer();
        
        if ($install_result['sucesso']) {
            $message = "PHPMailer instalado com sucesso! Por favor, recarregue a página.";
            $message_type = "success";
        } else {
            $message = "Erro ao instalar PHPMailer. Verifique se o Composer está instalado.";
            $message_type = "error";
        }
        
        $install_output = $install_result['output'];
    }
    
    // Verificar se é uma solicitação de envio de e-mail
    if (isset($_POST['enviar']) && $phpmailer_installed) {
        $to = $_POST['to'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $body = $_POST['body'] ?? '';
        $smtp_host = $_POST['smtp_host'] ?? '';
        $smtp_port = $_POST['smtp_port'] ?? '';
        $smtp_username = $_POST['smtp_username'] ?? '';
        $smtp_password = $_POST['smtp_password'] ?? '';
        $smtp_from = $_POST['smtp_from'] ?? '';
        
        // Usar PHPMailer
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configurações do servidor
            $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_CONNECTION; // Saída de depuração (0 = desativado, 2 = comandos e respostas)
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_username;
            $mail->Password   = $smtp_password;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtp_port;
            
            // Capturar saída de depuração
            ob_start();
            
            // Destinatários
            $mail->setFrom($smtp_from, 'EntreLinhas');
            $mail->addAddress($to);
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            // Enviar e-mail
            $mail->send();
            
            $debug_output = ob_get_clean();
            
            $message = "E-mail enviado com sucesso para {$to}";
            $message_type = "success";
            $email_result = [
                'success' => true,
                'debug' => $debug_output,
                'to' => $to,
                'subject' => $subject
            ];
        } catch (Exception $e) {
            $debug_output = ob_get_clean();
            
            $message = "Erro ao enviar e-mail: {$mail->ErrorInfo}";
            $message_type = "error";
            $email_result = [
                'success' => false,
                'error' => $mail->ErrorInfo,
                'debug' => $debug_output,
                'to' => $to,
                'subject' => $subject
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste PHPMailer - EntreLinhas</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { color: #444; margin-top: 30px; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], textarea, select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 150px; font-family: monospace; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 14px; }
        .buttons { margin-top: 20px; }
        button, input[type="submit"] { background: #4285f4; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover, input[type="submit"]:hover { background: #3367d6; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .message.success { background: #e6f4ea; border-left: 5px solid #34a853; }
        .message.error { background: #fce8e6; border-left: 5px solid #ea4335; }
        .message.info { background: #e8f0fe; border-left: 5px solid #4285f4; }
        .tabs { display: flex; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border: 1px solid #ddd; border-bottom: none; background: #f5f5f5; border-radius: 5px 5px 0 0; margin-right: 5px; }
        .tab.active { background: white; border-bottom: 2px solid white; margin-bottom: -1px; }
        .tab-content { display: none; border: 1px solid #ddd; padding: 20px; border-radius: 0 5px 5px 5px; }
        .tab-content.active { display: block; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; }
        .status.installed { background: #e6f4ea; color: #34a853; }
        .status.missing { background: #fce8e6; color: #ea4335; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Teste de Envio de E-mails com PHPMailer</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Status do PHPMailer</h2>
            
            <div style="margin-bottom: 15px;">
                <?php if ($phpmailer_installed): ?>
                    <span class="status installed">✓ PHPMailer Instalado</span>
                <?php else: ?>
                    <span class="status missing">✗ PHPMailer Não Instalado</span>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 15px;">
                <?php if ($composer_json_exists): ?>
                    <span class="status installed">✓ composer.json encontrado</span>
                <?php else: ?>
                    <span class="status missing">✗ composer.json não encontrado</span>
                <?php endif; ?>
                
                <?php if ($vendor_autoload_exists): ?>
                    <span class="status installed">✓ vendor/autoload.php encontrado</span>
                <?php else: ?>
                    <span class="status missing">✗ vendor/autoload.php não encontrado</span>
                <?php endif; ?>
            </div>
            
            <?php if (!$phpmailer_installed): ?>
                <div class="message info">
                    <p><strong>Nota:</strong> O PHPMailer não está instalado ou não foi detectado. Você pode instalá-lo usando o Composer.</p>
                    <p>1. Certifique-se de que o Composer está instalado em seu sistema.</p>
                    <p>2. Clique no botão "Instalar PHPMailer" abaixo.</p>
                </div>
                
                <form method="post" action="">
                    <div class="buttons">
                        <input type="submit" name="instalar" value="Instalar PHPMailer">
                    </div>
                </form>
                
                <?php if ($install_output): ?>
                    <h3>Saída da Instalação</h3>
                    <pre><?php echo $install_output; ?></pre>
                <?php endif; ?>
            <?php else: ?>
                <p>PHPMailer está instalado e pronto para uso.</p>
            <?php endif; ?>
        </div>
        
        <?php if ($phpmailer_installed): ?>
            <div class="tabs">
                <div class="tab active" onclick="showTab('gmail')">Gmail</div>
                <div class="tab" onclick="showTab('outlook')">Outlook</div>
                <div class="tab" onclick="showTab('custom')">Personalizado</div>
            </div>
            
            <div id="gmail-tab" class="tab-content active">
                <h2>Enviar E-mail via Gmail SMTP</h2>
                <form method="post" action="">
                    <input type="hidden" name="smtp_host" value="smtp.gmail.com">
                    <input type="hidden" name="smtp_port" value="587">
                    
                    <div class="form-group">
                        <label for="gmail-username">Seu E-mail Gmail:</label>
                        <input type="email" id="gmail-username" name="smtp_username" required placeholder="seuemail@gmail.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="gmail-password">Senha de App ou Senha da Conta:</label>
                        <input type="password" id="gmail-password" name="smtp_password" required placeholder="senha ou senha de app">
                        <p><small>Nota: Recomenda-se usar uma <a href="https://support.google.com/accounts/answer/185833" target="_blank">Senha de App</a> para maior segurança.</small></p>
                    </div>
                    
                    <input type="hidden" name="smtp_from" id="gmail-from">
                    
                    <div class="form-group">
                        <label for="gmail-to">E-mail do Destinatário:</label>
                        <input type="email" id="gmail-to" name="to" required placeholder="destinatario@exemplo.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="gmail-subject">Assunto:</label>
                        <input type="text" id="gmail-subject" name="subject" value="Teste de E-mail do EntreLinhas via PHPMailer">
                    </div>
                    
                    <div class="form-group">
                        <label for="gmail-body">Corpo do E-mail (HTML):</label>
                        <textarea id="gmail-body" name="body"><h2>Teste de E-mail via PHPMailer</h2><p>Olá,</p><p>Esta é uma mensagem de teste enviada pelo sistema EntreLinhas usando PHPMailer com SMTP do Gmail.</p><p>Horário do envio: <?php echo date('Y-m-d H:i:s'); ?></p><p>Atenciosamente,<br>Equipe EntreLinhas</p></textarea>
                    </div>
                    
                    <div class="buttons">
                        <input type="submit" name="enviar" value="Enviar E-mail">
                    </div>
                </form>
                <script>
                    document.getElementById('gmail-username').addEventListener('input', function() {
                        document.getElementById('gmail-from').value = this.value;
                    });
                </script>
            </div>
            
            <div id="outlook-tab" class="tab-content">
                <h2>Enviar E-mail via Outlook SMTP</h2>
                <form method="post" action="">
                    <input type="hidden" name="smtp_host" value="smtp-mail.outlook.com">
                    <input type="hidden" name="smtp_port" value="587">
                    
                    <div class="form-group">
                        <label for="outlook-username">Seu E-mail Outlook:</label>
                        <input type="email" id="outlook-username" name="smtp_username" required placeholder="seuemail@outlook.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="outlook-password">Senha da Conta:</label>
                        <input type="password" id="outlook-password" name="smtp_password" required placeholder="sua senha">
                    </div>
                    
                    <input type="hidden" name="smtp_from" id="outlook-from">
                    
                    <div class="form-group">
                        <label for="outlook-to">E-mail do Destinatário:</label>
                        <input type="email" id="outlook-to" name="to" required placeholder="destinatario@exemplo.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="outlook-subject">Assunto:</label>
                        <input type="text" id="outlook-subject" name="subject" value="Teste de E-mail do EntreLinhas via PHPMailer">
                    </div>
                    
                    <div class="form-group">
                        <label for="outlook-body">Corpo do E-mail (HTML):</label>
                        <textarea id="outlook-body" name="body"><h2>Teste de E-mail via PHPMailer</h2><p>Olá,</p><p>Esta é uma mensagem de teste enviada pelo sistema EntreLinhas usando PHPMailer com SMTP do Outlook.</p><p>Horário do envio: <?php echo date('Y-m-d H:i:s'); ?></p><p>Atenciosamente,<br>Equipe EntreLinhas</p></textarea>
                    </div>
                    
                    <div class="buttons">
                        <input type="submit" name="enviar" value="Enviar E-mail">
                    </div>
                </form>
                <script>
                    document.getElementById('outlook-username').addEventListener('input', function() {
                        document.getElementById('outlook-from').value = this.value;
                    });
                </script>
            </div>
            
            <div id="custom-tab" class="tab-content">
                <h2>Enviar E-mail via SMTP Personalizado</h2>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="custom-host">Servidor SMTP:</label>
                        <input type="text" id="custom-host" name="smtp_host" required placeholder="smtp.seuservidor.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-port">Porta SMTP:</label>
                        <input type="text" id="custom-port" name="smtp_port" required placeholder="587" value="587">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-username">Nome de Usuário SMTP:</label>
                        <input type="text" id="custom-username" name="smtp_username" required placeholder="seu_usuario">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-password">Senha SMTP:</label>
                        <input type="password" id="custom-password" name="smtp_password" required placeholder="sua_senha">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-from">E-mail de Origem:</label>
                        <input type="email" id="custom-from" name="smtp_from" required placeholder="seu@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-to">E-mail do Destinatário:</label>
                        <input type="email" id="custom-to" name="to" required placeholder="destinatario@exemplo.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-subject">Assunto:</label>
                        <input type="text" id="custom-subject" name="subject" value="Teste de E-mail do EntreLinhas via PHPMailer">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-body">Corpo do E-mail (HTML):</label>
                        <textarea id="custom-body" name="body"><h2>Teste de E-mail via PHPMailer</h2><p>Olá,</p><p>Esta é uma mensagem de teste enviada pelo sistema EntreLinhas usando PHPMailer com SMTP personalizado.</p><p>Horário do envio: <?php echo date('Y-m-d H:i:s'); ?></p><p>Atenciosamente,<br>Equipe EntreLinhas</p></textarea>
                    </div>
                    
                    <div class="buttons">
                        <input type="submit" name="enviar" value="Enviar E-mail">
                    </div>
                </form>
            </div>
            
            <?php if ($email_result): ?>
                <div class="section">
                    <h2>Resultado do Envio de E-mail</h2>
                    
                    <?php if ($email_result['success']): ?>
                        <div class="message success">
                            <h3>✓ E-mail Enviado com Sucesso!</h3>
                            <p>O e-mail foi enviado para <?php echo htmlspecialchars($email_result['to']); ?>.</p>
                            <p>Assunto: <?php echo htmlspecialchars($email_result['subject']); ?></p>
                            <p>Horário do envio: <?php echo date('Y-m-d H:i:s'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="message error">
                            <h3>✗ Falha ao Enviar E-mail</h3>
                            <p>Não foi possível enviar o e-mail para <?php echo htmlspecialchars($email_result['to']); ?>.</p>
                            <p>Erro: <?php echo htmlspecialchars($email_result['error']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <h3>Saída de Depuração</h3>
                    <pre><?php echo htmlspecialchars($email_result['debug']); ?></pre>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="section">
            <h2>Instruções de Uso</h2>
            
            <?php if ($phpmailer_installed): ?>
                <p>O PHPMailer está instalado e pronto para ser utilizado no seu código. Veja abaixo um exemplo de como integrar:</p>
                
                <pre>
// Exemplo de uso do PHPMailer no seu código
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviar_email($para, $assunto, $mensagem) {
    $mail = new PHPMailer(true);
    
    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host       = 'smtp.exemplo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'seu_usuario@exemplo.com';
        $mail->Password   = 'sua_senha';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Remetente e destinatário
        $mail->setFrom('seu_email@exemplo.com', 'EntreLinhas');
        $mail->addAddress($para);
        
        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;
        $mail->AltBody = strip_tags($mensagem);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
</pre>
            <?php else: ?>
                <p>Para usar o PHPMailer no seu código, primeiro é necessário instalá-lo seguindo as instruções acima.</p>
            <?php endif; ?>
            
            <h3>Dicas e Soluções de Problemas</h3>
            <ul>
                <li><strong>Credenciais Inválidas:</strong> Verifique se o usuário e senha estão corretos.</li>
                <li><strong>Gmail:</strong> Para o Gmail, é necessário utilizar uma <a href="https://support.google.com/accounts/answer/185833" target="_blank">Senha de App</a> ou habilitar "Apps menos seguros" (não recomendado).</li>
                <li><strong>Firewall:</strong> Verifique se o firewall está permitindo conexões nas portas SMTP (geralmente 25, 465 ou 587).</li>
                <li><strong>SSL/TLS:</strong> Certifique-se de que a extensão OpenSSL está habilitada no PHP.</li>
                <li><strong>Autenticação:</strong> Alguns servidores podem exigir configurações específicas de autenticação.</li>
                <li><strong>Limites de Envio:</strong> Provedores de e-mail podem ter limites diários de envio.</li>
            </ul>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <p>
                <a href="verificar_sendgrid.php">Verificar SendGrid</a> | 
                <a href="email_simples.php">E-mail Simples (mail)</a> | 
                <a href="diagnostico_sistema.php">Diagnóstico do Sistema</a>
            </p>
        </div>
    </div>
    
    <script>
        function showTab(tabId) {
            // Esconder todos os conteúdos de tabs
            var tabContents = document.getElementsByClassName('tab-content');
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Remover classe active de todas as tabs
            var tabs = document.getElementsByClassName('tab');
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            // Mostrar o conteúdo da tab selecionada
            document.getElementById(tabId + '-tab').classList.add('active');
            
            // Marcar a tab selecionada como ativa
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>
