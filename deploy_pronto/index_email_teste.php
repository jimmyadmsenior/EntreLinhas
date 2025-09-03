<?php
// index_email_teste.php - Página central para os testes de e-mail do EntreLinhas
?>
<!DOCTYPE html>
<html>
<head>
    <title>Testes de E-mail - EntreLinhas</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { color: #444; margin-top: 30px; }
        .card-container { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 30px; }
        .card { flex: 1 0 300px; border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #fff; transition: all 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #333; }
        .card p { color: #666; }
        .card .button { display: inline-block; background: #4285f4; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; margin-top: 10px; }
        .card .button:hover { background: #3367d6; }
        .diagnostic-container { margin-top: 40px; }
        .diagnostic-card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .diagnostic-card h3 { margin-top: 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .status-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; }
        .status-green { background-color: #34a853; }
        .status-yellow { background-color: #fbbc05; }
        .status-red { background-color: #ea4335; }
        footer { margin-top: 40px; text-align: center; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Testes de E-mail - EntreLinhas</h1>
        
        <p>Bem-vindo à central de testes de e-mail do sistema EntreLinhas. Esta página fornece acesso a diferentes métodos de envio de e-mail e ferramentas de diagnóstico.</p>
        
        <h2>Opções de Envio de E-mail</h2>
        <div class="card-container">
            <div class="card">
                <h3>SendGrid API</h3>
                <p>Envie e-mails usando a API SendGrid. Requer uma chave de API válida e conexão com a internet.</p>
                <p><strong>Vantagens:</strong> Confiável, alta entregabilidade, rastreamento de e-mails</p>
                <a href="teste_sendgrid_direto.php" class="button">Testar SendGrid</a>
            </div>
            
            <div class="card">
                <h3>PHP mail()</h3>
                <p>Envie e-mails usando a função nativa mail() do PHP. Requer um servidor de e-mail configurado no servidor.</p>
                <p><strong>Vantagens:</strong> Simples, sem dependências externas</p>
                <a href="email_simples.php" class="button">Testar mail()</a>
            </div>
            
            <div class="card">
                <h3>PHPMailer</h3>
                <p>Envie e-mails usando a biblioteca PHPMailer com suporte a SMTP. Requer instalação via Composer.</p>
                <p><strong>Vantagens:</strong> Flexível, suporte a anexos, HTML e SMTP</p>
                <a href="phpmailer_teste.php" class="button">Testar PHPMailer</a>
            </div>
            
            <div class="card">
                <h3>Sistema Universal</h3>
                <p>Sistema inteligente que escolhe automaticamente o melhor método de envio disponível com fallback.</p>
                <p><strong>Vantagens:</strong> Altamente confiável, flexível, com estratégia de fallback</p>
                <a href="teste_email_universal_integration.php" class="button">Testar Sistema Universal</a>
            </div>
        </div>
        
        <h2>Diagnósticos</h2>
        <div class="card-container">
            <div class="card">
                <h3>Diagnóstico SendGrid</h3>
                <p>Execute testes de conectividade e configuração específicos para o SendGrid.</p>
                <a href="diagnostico_sendgrid.php" class="button">Executar Diagnóstico</a>
            </div>
            
            <div class="card">
                <h3>Verificação SendGrid</h3>
                <p>Verifique requisitos e teste a instalação do SendGrid de forma detalhada.</p>
                <a href="verificar_sendgrid.php" class="button">Verificar SendGrid</a>
            </div>
            
            <div class="card">
                <h3>Diagnóstico do Sistema</h3>
                <p>Verifique o ambiente PHP, extensões e configurações necessárias para o funcionamento do sistema de e-mail.</p>
                <a href="diagnostico_sistema.php" class="button">Verificar Sistema</a>
            </div>
        </div>
        
        <div class="diagnostic-container">
            <h2>Status Atual do Sistema</h2>
            
            <?php
            // Verificar se cURL está instalado
            $curl_ok = function_exists('curl_init');
            
            // Verificar arquivo de configuração do SendGrid
            $sendgrid_file = __DIR__ . '/backend/sendgrid_email.php';
            $sendgrid_configured = file_exists($sendgrid_file);
            
            // Verificar se a função mail() está disponível
            $mail_function_ok = function_exists('mail');
            ?>
            
            <div class="diagnostic-card">
                <h3>Funcionalidades de E-mail</h3>
                <p>
                    <span class="status-indicator <?php echo $curl_ok ? 'status-green' : 'status-red'; ?>"></span>
                    <strong>cURL:</strong> <?php echo $curl_ok ? 'Disponível' : 'Não disponível'; ?>
                </p>
                <p>
                    <span class="status-indicator <?php echo $sendgrid_configured ? 'status-green' : 'status-yellow'; ?>"></span>
                    <strong>SendGrid:</strong> <?php echo $sendgrid_configured ? 'Configurado' : 'Não configurado'; ?>
                </p>
                <p>
                    <span class="status-indicator <?php echo $mail_function_ok ? 'status-green' : 'status-yellow'; ?>"></span>
                    <strong>Função mail():</strong> <?php echo $mail_function_ok ? 'Disponível' : 'Não disponível'; ?>
                </p>
                <p>
                    <span class="status-indicator status-yellow"></span>
                    <strong>PHPMailer:</strong> Instalação necessária via Composer
                </p>
                <p>
                    <span class="status-indicator status-green"></span>
                    <strong>Sistema Universal:</strong> Disponível e configurado
                </p>
            </div>
        </div>
        
        <footer>
            <p>EntreLinhas - Sistema de Teste de E-mail © <?php echo date('Y'); ?></p>
            <p><a href="index.php">Voltar ao Site Principal</a></p>
        </footer>
    </div>
</body>
</html>
