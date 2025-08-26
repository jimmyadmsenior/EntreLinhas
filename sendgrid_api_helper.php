<?php
/**
 * sendgrid_api_helper.php
 * 
 * Arquivo de ajuda para envio de e-mails via SendGrid API
 * Versão simplificada para uso na linha de comando ou inclusão em outros arquivos
 */

/**
 * Envia um e-mail usando a API do SendGrid
 * 
 * @param string $to_email E-mail do destinatário
 * @param string $subject Assunto do e-mail
 * @param string $html_message Mensagem em HTML
 * @param string $plain_message Mensagem em texto plano (opcional)
 * @param string $from_email E-mail do remetente (opcional)
 * @param string $from_name Nome do remetente (opcional)
 * @param string $api_key Chave API do SendGrid (opcional)
 * @return array Resultado da operação
 */
function sendgrid_send_email($to_email, $subject, $html_message, $plain_message = '', $from_email = 'jimmycastilho555@gmail.com', $from_name = 'EntreLinhas', $api_key = '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') {
    // Verificar parâmetros
    if (empty($to_email) || empty($subject) || empty($html_message)) {
        return [
            'success' => false,
            'message' => 'Parâmetros inválidos. Destinatário, assunto e mensagem são obrigatórios.',
            'http_code' => 0
        ];
    }
    
    // Verificar cURL
    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'message' => 'Extensão cURL não está disponível.',
            'http_code' => 0
        ];
    }
    
    // Se não foi fornecida a mensagem em texto plano, strip tags do HTML
    if (empty($plain_message)) {
        $plain_message = strip_tags($html_message);
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
            'name' => $from_name
        ],
        'content' => [
            [
                'type' => 'text/plain',
                'value' => $plain_message
            ],
            [
                'type' => 'text/html',
                'value' => $html_message
            ]
        ]
    ];
    
    // Converter para JSON
    $json_data = json_encode($data);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Falha ao converter dados para JSON: ' . json_last_error_msg(),
            'http_code' => 0
        ];
    }
    
    // Inicializar cURL
    $ch = curl_init();
    if ($ch === false) {
        return [
            'success' => false,
            'message' => 'Falha ao inicializar cURL.',
            'http_code' => 0
        ];
    }
    
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
    
    // Executar requisição
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Fechar conexão
    curl_close($ch);
    
    // Verificar resultado
    if ($curl_errno) {
        return [
            'success' => false,
            'message' => "Erro cURL #{$curl_errno}: {$curl_error}",
            'http_code' => $http_code
        ];
    }
    
    if ($http_code == 202) {
        return [
            'success' => true,
            'message' => 'E-mail enviado com sucesso.',
            'http_code' => $http_code
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Falha ao enviar e-mail. Código: ' . $http_code . '. Resposta: ' . $response,
            'http_code' => $http_code,
            'response' => $response
        ];
    }
}

// Exemplo de uso (executado apenas quando este arquivo é chamado diretamente)
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $result = sendgrid_send_email(
        'jimmycastilho555@gmail.com',
        'Teste do Helper SendGrid',
        '<h1>Teste de Helper</h1><p>Este é um teste do helper de envio de e-mail.</p>'
    );
    
    echo "Resultado do envio:\n";
    echo "Sucesso: " . ($result['success'] ? 'Sim' : 'Não') . "\n";
    echo "Mensagem: " . $result['message'] . "\n";
    echo "Código HTTP: " . $result['http_code'] . "\n";
}
?>
