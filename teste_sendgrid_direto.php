<?php
// Teste direto de envio via SendGrid
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste Direto SendGrid</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .log { background: #f5f5f5; border: 1px solid #ddd; padding: 10px; white-space: pre-wrap; font-family: monospace; margin: 10px 0; height: 200px; overflow: auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        h2 { margin-top: 30px; }
        button, input[type="submit"] { background: #4CAF50; color: white; border: none; padding: 10px 15px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Teste Direto da API do SendGrid</h1>
        <p>Esta página testa o envio de e-mail diretamente pelo código, sem usar funções externas.</p>
        
        <?php
        // Verificar se cURL está disponível
        if (!function_exists('curl_init')) {
            echo '<p class="error">ERRO: A extensão cURL não está disponível no PHP!</p>';
            exit;
        } else {
            echo '<p class="success">✓ cURL está disponível</p>';
        }
        
        // Criar log para registro
        $log_file = 'sendgrid_test_log.txt';
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Iniciando teste direto\n", FILE_APPEND);
        
        // Se o formulário foi enviado
        if (isset($_POST['enviar'])) {
            $to_email = $_POST['to_email'];
            $subject = $_POST['subject'];
            $message = $_POST['message'];
            $api_key = $_POST['api_key'];
            
            echo '<h2>Enviando e-mail...</h2>';
            echo '<div class="log">';
            
            // Log da tentativa
            echo "Tentando enviar e-mail para: {$to_email}\n";
            echo "Assunto: {$subject}\n";
            echo "API Key usada: " . substr($api_key, 0, 10) . "...\n\n";
            
            // Preparar dados para envio
            $data = [
                'personalizations' => [
                    [
                        'to' => [
                            [
                                'email' => $to_email
                            ]
                        ],
                        'subject' => $subject
                    ]
                ],
                'from' => [
                    'email' => 'jimmycastilho555@gmail.com',
                    'name' => 'EntreLinhas Teste'
                ],
                'content' => [
                    [
                        'type' => 'text/plain',
                        'value' => strip_tags($message)
                    ],
                    [
                        'type' => 'text/html',
                        'value' => $message
                    ]
                ]
            ];
            
            // Converter para JSON
            $json_data = json_encode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = "Erro ao converter dados para JSON: " . json_last_error_msg();
                echo "ERRO: {$error}\n";
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - {$error}\n", FILE_APPEND);
                echo '</div>';
                exit;
            }
            
            echo "Dados JSON preparados com sucesso.\n";
            
            // Inicializar cURL
            $ch = curl_init();
            if ($ch === false) {
                $error = "Falha ao inicializar cURL";
                echo "ERRO: {$error}\n";
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - {$error}\n", FILE_APPEND);
                echo '</div>';
                exit;
            }
            
            echo "cURL inicializado com sucesso.\n";
            
            // Configurar requisição
            curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desabilitar verificação de SSL para teste
            
            echo "Opções do cURL configuradas.\n";
            echo "Enviando requisição para a API do SendGrid...\n\n";
            
            // Executar requisição
            $response = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Verificar erros
            if ($curl_errno) {
                echo "ERRO cURL #{$curl_errno}: {$curl_error}\n";
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Erro cURL #{$curl_errno}: {$curl_error}\n", FILE_APPEND);
            } else {
                echo "Requisição cURL executada sem erros.\n";
            }
            
            echo "Código HTTP recebido: {$http_code}\n";
            echo "Resposta recebida:\n{$response}\n";
            
            // Verificar sucesso (código 202)
            if ($http_code == 202) {
                echo "\n✅ E-MAIL ENVIADO COM SUCESSO!\n";
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - E-mail enviado com sucesso para {$to_email}\n", FILE_APPEND);
            } else {
                echo "\n❌ FALHA AO ENVIAR E-MAIL. Código: {$http_code}\n";
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Falha ao enviar e-mail para {$to_email}, código: {$http_code}, resposta: {$response}\n", FILE_APPEND);
            }
            
            // Fechar cURL
            curl_close($ch);
            echo "\nConexão cURL fechada.\n";
            echo '</div>';
        }
        ?>
        
        <h2>Formulário de Teste</h2>
        <form method="post" action="">
            <div>
                <label for="to_email">E-mail do destinatário:</label><br>
                <input type="email" id="to_email" name="to_email" required style="width: 300px;" 
                      value="<?php echo isset($_POST['to_email']) ? htmlspecialchars($_POST['to_email']) : ''; ?>">
            </div>
            <div style="margin-top: 10px;">
                <label for="subject">Assunto:</label><br>
                <input type="text" id="subject" name="subject" required style="width: 300px;"
                      value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : 'Teste de E-mail do EntreLinhas'; ?>">
            </div>
            <div style="margin-top: 10px;">
                <label for="message">Mensagem (HTML):</label><br>
                <textarea id="message" name="message" rows="5" style="width: 100%;"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '<h2>Teste de E-mail do EntreLinhas</h2><p>Este é um e-mail de teste enviado pelo sistema.</p>'; ?></textarea>
            </div>
            <div style="margin-top: 10px;">
                <label for="api_key">SendGrid API Key:</label><br>
                <input type="text" id="api_key" name="api_key" required style="width: 100%;"
                       value="<?php echo isset($_POST['api_key']) ? htmlspecialchars($_POST['api_key']) : 'CHAVE_SENDGRID_REMOVIDA'; ?>">
            </div>
            <div style="margin-top: 20px;">
                <input type="submit" name="enviar" value="Enviar E-mail de Teste">
            </div>
        </form>
        
        <h2>Diagnóstico do Sistema</h2>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>cURL Version:</strong> <?php echo function_exists('curl_version') ? curl_version()['version'] : 'N/A'; ?></p>
        <p><strong>SSL Version:</strong> <?php echo function_exists('curl_version') ? curl_version()['ssl_version'] : 'N/A'; ?></p>
        <p><a href="teste_curl.php">Executar Teste de cURL</a> | <a href="teste_sendgrid.php">Voltar para Teste do SendGrid</a></p>
    </div>
</body>
</html>
