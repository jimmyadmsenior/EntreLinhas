<?php
// teste_sendgrid_cli.php
// Script para testar o envio de email via SendGrid diretamente da linha de comando

// Configurações
$to_email = "jimmycastilho555@gmail.com";
$from_email = "jimmycastilho555@gmail.com";
$subject = "Teste CLI SendGrid";
$message = "Este é um teste de envio via linha de comando.";
$api_key = "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";

// Ativar relatório de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Iniciando teste de envio via SendGrid...\n";

// Verificar se cURL está disponível
if (!function_exists('curl_init')) {
    die("ERRO: Extensão cURL não está disponível.\n");
}

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
        'email' => $from_email,
        'name' => 'EntreLinhas Teste CLI'
    ],
    'content' => [
        [
            'type' => 'text/plain',
            'value' => $message
        ],
        [
            'type' => 'text/html',
            'value' => "<h1>Teste CLI</h1><p>{$message}</p>"
        ]
    ]
];

// Converter para JSON
$json_data = json_encode($data);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("ERRO: Falha ao converter dados para JSON: " . json_last_error_msg() . "\n");
}

echo "Dados JSON preparados com sucesso.\n";

// Inicializar cURL
$ch = curl_init();
if ($ch === false) {
    die("ERRO: Falha ao inicializar cURL.\n");
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
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para testes

echo "Opções cURL configuradas.\n";

// Executar requisição
echo "Enviando requisição para a API do SendGrid...\n";
$response = curl_exec($ch);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Verificar erros cURL
if ($curl_errno) {
    echo "ERRO cURL #{$curl_errno}: {$curl_error}\n";
} else {
    echo "Requisição cURL executada sem erros.\n";
}

// Verificar código HTTP
echo "Código HTTP recebido: {$http_code}\n";

// Mostrar resposta
echo "Resposta recebida:\n";
if (!empty($response)) {
    echo $response . "\n";
} else {
    echo "[Resposta vazia]\n";
}

// Verificar sucesso
if ($http_code == 202) {
    echo "\n✅ E-mail enviado com SUCESSO!\n";
} else {
    echo "\n❌ FALHA ao enviar e-mail. Código: {$http_code}\n";
}

// Fechar conexão cURL
curl_close($ch);
echo "Conexão cURL fechada.\n";

echo "Teste concluído.\n";
?>
