<?php
/**
 * Script de teste para verificar o funcionamento do envio de e-mails
 * Acesse esse script pela URL para testar o envio
 */

// Inicializar sessão e incluir arquivos necessários
session_start();
require_once 'backend/config.php';
require_once 'backend/email_notification.php';

// Verificar se estamos em ambiente de desenvolvimento
$is_local = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1');

// Por segurança, só permitir execução em ambiente local
if (!$is_local) {
    die("Este script só pode ser executado em ambiente local.");
}

// Exibir cabeçalho HTML
echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Teste de E-mail</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .result { padding: 15px; margin: 15px 0; border-left: 4px solid #333; background: #f9f9f9; }
        .success { border-left-color: green; }
        .error { border-left-color: red; }
    </style>
</head>
<body>
<div class='container'>
    <h1>Teste de Envio de E-mail</h1>";

// Iniciar buffer de saída para capturar logs
ob_start();

// Artigo de teste
$artigo_teste = [
    'id' => 9999,
    'titulo' => 'Artigo de Teste - ' . date('d/m/Y H:i:s'),
    'conteudo' => '<p>Este é um artigo de teste gerado automaticamente para verificar o funcionamento do sistema de notificação por e-mail.</p>
                  <p>Se você recebeu este e-mail, o sistema está funcionando corretamente.</p>',
    'categoria' => 'Teste',
    'data_criacao' => date('Y-m-d H:i:s')
];

// Nome do autor de teste
$autor_teste = "Sistema de Teste";

// Testar envio para administradores
echo "<h2>Testando envio para administradores</h2>";
$resultado_admins = notificar_admins_novo_artigo($artigo_teste, $autor_teste);

if ($resultado_admins) {
    echo "<div class='result success'>";
    echo "<h3>✓ Sucesso!</h3>";
    echo "<p>E-mail de notificação para administradores enviado com sucesso.</p>";
    echo "</div>";
} else {
    echo "<div class='result error'>";
    echo "<h3>✗ Erro!</h3>";
    echo "<p>Falha ao enviar e-mail de notificação para administradores.</p>";
    echo "</div>";
}

// Testar envio para autor (aprovação)
echo "<h2>Testando envio para autor (aprovação)</h2>";
$resultado_aprovacao = notificar_autor_status_artigo('jimmycastilho555@gmail.com', 'Autor Teste', $artigo_teste, true, 'Artigo aprovado no teste automatizado.');

if ($resultado_aprovacao) {
    echo "<div class='result success'>";
    echo "<h3>✓ Sucesso!</h3>";
    echo "<p>E-mail de aprovação para autor enviado com sucesso.</p>";
    echo "</div>";
} else {
    echo "<div class='result error'>";
    echo "<h3>✗ Erro!</h3>";
    echo "<p>Falha ao enviar e-mail de aprovação para autor.</p>";
    echo "</div>";
}

// Testar envio para autor (rejeição)
echo "<h2>Testando envio para autor (rejeição)</h2>";
$resultado_rejeicao = notificar_autor_status_artigo('jimmycastilho555@gmail.com', 'Autor Teste', $artigo_teste, false, 'Artigo rejeitado no teste automatizado.');

if ($resultado_rejeicao) {
    echo "<div class='result success'>";
    echo "<h3>✓ Sucesso!</h3>";
    echo "<p>E-mail de rejeição para autor enviado com sucesso.</p>";
    echo "</div>";
} else {
    echo "<div class='result error'>";
    echo "<h3>✗ Erro!</h3>";
    echo "<p>Falha ao enviar e-mail de rejeição para autor.</p>";
    echo "</div>";
}

// Capturar logs
$log_output = ob_get_clean();

// Exibir os logs
echo "<h2>Logs do Sistema</h2>";
echo "<pre style='background: #f0f0f0; padding: 15px; overflow: auto;'>";
echo htmlspecialchars(error_get_last() ? print_r(error_get_last(), true) : "Nenhum erro PHP registrado.");
echo "</pre>";

// Exibir informações sobre a configuração do PHP Mail
echo "<h2>Configuração PHP Mail</h2>";
echo "<pre style='background: #f0f0f0; padding: 15px; overflow: auto;'>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "php.ini location: " . php_ini_loaded_file() . "\n";
echo "mail() function available: " . (function_exists('mail') ? "Yes" : "No") . "\n";

// Verificar configurações relevantes
$sendmail_path = ini_get('sendmail_path');
$SMTP = ini_get('SMTP');
$smtp_port = ini_get('smtp_port');

echo "sendmail_path: " . ($sendmail_path ? $sendmail_path : "Not set") . "\n";
echo "SMTP: " . ($SMTP ? $SMTP : "Not set") . "\n";
echo "smtp_port: " . ($smtp_port ? $smtp_port : "Not set") . "\n";
echo "</pre>";

// Exibir instruções
echo "<h2>Próximos passos</h2>";
echo "<p>Verifique seu e-mail para confirmar se recebeu as notificações de teste.</p>";
echo "<p>Se não recebeu, verifique:</p>";
echo "<ol>
    <li>As configurações de envio de e-mail no servidor PHP</li>
    <li>O arquivo de log do PHP para mensagens de erro detalhadas</li>
    <li>Se os e-mails não estão indo para a pasta de spam</li>
    <li>Se você precisa configurar um servidor SMTP real em vez de usar a função mail() nativa</li>
</ol>";

echo "</div></body></html>";
?>
