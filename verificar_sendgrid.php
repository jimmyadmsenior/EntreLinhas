<?php
// verificar_sendgrid.php
// Ferramenta para verificação de requisitos e teste de envio do SendGrid
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// Funções de verificação
function check_status($condition, $success_msg, $failure_msg) {
    if ($condition) {
        return "<span class='success'>✓ $success_msg</span>";
    } else {
        return "<span class='error'>✗ $failure_msg</span>";
    }
}

// Verificações do Sistema
$php_ok = version_compare(PHP_VERSION, '7.4.0', '>=');
$curl_ok = extension_loaded('curl');
$json_ok = extension_loaded('json');
$ssl_ok = extension_loaded('openssl');
$dns_ok = function_exists('gethostbyname');

// Verificação de conectividade
function check_connection($host, $port=443, $timeout=5) {
    $connection = @fsockopen('ssl://' . $host, $port, $errno, $errstr, $timeout);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    return false;
}

$sendgrid_api_connection = check_connection('api.sendgrid.com');

// Verificar API Key
$api_key = "CHAVE_SENDGRID_REMOVIDA";
$api_key_valid_format = (strpos($api_key, 'SG.') === 0 && strlen($api_key) > 20);

// Verificar arquivo de configuração
$config_file = __DIR__ . '/backend/sendgrid_email.php';
$config_exists = file_exists($config_file);
$config_content = $config_exists ? file_get_contents($config_file) : '';
$config_has_api_key = $config_exists && strpos($config_content, '$api_key') !== false;

// Envio de e-mail de teste se solicitado
$email_result = null;
$email_details = null;

