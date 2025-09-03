<?php
// Este script verifica a estrutura da tabela de usuários
require_once "backend/config.php";

// Consulta para obter a estrutura da tabela
$sql = "DESCRIBE usuarios";
$result = mysqli_query($conn, $sql);

echo "<h1>Estrutura da Tabela 'usuarios'</h1>";
echo "<table border='1'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Verificar se a coluna 'status' existe
$sql = "SHOW COLUMNS FROM usuarios LIKE 'status'";
$result = mysqli_query($conn, $sql);
$status_exists = mysqli_num_rows($result) > 0;

echo "<h2>Verificação da coluna 'status'</h2>";
if ($status_exists) {
    echo "<p style='color:green;'>A coluna 'status' existe na tabela.</p>";
} else {
    echo "<p style='color:red;'>A coluna 'status' NÃO existe na tabela.</p>";
    
    // Mostrar script para adicionar a coluna
    echo "<h3>Script para adicionar a coluna 'status':</h3>";
    echo "<pre>";
    echo "ALTER TABLE usuarios ADD COLUMN status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo';";
    echo "</pre>";
}

// Buscar um usuário para exemplo
$sql = "SELECT * FROM usuarios WHERE id = 1 LIMIT 1";
$result = mysqli_query($conn, $sql);

echo "<h2>Exemplo de dados de um usuário:</h2>";
if ($row = mysqli_fetch_assoc($result)) {
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "<p>Nenhum usuário encontrado.</p>";
}

// Fechar conexão
mysqli_close($conn);
?>
