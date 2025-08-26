<?php
// teste_sendgrid_simples.php - Versão simplificada para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste Simplificado do SendGrid</h1>";
echo "<p>Esta página é uma versão simplificada para diagnóstico.</p>";

// Verificar se cURL está disponível
if (!function_exists('curl_init')) {
    echo '<p style="color:red"><strong>ERRO: A extensão cURL não está disponível no PHP!</strong></p>';
} else {
    echo '<p style="color:green"><strong>✓ cURL está disponível</strong></p>';
}

// Formulário simples
if (!isset($_POST['enviar'])) {
    echo '<form method="post">
        <p>E-mail destinatário: <input type="email" name="to_email" value="jimmycastilho555@gmail.com" required></p>
        <p>Assunto: <input type="text" name="subject" value="Teste Simples SendGrid" required></p>
        <p>Mensagem: <textarea name="message" required>Teste simples de e-mail via SendGrid</textarea></p>
        <p>API Key: <input type="text" name="api_key" value="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" required></p>
        <p><input type="submit" name="enviar" value="Enviar E-mail"></p>
    </form>';
} else {
    // Processar o envio
    $to_email = $_POST['to_email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $api_key = $_POST['api_key'];
    
    echo "<h2>Resultado do Envio</h2>";
    echo "<pre>";
    
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
        echo "ERRO JSON: " . json_last_error_msg() . "\n";
        exit;
    }
    
    echo "Dados JSON preparados\n";
    
    // Inicializar cURL
    $ch = curl_init();
    if ($ch === false) {
        echo "ERRO: Falha ao inicializar cURL\n";
        exit;
    }
    
    echo "cURL inicializado\n";
    
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    echo "Opções configuradas\n";
    
    // Executar requisição
    echo "Enviando requisição...\n";
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($curl_errno) {
        echo "ERRO cURL #{$curl_errno}: {$curl_error}\n";
    } else {
        echo "Requisição executada\n";
    }
    
    echo "Código HTTP: {$http_code}\n";
    echo "Resposta: " . ($response ? $response : "[Sem resposta]") . "\n";
    
    if ($http_code == 202) {
        echo "\nSUCESSO! E-mail enviado.\n";
    } else {
        echo "\nERRO! Falha ao enviar e-mail.\n";
    }
    
    curl_close($ch);
    echo "</pre>";
    
    echo '<p><a href="teste_sendgrid_simples.php">Voltar</a></p>';
}

echo '<p><a href="index_email_teste.php">Voltar para o índice de testes</a></p>';
?>
