<?php
/**
 * Diagnóstico de Email para EntreLinhas
 * 
 * Esta ferramenta verifica a configuração de e-mail e testa diferentes métodos de envio.
 */

// Cabeçalhos para exibição em HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de E-mail - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        h2 {
            color: #3498db;
            margin-top: 30px;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
        }
        .success {
            color: #27ae60;
            background-color: #e8f5e9;
            padding: 10px;
            border-left: 4px solid #27ae60;
        }
        .warning {
            color: #e67e22;
            background-color: #fef9e7;
            padding: 10px;
            border-left: 4px solid #e67e22;
        }
        .error {
            color: #e74c3c;
            background-color: #fdedec;
            padding: 10px;
            border-left: 4px solid #e74c3c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        code {
            background: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        button, .button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-block;
            text-decoration: none;
        }
        button:hover, .button:hover {
            background-color: #2980b9;
        }
        .test-section {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de E-mail - EntreLinhas</h1>

<?php
// Verificar configuração do PHP
echo '<h2>1. Informações do PHP</h2>';
echo '<div class="info-box">';
echo '<p><strong>Versão do PHP:</strong> ' . phpversion() . '</p>';
echo '<p><strong>Sistema Operacional:</strong> ' . PHP_OS . '</p>';
echo '<p><strong>Servidor Web:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</p>';

// Verificar a configuração de email do PHP
echo '<h3>Configuração de E-mail do PHP</h3>';
echo '<table>';
echo '<tr><th>Diretiva</th><th>Valor</th></tr>';

$email_directives = [
    'SMTP' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'sendmail_path' => ini_get('sendmail_path'),
    'mail.add_x_header' => ini_get('mail.add_x_header'),
    'mail.force_extra_parameters' => ini_get('mail.force_extra_parameters')
];

foreach ($email_directives as $directive => $value) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($directive) . '</td>';
    echo '<td>' . (empty($value) ? '<em>não definido</em>' : htmlspecialchars($value)) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</div>';

// Verificar extensões necessárias
echo '<h2>2. Extensões do PHP</h2>';
$required_extensions = ['openssl', 'curl', 'mbstring', 'fileinfo'];
$missing_extensions = [];

echo '<table>';
echo '<tr><th>Extensão</th><th>Status</th></tr>';

foreach ($required_extensions as $extension) {
    $loaded = extension_loaded($extension);
    
    echo '<tr>';
    echo '<td>' . htmlspecialchars($extension) . '</td>';
    
    if ($loaded) {
        echo '<td class="success">Carregada</td>';
    } else {
        echo '<td class="error">Não carregada</td>';
        $missing_extensions[] = $extension;
    }
    
    echo '</tr>';
}

echo '</table>';

if (!empty($missing_extensions)) {
    echo '<div class="error">';
    echo '<p><strong>Atenção:</strong> As seguintes extensões necessárias não estão carregadas:</p>';
    echo '<ul>';
    foreach ($missing_extensions as $ext) {
        echo '<li>' . htmlspecialchars($ext) . '</li>';
    }
    echo '</ul>';
    echo '<p>Verifique seu arquivo php.ini ou entre em contato com seu provedor de hospedagem.</p>';
    echo '</div>';
}

// Verificar função mail()
echo '<h2>3. Verificação da Função mail()</h2>';

if (function_exists('mail')) {
    echo '<p class="success">A função mail() está disponível.</p>';
} else {
    echo '<p class="error">A função mail() não está disponível nesta instalação do PHP!</p>';
}

// Verificar SendGrid
echo '<h2>4. Verificação da Integração SendGrid</h2>';

$sendgrid_file = __DIR__ . '/backend/sendgrid_email.php';
if (file_exists($sendgrid_file)) {
    require_once $sendgrid_file;
    
    echo '<p class="success">O arquivo de integração com SendGrid foi encontrado.</p>';
    
    if (defined('SENDGRID_API_KEY')) {
        if (SENDGRID_API_KEY === 'SUA_API_KEY_AQUI') {
            echo '<p class="warning">A API Key do SendGrid está definida, mas parece ser o valor padrão. Substitua por sua API Key real.</p>';
        } else {
            // Ocultar a chave real por segurança
            $masked_key = substr(SENDGRID_API_KEY, 0, 4) . '...' . substr(SENDGRID_API_KEY, -4);
            echo '<p class="success">A API Key do SendGrid está configurada: ' . $masked_key . '</p>';
        }
    } else {
        echo '<p class="error">A constante SENDGRID_API_KEY não está definida no arquivo sendgrid_email.php</p>';
    }
    
    if (function_exists('sendEmail')) {
        echo '<p class="success">A função sendEmail() está disponível.</p>';
    } else {
        echo '<p class="error">A função sendEmail() não foi encontrada no arquivo sendgrid_email.php</p>';
    }
} else {
    echo '<p class="error">O arquivo de integração com SendGrid não foi encontrado em: ' . htmlspecialchars($sendgrid_file) . '</p>';
}

// Verificar logs de e-mail
echo '<h2>5. Logs de Envio de E-mail</h2>';

$log_files = [
    '../logs/email_notify.log' => 'Notificações de Novos Artigos',
    '../logs/email_status.log' => 'Notificações de Mudança de Status',
    'sendgrid_log.txt' => 'Logs do SendGrid'
];

$log_found = false;

foreach ($log_files as $log_file => $description) {
    if (file_exists($log_file)) {
        $log_found = true;
        
        echo '<div class="card">';
        echo '<h3>' . htmlspecialchars($description) . '</h3>';
        
        $log_contents = file_get_contents($log_file);
        
        if (!empty($log_contents)) {
            echo '<p><strong>Últimas 10 entradas:</strong></p>';
            
            // Obter as últimas 10 linhas do log
            $lines = explode("\n", $log_contents);
            $lines = array_filter($lines); // Remover linhas vazias
            $lines = array_slice($lines, -10);
            
            echo '<pre style="background:#f8f9fa; padding:10px; overflow:auto; max-height:200px; font-size:12px;">';
            foreach ($lines as $line) {
                echo htmlspecialchars($line) . "\n";
            }
            echo '</pre>';
        } else {
            echo '<p>O arquivo de log está vazio.</p>';
        }
        
        echo '</div>';
    }
}

if (!$log_found) {
    echo '<p class="warning">Nenhum arquivo de log de e-mail foi encontrado. Os logs podem não estar sendo gerados ou estão em um local diferente.</p>';
}

// Botões para teste e solução de problemas
?>

        <h2>6. Ferramentas de Teste</h2>
        
        <div class="test-section">
            <h3>Testes de Envio de E-mail</h3>
            <p>Use estas ferramentas para testar diferentes métodos de envio de e-mail:</p>
            
            <p>
                <a href="teste_sendgrid.php" class="button">Testar Envio via SendGrid</a>
                <a href="phpinfo.php" class="button" target="_blank">Ver phpinfo()</a>
            </p>
        </div>
        
        <h2>7. Solução de Problemas</h2>
        
        <div class="card">
            <h3>Problemas Comuns</h3>
            
            <h4>E-mails não são enviados</h4>
            <ul>
                <li>Verifique se a API Key do SendGrid está configurada corretamente</li>
                <li>Confirme que a extensão cURL está habilitada</li>
                <li>Verifique os logs para mensagens de erro específicas</li>
                <li>Teste o envio usando a página de teste do SendGrid</li>
            </ul>
            
            <h4>E-mails são enviados mas não são recebidos</h4>
            <ul>
                <li>Verifique a pasta de spam do destinatário</li>
                <li>Verifique se o domínio do e-mail do remetente é válido</li>
                <li>Configure o remetente como um domínio verificado no SendGrid</li>
            </ul>
        </div>
    </div>
</body>
</html>
