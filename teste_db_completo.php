<?php
// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão com o Banco de Dados</h1>";

// Informações de conexão
$server = "sql302.infinityfree.com";
$username = "if0_39798697";
$password = "xKIcJzBS13BB50t";
$database = "if0_39798697_entrelinhas";

try {
    // Criar conexão com PDO (mais seguro e com melhor tratamento de erros)
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
    
    // Configurar o PDO para lançar exceções em caso de erro
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>Conexão com o banco de dados estabelecida com sucesso!</p>";
    
    // Testar consulta simples
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tabelas encontradas no banco de dados:</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>Erro de conexão: " . $e->getMessage() . "</p>";
}

// Fechar conexão
$conn = null;
?>
