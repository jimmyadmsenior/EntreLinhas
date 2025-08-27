<?php
// Configurações de exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Informações de conexão
$server = "localhost";  // Use 'localhost' quando executado no servidor InfinityFree
$username = "if0_39798697";
$password = "xKIcJzBS13BB50t";
$database = "if0_39798697_entrelinhas";

// Criar conexão
$conn = new mysqli($server, $username, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error . " (Erro #: " . $conn->connect_errno . ")");
} 

echo "<h1>Teste de Conexão</h1>";
echo "<p>Conexão com o banco de dados bem-sucedida!</p>";
echo "<p>Informações do servidor: " . $conn->server_info . "</p>";
echo "<p>Informações do host: " . $conn->host_info . "</p>";

// Testar uma consulta simples
$sql = "SHOW TABLES";
$result = $conn->query($sql);

if ($result) {
    echo "<h2>Tabelas no banco de dados:</h2>";
    echo "<ul>";
    while($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Erro ao executar consulta: " . $conn->error . "</p>";
}

$conn->close();
?>
