<?php
// diagnóstico_sendgrid.php
// Arquivo para diagnóstico de conectividade com o SendGrid API
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// Função para executar um teste específico e retornar resultado
function executarTeste($nome, $funcao) {
    echo "<div class='teste'><h3>$nome</h3>";
    
    $inicio = microtime(true);
    try {
        $resultado = $funcao();
        $duracao = round((microtime(true) - $inicio) * 1000, 2);
        
        if (isset($resultado['sucesso']) && $resultado['sucesso']) {
            echo "<div class='resultado sucesso'>";
            echo "<span class='badge'>✓ SUCESSO</span>";
            echo " ({$duracao}ms)";
            if (!empty($resultado['mensagem'])) {
                echo "<div class='detalhes'>{$resultado['mensagem']}</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='resultado falha'>";
            echo "<span class='badge'>✗ FALHA</span>";
            echo " ({$duracao}ms)";
            if (!empty($resultado['mensagem'])) {
                echo "<div class='detalhes'>{$resultado['mensagem']}</div>";
            }
            echo "</div>";
        }
    } catch (Exception $e) {
        $duracao = round((microtime(true) - $inicio) * 1000, 2);
        echo "<div class='resultado erro'>";
        echo "<span class='badge'>! ERRO</span>";
        echo " ({$duracao}ms)";
        echo "<div class='detalhes'>Exceção: {$e->getMessage()}</div>";
        echo "</div>";
    }
    
    echo "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico SendGrid - EntreLinhas</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { margin-top: 30px; color: #444; }
        .teste { margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .resultado { margin-top: 10px; padding: 10px; border-radius: 4px; }
        .sucesso { background-color: #e6f4ea; }
        .falha { background-color: #fce8e6; }
        .erro { background-color: #fce8e6; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-weight: bold; }
        .sucesso .badge { background-color: #34a853; color: white; }
        .falha .badge { background-color: #ea4335; color: white; }
        .erro .badge { background-color: #ea4335; color: white; }
        .detalhes { margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #ddd; font-family: monospace; white-space: pre-wrap; overflow-x: auto; }
        button { background: #4285f4; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #3367d6; }
        input[type="text"] { width: 100%; padding: 8px; margin: 5px 0 15px; box-sizing: border-box; }
        .sistema { background: #eee; padding: 15px; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de Conexão com SendGrid</h1>
        
        <?php
        // API Key do SendGrid para teste
        $api_key = "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";
        
        // 1. Teste de disponibilidade de cURL
        executarTeste("Verificação de cURL", function() {
            if (!function_exists('curl_init')) {
                return [
                    'sucesso' => false,
                    'mensagem' => "A extensão cURL não está disponível no PHP. Ela é necessária para comunicação com a API do SendGrid."
                ];
            }
            
            $curl_info = curl_version();
            return [
                'sucesso' => true,
                'mensagem' => "cURL disponível.\nVersão: {$curl_info['version']}\nSSL Version: {$curl_info['ssl_version']}"
            ];
        });
        
        // 2. Teste de DNS para api.sendgrid.com
        executarTeste("Resolução de DNS para api.sendgrid.com", function() {
            $host = 'api.sendgrid.com';
            $ip = gethostbyname($host);
            
            if ($ip === $host) {
                return [
                    'sucesso' => false,
                    'mensagem' => "Não foi possível resolver o nome de domínio api.sendgrid.com. Verifique a configuração de DNS."
                ];
            }
            
            return [
                'sucesso' => true,
                'mensagem' => "Resolução de DNS para api.sendgrid.com bem-sucedida.\nIP: {$ip}"
            ];
        });
        
        // 3. Teste de conectividade com api.sendgrid.com (porta 443)
        executarTeste("Conectividade com api.sendgrid.com (HTTPS, porta 443)", function() {
            $host = 'api.sendgrid.com';
            $port = 443;
            $timeout = 5;
            
            $connection = @fsockopen('ssl://' . $host, $port, $errno, $errstr, $timeout);
            
            if (!$connection) {
                return [
                    'sucesso' => false,
                    'mensagem' => "Não foi possível estabelecer conexão com {$host} na porta {$port}.\nErro #{$errno}: {$errstr}"
                ];
            }
            
            fclose($connection);
            return [
                'sucesso' => true,
                'mensagem' => "Conexão com {$host} na porta {$port} estabelecida com sucesso."
            ];
        });
        
        // 4. Teste de Requisição GET para API do SendGrid
        executarTeste("Requisição GET para API do SendGrid", function() use ($api_key) {
            $ch = curl_init('https://api.sendgrid.com/v3/scopes');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para diagnóstico
            
            $response = curl_exec($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($curl_error) {
                return [
                    'sucesso' => false,
                    'mensagem' => "Erro cURL: {$curl_error}"
                ];
            }
            
            if ($http_code >= 200 && $http_code < 300) {
                return [
                    'sucesso' => true,
                    'mensagem' => "Requisição GET bem-sucedida.\nCódigo HTTP: {$http_code}\nResposta: {$response}"
                ];
            } else {
                return [
                    'sucesso' => false,
                    'mensagem' => "Requisição GET falhou.\nCódigo HTTP: {$http_code}\nResposta: {$response}"
                ];
            }
        });
        
        // 5. Teste de Ping para o servidor do SendGrid
        executarTeste("Ping para api.sendgrid.com", function() {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                $command = 'ping -n 3 api.sendgrid.com';
            } else {
                // Linux/Unix
                $command = 'ping -c 3 api.sendgrid.com';
            }
            
            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);
            
            $output_str = implode("\n", $output);
            
            if ($return_var === 0) {
                return [
                    'sucesso' => true,
                    'mensagem' => "Ping bem-sucedido:\n{$output_str}"
                ];
            } else {
                return [
                    'sucesso' => false,
                    'mensagem' => "Ping falhou:\n{$output_str}"
                ];
            }
        });
        
        // 6. Teste básico de envio de email (somente simulação)
        executarTeste("Simulação de Envio de E-mail via SendGrid API", function() use ($api_key) {
            $url = 'https://api.sendgrid.com/v3/mail/send';
            
            $data = [
                'personalizations' => [
                    [
                        'to' => [['email' => 'teste@example.com']],
                        'subject' => 'Teste de Diagnóstico'
                    ]
                ],
                'from' => [
                    'email' => 'noreply@entrelinhas.com',
                    'name' => 'EntreLinhas Teste'
                ],
                'content' => [
                    [
                        'type' => 'text/plain',
                        'value' => 'Isto é apenas um teste de diagnóstico.'
                    ]
                ]
            ];
            
            $json_data = json_encode($data);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_data)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para diagnóstico
            
            $response = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $request_header = curl_getinfo($ch, CURLINFO_HEADER_OUT);
            curl_close($ch);
            
            $output = "URL: {$url}\n";
            $output .= "Dados enviados: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            $output .= "Headers enviados:\n{$request_header}\n";
            
            if ($curl_errno) {
                $output .= "Erro cURL #{$curl_errno}: {$curl_error}\n";
                return [
                    'sucesso' => false,
                    'mensagem' => $output
                ];
            }
            
            $output .= "Código HTTP: {$http_code}\n";
            $output .= "Resposta: {$response}\n";
            
            if ($http_code == 202) {
                return [
                    'sucesso' => true,
                    'mensagem' => $output
                ];
            } else {
                return [
                    'sucesso' => false,
                    'mensagem' => $output
                ];
            }
        });
        ?>
        
        <h2>Informações do Sistema</h2>
        <div class="sistema">
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>cURL Version:</strong> <?php echo function_exists('curl_version') ? curl_version()['version'] : 'N/A'; ?></p>
            <p><strong>SSL Version:</strong> <?php echo function_exists('curl_version') ? curl_version()['ssl_version'] : 'N/A'; ?></p>
            <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></p>
            <p><strong>User Agent:</strong> <?php echo $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'; ?></p>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <button onclick="location.reload();">Executar Testes Novamente</button>
            <p><a href="teste_sendgrid_direto.php">Ir para Teste de Envio</a> | <a href="teste_curl.php">Testar cURL</a></p>
        </div>
    </div>
</body>
</html>
