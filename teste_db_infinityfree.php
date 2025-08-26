<?php
// Informações de conexão
$server = "sql302.infinityfree.com";
$username = "if0_39798697";
$password = "xKIcJzBS13BB50t";
$database = "if0_39798697_entrelinhas";

// Criar conexão
$conn = new mysqli($server, $username, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
} 
echo "Conexão com o banco de dados bem-sucedida!";
$conn->close();
?>
