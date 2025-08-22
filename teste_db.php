<?php
// Arquivo simples para testar a conexão com o banco de dados
echo "<h1>Teste de Conexão com o Banco de Dados</h1>";

$host = 'localhost';
$user = 'root';
$password = '';

// Tentar estabelecer conexão com o servidor MySQL
$conn = new mysqli($host, $user, $password);

// Verificar conexão
if ($conn->connect_error) {
    die("<p style='color: red;'>Erro de conexão: " . $conn->connect_error . "</p>");
}
echo "<p style='color: green;'>✅ Conexão com o servidor MySQL estabelecida com sucesso!</p>";

// Criar banco de dados se não existir
$dbname = 'entrelinhas';
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✅ Banco de dados '$dbname' criado ou já existente</p>";
} else {
    die("<p style='color: red;'>Erro ao criar o banco de dados: " . $conn->error . "</p>");
}

// Fechar conexão
$conn->close();
echo "<p>Teste concluído com sucesso!</p>";
?>
