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

// Criar tabela de teste
$sql = "CREATE TABLE IF NOT EXISTS teste (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela 'teste' criada com sucesso!";
} else {
    echo "Erro ao criar tabela: " . $conn->error;
}

$conn->close();
?>
