<?php
/**
 * enviar_email_arquivo.php
 * 
 * Script para "enviar" e-mails salvando-os em arquivos locais
 * Útil para testes ou quando não há acesso a servidores de e-mail
 */

/**
 * Salva um e-mail como arquivo para simular o envio
 * 
 * @param string $to_email E-mail do destinatário
 * @param string $subject Assunto do e-mail
 * @param string $html_message Mensagem em HTML
 * @param string $from_email E-mail do remetente
 * @param string $from_name Nome do remetente
 * @return array Resultado da operação
 */
function enviar_email_arquivo($to_email, $subject, $html_message, $from_email = 'jimmycastilho555@gmail.com', $from_name = 'EntreLinhas') {
    // Criar diretório para os e-mails se não existir
    $email_dir = __DIR__ . '/emails';
    if (!file_exists($email_dir)) {
        if (!mkdir($email_dir, 0755, true)) {
            return [
                'success' => false,
                'message' => "Não foi possível criar o diretório {$email_dir}",
                'method' => 'arquivo'
            ];
        }
    }
    
    // Gerar nome de arquivo baseado no destinatário e data/hora
    $timestamp = date('Y-m-d_H-i-s');
    $to_safe = str_replace(['@', '.'], ['_at_', '_dot_'], $to_email);
    $filename = "{$email_dir}/{$timestamp}_{$to_safe}.html";
    
    // Criar versão em texto puro
    $plain_message = strip_tags($html_message);
    
    // Construir conteúdo do e-mail com metadados
    $email_content = "<!DOCTYPE html>
<html>
<head>
    <meta charset=\"UTF-8\">
    <title>E-mail Simulado: {$subject}</title>
    <style>
        .metadata { background: #f0f0f0; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; }
        .email-body { border: 1px solid #ddd; padding: 20px; }
        .text-version { background: #eee; padding: 10px; margin-top: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class=\"metadata\">
        <strong>De:</strong> {$from_name} &lt;{$from_email}&gt;<br>
        <strong>Para:</strong> &lt;{$to_email}&gt;<br>
        <strong>Assunto:</strong> {$subject}<br>
        <strong>Data:</strong> " . date('Y-m-d H:i:s') . "<br>
        <strong>Método:</strong> Simulação de Envio (Arquivo Local)<br>
    </div>
    
    <h2>Versão HTML:</h2>
    <div class=\"email-body\">
        {$html_message}
    </div>
    
    <h2>Versão Texto:</h2>
    <div class=\"text-version\">{$plain_message}</div>
</body>
</html>";
    
    // Salvar o arquivo
    if (file_put_contents($filename, $email_content)) {
        return [
            'success' => true,
            'message' => "E-mail simulado salvo em {$filename}",
            'method' => 'arquivo',
            'file_path' => $filename
        ];
    } else {
        return [
            'success' => false,
            'message' => "Falha ao salvar e-mail em {$filename}",
            'method' => 'arquivo'
        ];
    }
}

// Se o script for executado diretamente, permitir testes
if (basename(__FILE__) == basename($_SERVER['PHP_SELF']) || (isset($argc) && $argc > 0)) {
    // Verificar argumentos da linha de comando
    if (isset($argv) && $argc < 3) {
        echo "Uso: php enviar_email_arquivo.php [destinatario] [assunto] [mensagem]\n";
        echo "  destinatario: Endereço de e-mail para envio\n";
        echo "  assunto: Assunto do e-mail\n";
        echo "  mensagem: Texto da mensagem (pode ser HTML)\n";
        exit(1);
    }
    
    // Dados do e-mail via linha de comando
    $to = isset($argv[1]) ? $argv[1] : '';
    $subject = isset($argv[2]) ? $argv[2] : '';
    $message = isset($argv[3]) ? $argv[3] : '';
    
    // Enviar o e-mail
    if (!empty($to) && !empty($subject) && !empty($message)) {
        echo "Simulando envio de e-mail para {$to}...\n";
        $result = enviar_email_arquivo($to, $subject, $message);
        
        // Exibir resultado
        if ($result['success']) {
            echo "✅ " . $result['message'] . "\n";
        } else {
            echo "❌ " . $result['message'] . "\n";
        }
    }
}
?>
