<?php
// Configuração para registrar logs de e-mail
ini_set('log_errors', 1);
ini_set('error_log', '../logs/email_errors.log');
error_log("Testando o registro de erros de e-mail");

// Incluir arquivos necessários
require_once '../backend/config.php';
require_once '../backend/email_notification.php';

// Testar o envio de e-mail para administrador
$artigo_teste = [
    'id' => 99999,
    'titulo' => 'Artigo de Teste para E-mail',
    'conteudo' => 'Este é um conteúdo de teste para verificar se o e-mail está sendo enviado corretamente.',
    'categoria' => 'Teste'
];

$autor_nome = 'Usuário de Teste';

// Tentar enviar e-mail
$resultado = notificar_admins_novo_artigo($artigo_teste, $autor_nome);

// Exibir resultado
echo "<h1>Teste de Envio de E-mail</h1>";

if ($resultado) {
    echo "<p style='color: green;'>E-mail enviado com sucesso!</p>";
} else {
    echo "<p style='color: red;'>Falha ao enviar e-mail!</p>";
}

// Exibir mais detalhes
echo "<h2>Detalhes do Teste</h2>";
echo "<pre>";
echo "Hora do teste: " . date("Y-m-d H:i:s") . "\n";
echo "Função mail() existe: " . (function_exists('mail') ? 'Sim' : 'Não') . "\n";
echo "Configurações do PHP para e-mail:\n";
echo "  sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "  SMTP: " . ini_get('SMTP') . "\n";
echo "  smtp_port: " . ini_get('smtp_port') . "\n";
echo "</pre>";

echo "<p>Verifique o arquivo de log em logs/email_errors.log para mais detalhes.</p>";
?>