if (isset($_POST['test_email']) && !empty($_POST['to_email'])) {
    $to_email = $_POST['to_email'];
    
    // Função para envio de e-mail de teste
    function send_test_email($to, $api_key) {
        $url = 'https://api.sendgrid.com/v3/mail/send';
        
        $data = [
            'personalizations' => [
                [
                    'to' => [
                        [
                            'email' => $to
                        ]
                    ],
                    'subject' => 'Teste de Verificação SendGrid - EntreLinhas'
                ]
            ],
            'from' => [
                'email' => 'noreply@entrelinhas.com',
                'name' => 'EntreLinhas'
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => '<h2>Teste de Envio do SendGrid</h2><p>Este é um e-mail de teste para verificar a conexão do sistema EntreLinhas com o SendGrid.</p><p>Horário do envio: ' . date('Y-m-d H:i:s') . '</p>'
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para teste apenas
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'success' => ($info['http_code'] == 202),
            'http_code' => $info['http_code'],
            'response' => $result,
            'error' => $error
        ];
    }
    
    $email_details = send_test_email($to_email, $api_key);
    $email_result = $email_details['success'] ? 'success' : 'error';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificação SendGrid - EntreLinhas</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { color: #444; margin-top: 30px; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: #34a853; font-weight: bold; }
        .warning { color: #fbbc05; font-weight: bold; }
        .error { color: #ea4335; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f2f2f2; }
        .notification { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .notification.success { background: #e6f4ea; border-left: 5px solid #34a853; }
        .notification.error { background: #fce8e6; border-left: 5px solid #ea4335; }
        .progress-bar { height: 20px; background: #e0e0e0; border-radius: 10px; margin-top: 5px; overflow: hidden; }
        .progress-fill { height: 100%; background: #4285f4; border-radius: 10px 0 0 10px; }
        button, input[type="submit"] { background: #4285f4; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover, input[type="submit"]:hover { background: #3367d6; }
        input[type="email"], input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verificação de Requisitos do SendGrid</h1>
        
        <div class="section">
            <h2>Requisitos do Sistema</h2>
            <table>
                <tr>
                    <th style="width: 60%;">Item</th>
                    <th style="width: 40%;">Status</th>
                </tr>
                <tr>
                    <td>PHP v7.4 ou superior</td>
                    <td><?php echo check_status($php_ok, 'PHP ' . PHP_VERSION . ' (Compatível)', 'PHP ' . PHP_VERSION . ' (Incompatível)'); ?></td>
                </tr>
                <tr>
                    <td>Extensão cURL</td>
                    <td><?php echo check_status($curl_ok, 'Instalada', 'Não Instalada'); ?></td>
                </tr>
                <tr>
                    <td>Extensão JSON</td>
                    <td><?php echo check_status($json_ok, 'Instalada', 'Não Instalada'); ?></td>
                </tr>
                <tr>
                    <td>Suporte a SSL (OpenSSL)</td>
                    <td><?php echo check_status($ssl_ok, 'Instalado', 'Não Instalado'); ?></td>
                </tr>
                <tr>
                    <td>Suporte a Resolução DNS</td>
                    <td><?php echo check_status($dns_ok, 'Disponível', 'Indisponível'); ?></td>
                </tr>
            </table>
            
            <?php
            $req_count = 0;
            $req_total = 5;
            if ($php_ok) $req_count++;
            if ($curl_ok) $req_count++;
            if ($json_ok) $req_count++;
            if ($ssl_ok) $req_count++;
            if ($dns_ok) $req_count++;
            
            $req_percent = ($req_count / $req_total) * 100;
            ?>
            
            <p><strong>Progresso de Requisitos: <?php echo $req_count; ?>/<?php echo $req_total; ?> (<?php echo round($req_percent); ?>%)</strong></p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $req_percent; ?>%"></div>
            </div>
        </div>
        
        <div class="section">
            <h2>Configuração SendGrid</h2>
            <table>
                <tr>
                    <th style="width: 60%;">Item</th>
                    <th style="width: 40%;">Status</th>
                </tr>
                <tr>
                    <td>Arquivo sendgrid_email.php</td>
                    <td><?php echo check_status($config_exists, 'Encontrado', 'Não Encontrado'); ?></td>
                </tr>
                <tr>
                    <td>Chave de API SendGrid configurada</td>
                    <td><?php echo check_status($config_has_api_key, 'Configurada', 'Não Configurada'); ?></td>
                </tr>
                <tr>
                    <td>Formato da Chave de API válido</td>
                    <td><?php echo check_status($api_key_valid_format, 'Válido', 'Inválido'); ?></td>
                </tr>
                <tr>
                    <td>Conexão com SendGrid API</td>
                    <td><?php echo check_status($sendgrid_api_connection, 'Conectável', 'Não Conectável'); ?></td>
                </tr>
            </table>
            
            <?php
            $config_count = 0;
            $config_total = 4;
            if ($config_exists) $config_count++;
            if ($config_has_api_key) $config_count++;
            if ($api_key_valid_format) $config_count++;
            if ($sendgrid_api_connection) $config_count++;
            
            $config_percent = ($config_count / $config_total) * 100;
            ?>
            
            <p><strong>Progresso de Configuração: <?php echo $config_count; ?>/<?php echo $config_total; ?> (<?php echo round($config_percent); ?>%)</strong></p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $config_percent; ?>%"></div>
            </div>
        </div>
        
        <?php if ($email_result): ?>
        <div class="notification <?php echo $email_result; ?>">
            <?php if ($email_result === 'success'): ?>
                <h3>✓ E-mail de Teste Enviado com Sucesso!</h3>
                <p>O e-mail foi enviado para <?php echo htmlspecialchars($_POST['to_email']); ?> com sucesso.</p>
                <p>Horário do envio: <?php echo date('Y-m-d H:i:s'); ?></p>
                <p>Código de resposta: HTTP <?php echo $email_details['http_code']; ?></p>
            <?php else: ?>
                <h3>✗ Falha no Envio do E-mail de Teste</h3>
                <p>Não foi possível enviar o e-mail para <?php echo htmlspecialchars($_POST['to_email']); ?>.</p>
                <p>Código de resposta: HTTP <?php echo $email_details['http_code']; ?></p>
                <p>Erro: <?php echo $email_details['error'] ? $email_details['error'] : 'Veja a resposta abaixo'; ?></p>
                <pre style="background: #f8f9fa; padding: 10px; overflow-x: auto;"><?php echo $email_details['response']; ?></pre>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Enviar E-mail de Teste</h2>
            <form method="post" action="">
                <div>
                    <label for="to_email">Endereço de E-mail para Teste:</label>
                    <input type="email" id="to_email" name="to_email" required placeholder="seuemail@exemplo.com">
                </div>
                <div style="margin-top: 15px;">
                    <input type="submit" name="test_email" value="Enviar E-mail de Teste">
                </div>
            </form>
        </div>
        
        <div class="section">
            <h2>Dicas de Solução de Problemas</h2>
            <ul>
                <li><strong>Erro de cURL:</strong> Verifique se a extensão cURL está instalada e funcionando corretamente.</li>
                <li><strong>Erro de SSL:</strong> Verifique se a extensão OpenSSL está instalada e configurada.</li>
                <li><strong>Erro de Autenticação:</strong> Verifique se a chave de API do SendGrid está correta e ativa.</li>
                <li><strong>Bloqueio de Firewall:</strong> Verifique se o servidor pode fazer conexões HTTPS externas.</li>
                <li><strong>Problemas de DNS:</strong> Verifique se o servidor pode resolver nomes de domínio corretamente.</li>
                <li><strong>E-mail não recebido:</strong> Verifique pastas de spam e confirme que o endereço de e-mail está correto.</li>
            </ul>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <button onclick="location.reload();">Verificar Novamente</button>
            <p>
                <a href="diagnostico_sendgrid.php">Diagnóstico SendGrid</a> | 
                <a href="teste_sendgrid_direto.php">Teste de Envio Direto</a> | 
                <a href="diagnostico_sistema.php">Diagnóstico do Sistema</a>
            </p>
        </div>
    </div>
</body>
</html>
