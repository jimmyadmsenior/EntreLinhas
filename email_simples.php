<?php
// email_simples.php
// Sistema simplificado de envio de e-mails usando mail() do PHP
// Utilize isso como alternativa caso o SendGrid não esteja funcionando

/**
 * Função para enviar e-mail usando a função mail() nativa do PHP
 * 
 * @param string $para E-mail do destinatário
 * @param string $assunto Assunto do e-mail
 * @param string $mensagem Conteúdo do e-mail (pode ser HTML)
 * @param string $de_email E-mail do remetente
 * @param string $de_nome Nome do remetente
 * @return array Resultado da operação
 */
function enviar_email_simples($para, $assunto, $mensagem, $de_email = 'noreply@entrelinhas.com', $de_nome = 'EntreLinhas') {
    // Log da tentativa
    $log_file = __DIR__ . '/email_simples_log.txt';
    $log_message = date('Y-m-d H:i:s') . " - Tentativa de envio para {$para} - Assunto: {$assunto}\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
    
    // Cabeçalhos do e-mail
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = "From: {$de_nome} <{$de_email}>";
    $headers[] = "Reply-To: {$de_email}";
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    // Enviar e-mail
    $resultado = mail($para, $assunto, $mensagem, implode("\r\n", $headers));
    
    // Registrar resultado
    $status = $resultado ? 'Sucesso' : 'Falha';
    $log_resultado = date('Y-m-d H:i:s') . " - {$status} no envio para {$para}\n";
    file_put_contents($log_file, $log_resultado, FILE_APPEND);
    
    // Retornar resultado
    return [
        'sucesso' => $resultado,
        'para' => $para,
        'assunto' => $assunto,
        'horario' => date('Y-m-d H:i:s')
    ];
}

// Se este arquivo for chamado diretamente, exibir interface de teste
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    // Verificar se há dados do formulário
    $email_enviado = false;
    $email_resultado = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar'])) {
        $para = $_POST['para'] ?? '';
        $assunto = $_POST['assunto'] ?? 'Teste de E-mail do EntreLinhas';
        $mensagem = $_POST['mensagem'] ?? '';
        
        if (!empty($para) && !empty($mensagem)) {
            $email_resultado = enviar_email_simples($para, $assunto, $mensagem);
            $email_enviado = $email_resultado['sucesso'];
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>E-mail Simples - EntreLinhas</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 200px; }
        .buttons { margin-top: 20px; }
        button, input[type="submit"] { background: #4285f4; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover, input[type="submit"]:hover { background: #3367d6; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-success { background: #e6f4ea; border-left: 5px solid #34a853; }
        .alert-danger { background: #fce8e6; border-left: 5px solid #ea4335; }
        .info-box { background: #e8f0fe; padding: 15px; margin: 20px 0; border-left: 5px solid #4285f4; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistema de E-mail Simples</h1>
        
        <div class="info-box">
            <h3>Sobre o Sistema de E-mail Simples</h3>
            <p>Esta é uma alternativa simplificada que utiliza a função nativa <code>mail()</code> do PHP para enviar e-mails.</p>
            <p><strong>Importante:</strong> O funcionamento deste sistema depende da configuração do servidor. Ele pode não funcionar em alguns ambientes locais ou de desenvolvimento que não possuem servidores de e-mail configurados.</p>
        </div>
        
        <?php if ($email_enviado): ?>
        <div class="alert alert-success">
            <h3>✓ E-mail Enviado com Sucesso!</h3>
            <p>O e-mail foi enviado para <?php echo htmlspecialchars($para); ?>.</p>
            <p>Horário do envio: <?php echo $email_resultado['horario']; ?></p>
            <p><strong>Nota:</strong> Este é apenas um indicativo de que o PHP tentou enviar o e-mail. Para garantir o recebimento, verifique a caixa de entrada (e pasta de spam) do destinatário.</p>
        </div>
        <?php elseif (isset($_POST['enviar'])): ?>
        <div class="alert alert-danger">
            <h3>✗ Falha no Envio do E-mail</h3>
            <p>Não foi possível enviar o e-mail para <?php echo htmlspecialchars($para); ?>.</p>
            <p>Verifique se o servidor possui um agente de transferência de e-mails (MTA) configurado corretamente.</p>
        </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="para">E-mail do Destinatário:</label>
                <input type="email" id="para" name="para" required value="<?php echo isset($_POST['para']) ? htmlspecialchars($_POST['para']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="assunto">Assunto:</label>
                <input type="text" id="assunto" name="assunto" value="<?php echo isset($_POST['assunto']) ? htmlspecialchars($_POST['assunto']) : 'Teste de E-mail do EntreLinhas'; ?>">
            </div>
            
            <div class="form-group">
                <label for="mensagem">Mensagem (HTML permitido):</label>
                <textarea id="mensagem" name="mensagem" required><?php echo isset($_POST['mensagem']) ? htmlspecialchars($_POST['mensagem']) : '<h2>Teste de E-mail</h2><p>Olá,</p><p>Esta é uma mensagem de teste do sistema EntreLinhas.</p><p>Atenciosamente,<br>Equipe EntreLinhas</p>'; ?></textarea>
            </div>
            
            <div class="buttons">
                <input type="submit" name="enviar" value="Enviar E-mail">
            </div>
        </form>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        
        <h2>Instruções para Uso no Código</h2>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;">
// Importar o arquivo
require_once 'email_simples.php';

// Enviar um e-mail
$resultado = enviar_email_simples(
    'destinatario@exemplo.com',
    'Assunto do E-mail',
    'Conteúdo HTML do e-mail',
    'seu@email.com', // opcional
    'Seu Nome'       // opcional
);

// Verificar o resultado
if ($resultado['sucesso']) {
    echo "E-mail enviado com sucesso!";
} else {
    echo "Falha ao enviar e-mail.";
}
</pre>

        <div style="margin-top: 30px; text-align: center;">
            <p>
                <a href="teste_sendgrid_direto.php">Teste SendGrid</a> | 
                <a href="verificar_sendgrid.php">Verificar SendGrid</a> | 
                <a href="diagnostico_sistema.php">Diagnóstico do Sistema</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php
}
?>
