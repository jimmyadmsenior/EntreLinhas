<?php
/**
 * SendGrid API Helper
 * 
 * Funções de apoio para envio de e-mails usando a API do SendGrid
 */

// Carregar variáveis de ambiente
require_once __DIR__ . '/env_loader.php';
carregarVariaveisAmbiente();

// Chave de API do SendGrid
define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY') ?: '');

/**
 * Envia e-mail usando a API SendGrid
 * 
 * @param string $destinatario E-mail do destinatário
 * @param string $assunto Assunto do e-mail
 * @param string $conteudo Conteúdo HTML do e-mail
 * @param string $remetente_nome Nome do remetente (opcional)
 * @param string $remetente_email E-mail do remetente (opcional)
 * @return bool Verdadeiro se o e-mail foi enviado, falso caso contrário
 */
function sendEmail($destinatario, $assunto, $conteudo, $remetente_nome = null, $remetente_email = null) {
    // Obter nome e email do remetente das variáveis de ambiente ou usar valores padrão
    $remetente_nome = $remetente_nome ?: (getenv('EMAIL_NOME') ?: 'EntreLinhas');
    $remetente_email = $remetente_email ?: (getenv('EMAIL_REMETENTE') ?: 'jimmycastilho555@gmail.com');
    $url = 'https://api.sendgrid.com/v3/mail/send';
    $headers = [
        'Authorization: Bearer ' . SENDGRID_API_KEY,
        'Content-Type: application/json'
    ];
    
    $data = [
        'personalizations' => [
            [
                'to' => [
                    ['email' => $destinatario]
                ],
                'subject' => $assunto,
            ]
        ],
        'from' => [
            'email' => $remetente_email,
            'name' => $remetente_nome
        ],
        'content' => [
            [
                'type' => 'text/html',
                'value' => $conteudo
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desativa verificação SSL (não recomendado para produção)
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    } else {
        // Registrar erro (opcional)
        error_log("Erro ao enviar e-mail via SendGrid: Código HTTP $httpCode, Resposta: $response, Erro: $error");
        return false;
    }
}
?>
