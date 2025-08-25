<?php
// Script de teste para verificar o envio de e-mail para administradores
require_once 'backend/config.php';
require_once 'backend/email_notification.php';

// Criar um artigo de teste
$artigo_teste = [
    'id' => 999,
    'titulo' => 'Artigo de Teste para Notificação',
    'conteudo' => 'Este é um conteúdo de teste para verificar se as notificações por e-mail estão funcionando corretamente. O sistema deve enviar este e-mail para todos os administradores cadastrados no sistema.',
    'categoria' => 'Teste'
];

$autor = 'Usuário Teste';

// Tentar enviar a notificação
$resultado = notificar_admins_novo_artigo($artigo_teste, $autor);

if ($resultado) {
    echo "<h2 style='color: green;'>E-mail enviado com sucesso!</h2>";
    echo "<p>O sistema tentou enviar e-mails para os seguintes administradores:</p>";
    echo "<ul>";
    echo "<li>Jimmy Castilho (jimmycastilho555@gmail.com)</li>";
    echo "<li>Bianca Blanco (bianca.blanco@aluno.senai.br)</li>";
    echo "<li>Miguel Zacharias (miguel.zacharias@aluno.senai.br)</li>";
    echo "</ul>";
    echo "<p>Verifique a caixa de entrada (e possivelmente a pasta de spam) desses e-mails para confirmar o recebimento.</p>";
} else {
    echo "<h2 style='color: red;'>Erro ao enviar e-mail!</h2>";
    echo "<p>Ocorreu um problema ao tentar enviar as notificações por e-mail. Verifique as configurações de e-mail do servidor.</p>";
    
    // Verificar se o PHP está configurado para enviar e-mails
    echo "<h3>Informações do servidor de e-mail:</h3>";
    echo "<pre>";
    print_r(ini_get('sendmail_path') ? "sendmail_path: " . ini_get('sendmail_path') : "sendmail_path não configurado");
    echo "</pre>";
}
?>
