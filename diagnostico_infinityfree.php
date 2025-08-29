<?php
// Diagnóstico específico para InfinityFree

echo "<h1>Diagnóstico de Conexão InfinityFree</h1>";

// Forçar uso das configurações do InfinityFree (independente do ambiente)
require_once 'backend/config.infinityfree.php';

// Exibir configurações (SEM A SENHA por segurança)
echo "<h3>Configurações do InfinityFree:</h3>";
echo "Host: " . $config['db']['host'] . "<br>";
echo "Usuário: " . $config['db']['username'] . "<br>";
echo "Banco: " . $config['db']['dbname'] . "<br><br>";

try {
    // Conexão direta usando as configurações do InfinityFree
    $dsn = "mysql:host=" . $config['db']['host'] . 
           ";dbname=" . $config['db']['dbname'] . 
           ";charset=" . $config['db']['charset'];
           
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    echo "Tentando conectar ao servidor InfinityFree...<br>";
    $conn = new PDO($dsn, $config['db']['username'], $config['db']['password'], $options);
    
    echo "<div style='color:green; font-weight:bold;'>✅ Conexão ao servidor InfinityFree realizada com sucesso!</div>";
    
    // Exibir algumas informações do banco
    echo "<br>Versão do servidor: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "<br>Informações da conexão: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    
} catch (PDOException $e) {
    echo "<div style='color:red; font-weight:bold;'>❌ Erro na conexão com InfinityFree: " . $e->getMessage() . "</div>";
    
    echo "<h3>IMPORTANTE: VERIFIQUE ESTES PONTOS</h3>";
    echo "<ol>";
    echo "<li>O nome do host <strong>sql312.infinityfree.com</strong> está correto? Verifique no painel do InfinityFree!</li>";
    echo "<li>O servidor de banco de dados do InfinityFree bloqueia conexões externas. Este script funcionará <strong>apenas</strong> quando executado no próprio servidor do InfinityFree!</li>";
    echo "<li>Se estiver testando localmente, você verá um erro de conexão, o que é normal!</li>";
    echo "</ol>";
    
    echo "<h3>Sugestões:</h3>";
    echo "<ol>";
    echo "<li>Faça upload deste arquivo para seu site no InfinityFree</li>";
    echo "<li>Acesse-o diretamente via URL (ex: https://seu-dominio.rf.gd/diagnostico_infinityfree.php)</li>";
    echo "<li>Verifique o painel do InfinityFree para confirmar as credenciais corretas</li>";
    echo "<li>Verifique se o banco de dados foi criado no painel do InfinityFree</li>";
    echo "</ol>";
}
?>
