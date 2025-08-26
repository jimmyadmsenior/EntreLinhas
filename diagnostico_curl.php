<?php
// Diagnóstico da extensão cURL para envio de emails via SendGrid
// Este arquivo verifica se o cURL está disponível e funcionando corretamente

// Ativar exibição de todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Diagnóstico de cURL - EntreLinhas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
        h1 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        h2 { margin-top: 20px; color: #444; }
        .success { color: green; background-color: #d4edda; padding: 10px; border-radius: 4px; }
        .error { color: red; background-color: #f8d7da; padding: 10px; border-radius: 4px; }
        .warning { color: #856404; background-color: #fff3cd; padding: 10px; border-radius: 4px; }
        .info { color: #0c5460; background-color: #d1ecf1; padding: 10px; border-radius: 4px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Diagnóstico de cURL para SendGrid</h1>";

// 1. Verificar se a extensão cURL está habilitada
echo "<h2>1. Verificação da extensão cURL</h2>";
if (function_exists('curl_version')) {
    $curl_version = curl_version();
    echo "<div class='success'>
        <p>✅ A extensão cURL está habilitada.</p>
        <p>Versão: " . $curl_version['version'] . "</p>
        <p>SSL Version: " . $curl_version['ssl_version'] . "</p>
    </div>";
} else {
    echo "<div class='error'>
        <p>❌ A extensão cURL NÃO está habilitada!</p>
        <p>Você precisa habilitar a extensão cURL no seu php.ini.</p>
        <p>Adicione ou descomente a linha: <code>extension=curl</code></p>
    </div>";
    
    echo "<div class='info'>
        <p><strong>Como habilitar o cURL:</strong></p>
        <ol>
            <li>Localize o seu arquivo php.ini</li>
            <li>Encontre a linha <code>;extension=curl</code> e remova o ponto e vírgula do início</li>
            <li>Se a linha não existir, adicione <code>extension=curl</code></li>
            <li>Reinicie o servidor web</li>
        </ol>
    </div>";
    exit;
}

// 2. Verificar se a função file_get_contents pode fazer requisições URL
echo "<h2>2. Verificação de allow_url_fopen</h2>";
if (ini_get('allow_url_fopen')) {
    echo "<div class='success'>
        <p>✅ allow_url_fopen está habilitado.</p>
    </div>";
} else {
    echo "<div class='warning'>
        <p>⚠️ allow_url_fopen está desabilitado.</p>
        <p>Isso não afeta o cURL diretamente, mas pode impactar outras funcionalidades.</p>
    </div>";
}

// 3. Testar uma requisição cURL simples
echo "<h2>3. Teste de requisição cURL</h2>";

function test_curl_request($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    echo "<p>Testando requisição para: <code>" . htmlspecialchars($url) . "</code></p>";
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    if ($result === false) {
        echo "<div class='error'>
            <p>❌ A requisição cURL falhou!</p>
            <p>Erro: " . htmlspecialchars($error) . "</p>
        </div>";
        
        echo "<div class='info'>
            <p>Informações adicionais:</p>
            <pre>" . print_r($info, true) . "</pre>
        </div>";
    } else {
        echo "<div class='success'>
            <p>✅ A requisição cURL foi bem-sucedida!</p>
            <p>Código de resposta HTTP: " . $info['http_code'] . "</p>
            <p>Tamanho da resposta: " . strlen($result) . " bytes</p>
            <p>Tempo de conexão: " . $info['connect_time'] . " segundos</p>
            <p>Tempo total: " . $info['total_time'] . " segundos</p>
        </div>";
    }
    
    curl_close($ch);
}

// Testar conexões com diferentes sites para garantir que o cURL funcione
test_curl_request('https://www.google.com');
test_curl_request('https://api.sendgrid.com');

// 4. Verificar as configurações de proxy
echo "<h2>4. Verificação de configurações de Proxy</h2>";
$proxy_env = [
    'http_proxy' => getenv('http_proxy') ?: 'não definido',
    'https_proxy' => getenv('https_proxy') ?: 'não definido',
    'HTTP_PROXY' => getenv('HTTP_PROXY') ?: 'não definido',
    'HTTPS_PROXY' => getenv('HTTPS_PROXY') ?: 'não definido',
    'no_proxy' => getenv('no_proxy') ?: 'não definido',
    'NO_PROXY' => getenv('NO_PROXY') ?: 'não definido'
];

echo "<div class='info'>
    <p>Variáveis de ambiente de proxy:</p>
    <pre>" . print_r($proxy_env, true) . "</pre>
</div>";

// 5. Verificar se o SendGrid está configurado corretamente
echo "<h2>5. Verificação da configuração do SendGrid</h2>";

// Verificar se o arquivo sendgrid_email.php existe
if (file_exists('../backend/sendgrid_email.php')) {
    echo "<div class='success'>✅ O arquivo sendgrid_email.php existe.</div>";
    
    // Verificar se a API key está definida
    $file_content = file_get_contents('../backend/sendgrid_email.php');
    if (preg_match('/define\(\s*\'SENDGRID_API_KEY\'\s*,\s*\'([^\']+)\'/i', $file_content, $matches)) {
        $api_key = $matches[1];
        if ($api_key == 'SUA_API_KEY_AQUI' || $api_key == 'CHAVE_SENDGRID_REMOVIDA') {
            echo "<div class='error'>
                <p>❌ A API Key do SendGrid não está configurada!</p>
                <p>Defina uma chave de API válida no arquivo sendgrid_email.php.</p>
            </div>";
        } else {
            $masked_key = substr($api_key, 0, 4) . str_repeat('*', strlen($api_key) - 8) . substr($api_key, -4);
            echo "<div class='success'>
                <p>✅ API Key do SendGrid encontrada: " . $masked_key . "</p>
            </div>";
        }
    } else {
        echo "<div class='error'>
            <p>❌ Não foi possível encontrar a definição da API Key do SendGrid!</p>
            <p>Verifique se o arquivo sendgrid_email.php está configurado corretamente.</p>
        </div>";
    }
} else {
    echo "<div class='error'>
        <p>❌ O arquivo sendgrid_email.php não foi encontrado!</p>
        <p>Verifique se o arquivo está presente na pasta backend.</p>
    </div>";
}

// 6. Exibir a solução para os problemas encontrados
echo "<h2>6. Solução para problemas comuns</h2>";

echo "<div class='info'>
    <p><strong>Problemas com o cURL em ambientes locais:</strong></p>
    <ul>
        <li>Instale ou habilite a extensão cURL no PHP</li>
        <li>Se estiver em uma rede com proxy, defina as variáveis de ambiente http_proxy e https_proxy</li>
        <li>Configure uma API key válida do SendGrid</li>
        <li>Para fins de desenvolvimento, você pode usar o arquivo email_fix.php para simular o envio de emails</li>
    </ul>
    
    <p><strong>Como usar a solução alternativa:</strong></p>
    <ol>
        <li>Certifique-se de que o arquivo email_fix.php está no diretório backend</li>
        <li>Verifique se o arquivo email_notification.php inclui o email_fix.php</li>
        <li>A função notificar_admins_novo_artigo deve ter sido renomeada para notificar_admins_artigo_original</li>
        <li>O email_fix.php fornece uma implementação simulada da função notificar_admins_novo_artigo para ambiente de desenvolvimento</li>
    </ol>
</div>";

echo "<div class='info'>
    <p><a href='../solucao_artigos.php'>Voltar para a página de diagnóstico principal</a></p>
</div>";

echo "</body></html>";
?>
