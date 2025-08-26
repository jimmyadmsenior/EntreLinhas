<?php
// diagnóstico_conexao.php - Verifica o estado atual da conexão e do servidor
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Conexão</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: #f5f5f5; border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        h2 { margin-top: 30px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de Conexão e Servidor</h1>
        <p>Esta página verifica o estado atual da conexão, arquivos críticos e configuração do servidor.</p>
        
        <h2>Informações do Servidor</h2>
        <div class="card">
            <?php
            echo "<p><strong>Versão do PHP:</strong> " . phpversion() . "</p>";
            echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
            echo "<p><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
            echo "<p><strong>URI solicitado:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
            echo "<p><strong>Memória utilizada:</strong> " . (memory_get_usage() / 1024 / 1024) . " MB</p>";
            echo "<p><strong>Tempo limite de execução:</strong> " . ini_get('max_execution_time') . " segundos</p>";
            echo "<p><strong>Limite de upload:</strong> " . ini_get('upload_max_filesize') . "</p>";
            echo "<p><strong>Tempo de execução atual:</strong> " . (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) . " segundos</p>";
            ?>
        </div>
        
        <h2>Verificação de Extensões</h2>
        <div class="card">
            <?php
            $extensoes_necessarias = array('curl', 'mysqli', 'json', 'openssl');
            foreach ($extensoes_necessarias as $ext) {
                if (extension_loaded($ext)) {
                    echo "<p class='success'>✓ Extensão {$ext} está carregada.</p>";
                } else {
                    echo "<p class='error'>✗ Extensão {$ext} NÃO está carregada!</p>";
                }
            }
            ?>
        </div>
        
        <h2>Verificação de Arquivos</h2>
        <div class="card">
            <?php
            $arquivos_importantes = array(
                'teste_sendgrid_direto.php' => 'Teste direto SendGrid',
                'email_universal.php' => 'Sistema universal de email',
                'backend/email_universal_integration.php' => 'Integração do sistema universal',
                'index_email_teste.php' => 'Índice de testes de email',
                'backend/config.php' => 'Configurações de banco de dados'
            );
            
            foreach ($arquivos_importantes as $arquivo => $descricao) {
                if (file_exists($arquivo)) {
                    $tamanho = filesize($arquivo);
                    $modificado = date("Y-m-d H:i:s", filemtime($arquivo));
                    echo "<p class='success'>✓ Arquivo {$arquivo} ({$descricao}) existe. Tamanho: {$tamanho} bytes, Última modificação: {$modificado}</p>";
                } else {
                    echo "<p class='error'>✗ Arquivo {$arquivo} ({$descricao}) NÃO existe!</p>";
                }
            }
            ?>
        </div>
        
        <h2>Teste de Conexão</h2>
        <div class="card">
            <?php
            // Teste de DNS
            echo "<h3>Teste de DNS</h3>";
            $host = 'api.sendgrid.com';
            $dns = @dns_get_record($host, DNS_A);
            if ($dns) {
                echo "<p class='success'>✓ Resolução de DNS para {$host} funcionando. IP: " . $dns[0]['ip'] . "</p>";
            } else {
                echo "<p class='error'>✗ Falha na resolução de DNS para {$host}!</p>";
            }
            
            // Teste de conexão
            echo "<h3>Teste de conexão TCP</h3>";
            $fp = @fsockopen($host, 443, $errno, $errstr, 5);
            if ($fp) {
                echo "<p class='success'>✓ Conexão TCP para {$host}:443 estabelecida com sucesso.</p>";
                fclose($fp);
            } else {
                echo "<p class='error'>✗ Falha na conexão TCP para {$host}:443! Erro: {$errstr} ({$errno})</p>";
            }
            
            // Teste de HTTPS
            echo "<h3>Teste de HTTPS</h3>";
            $ch = curl_init("https://{$host}/v3/");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para teste
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($response !== false) {
                echo "<p class='success'>✓ Conexão HTTPS para {$host} estabelecida. Código HTTP: {$httpCode}</p>";
            } else {
                echo "<p class='error'>✗ Falha na conexão HTTPS para {$host}!</p>";
            }
            ?>
        </div>
        
        <h2>Teste de Acesso a Arquivos de Teste</h2>
        <div class="card">
            <?php
            $test_urls = array(
                'http://localhost:8000/teste_sendgrid_direto.php',
                'http://localhost:8000/index_email_teste.php',
                'http://localhost:8000/teste_email_universal_integration.php'
            );
            
            foreach ($test_urls as $url) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($response !== false) {
                    echo "<p class='success'>✓ Acesso a {$url} está funcionando. Código HTTP: {$httpCode}</p>";
                } else {
                    echo "<p class='error'>✗ Falha no acesso a {$url}!</p>";
                }
            }
            ?>
        </div>
        
        <h2>Links para Testes</h2>
        <div class="card">
            <ul>
                <li><a href="teste_sendgrid_direto.php">Teste Direto do SendGrid</a></li>
                <li><a href="index_email_teste.php">Página Central de Testes de E-mail</a></li>
                <li><a href="teste_email_universal_integration.php">Teste do Sistema Universal de E-mail</a></li>
                <li><a href="phpinfo.php">Informações do PHP (phpinfo)</a></li>
            </ul>
        </div>
        
        <p><a href="index.php">Voltar para a página principal</a></p>
    </div>
</body>
</html>
