<?php
// Exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Verificação de Conexão com Banco de Dados Universal</h1>";

// Carregar a configuração universal
require_once 'backend/universal_db_config.php';

// Mostrar informações sobre o ambiente
echo "<h2>Ambiente detectado</h2>";
echo "<ul>";
echo "<li>Ambiente: " . (IS_PRODUCTION ? "Produção (InfinityFree)" : "Desenvolvimento (Local)") . "</li>";
echo "<li>Host: " . DB_SERVER . "</li>";
echo "<li>Usuário: " . DB_USERNAME . "</li>";
echo "<li>Banco: " . DB_NAME . "</li>";
echo "</ul>";

// Testar a conexão
echo "<h2>Teste de Conexão</h2>";

try {
    // A conexão já foi estabelecida em $conn
    echo "<p style='color:green'>✅ Conexão estabelecida com sucesso!</p>";
    
    // Testar uma consulta simples
    $result = $conn->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tabelas encontradas:</h3>";
    echo "<ul>";
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            echo "<li>{$table}</li>";
        }
    } else {
        echo "<li>Nenhuma tabela encontrada</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

echo "<h2>Recomendações</h2>";
echo "<p>Para usar esta configuração universal em todo o projeto:</p>";
echo "<ol>";
echo "<li>Inclua <code>require_once 'caminho/para/universal_db_config.php'</code> no início de cada script</li>";
echo "<li>Use a variável <code>\$conn</code> para operações de banco de dados</li>";
echo "<li>Use <code>IS_PRODUCTION</code> para comportamentos específicos de ambiente</li>";
echo "</ol>";
?>
