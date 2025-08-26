<?php
// Script para testar a funcionalidade básica do cURL

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Teste da extensão cURL</h1>';

if (!function_exists('curl_init')) {
    echo '<p style="color: red; font-weight: bold;">ERRO: A função curl_init não existe! A extensão cURL não está habilitada.</p>';
    exit;
}

echo '<p style="color: green;">✓ A extensão cURL está habilitada (curl_init existe).</p>';

// Tentar criar um handle do cURL
$ch = curl_init();
if ($ch === false) {
    echo '<p style="color: red; font-weight: bold;">ERRO: curl_init() falhou ao criar o handle.</p>';
    exit;
}

echo '<p style="color: green;">✓ curl_init() criou um handle com sucesso.</p>';

// Tentar configurar uma URL
curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');
echo '<p style="color: green;">✓ curl_setopt() funcionou.</p>';

// Configurar para retornar o resultado em vez de exibi-lo
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Tentar executar a requisição
echo '<p>Tentando acessar google.com para testar a conexão...</p>';
$response = curl_exec($ch);

if ($response === false) {
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    echo '<p style="color: red; font-weight: bold;">ERRO: curl_exec() falhou! Erro #' . $errno . ': ' . $error . '</p>';
    
    // Verificar problemas comuns
    if ($errno == 60) {
        echo '<p style="color: red;">Erro de certificado SSL. Você pode precisar configurar os certificados CA.</p>';
    } elseif ($errno == 7) {
        echo '<p style="color: red;">Não foi possível se conectar ao servidor. Verifique sua conexão com a internet.</p>';
    }
} else {
    echo '<p style="color: green;">✓ curl_exec() executou com sucesso e retornou dados.</p>';
    echo '<p>Tamanho da resposta: ' . strlen($response) . ' bytes</p>';
}

// Fechar o recurso cURL
curl_close($ch);
echo '<p style="color: green;">✓ curl_close() executado.</p>';

echo '<h2>Teste de conexão com o SendGrid</h2>';
echo '<p>Tentando se conectar ao servidor do SendGrid...</p>';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/scopes');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer SG.teste-teste'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    echo '<p style="color: red; font-weight: bold;">ERRO ao conectar com SendGrid: Erro #' . $errno . ': ' . $error . '</p>';
} else {
    echo '<p style="color: green;">✓ Conexão com o SendGrid bem-sucedida!</p>';
    echo '<p>Código HTTP retornado: ' . $http_code . ' (401 é esperado já que usamos uma chave inválida de teste)</p>';
}

curl_close($ch);

// Informações sobre a versão do cURL
$curl_version = curl_version();
echo '<h2>Informações da extensão cURL</h2>';
echo '<ul>';
echo '<li><strong>Versão do cURL:</strong> ' . $curl_version['version'] . '</li>';
echo '<li><strong>SSL Version:</strong> ' . $curl_version['ssl_version'] . '</li>';
echo '<li><strong>libz Version:</strong> ' . $curl_version['libz_version'] . '</li>';
echo '<li><strong>Protocolos:</strong> ' . implode(', ', $curl_version['protocols']) . '</li>';
echo '</ul>';

// Verificar a configuração de SSL/TLS
echo '<h2>Verificação de SSL/TLS</h2>';
$ch = curl_init('https://www.howsmyssl.com/a/check');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($data['tls_version'])) {
        echo '<p><strong>Versão do TLS usada:</strong> ' . $data['tls_version'] . '</p>';
        echo '<p><strong>Classificação da segurança:</strong> ' . $data['rating'] . '</p>';
    } else {
        echo '<p style="color: orange;">Não foi possível verificar a versão do TLS</p>';
    }
} else {
    echo '<p style="color: red;">Falha ao verificar a versão do TLS</p>';
}

echo '<p><a href="teste_sendgrid.php">Voltar para o teste do SendGrid</a></p>';
?>
