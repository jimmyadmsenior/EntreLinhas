<?php
// Incluir arquivo de configuração do banco de dados
require_once 'backend/config.php';

// Criar conexão
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Nome da tabela para verificar
$tabela = "usuarios";

// Verificar se a tabela existe
$sql_check = "SHOW TABLES LIKE '$tabela'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows == 0) {
    echo "A tabela '$tabela' não existe no banco de dados.\n";
    $conn->close();
    exit(1);
}

// Consultar a estrutura da tabela
$sql = "DESCRIBE $tabela";
$result = $conn->query($sql);

echo "=== ESTRUTURA DA TABELA '$tabela' ===\n";
echo str_repeat("-", 80) . "\n";
echo sprintf("%-20s | %-30s | %-5s | %-10s\n", "Campo", "Tipo", "Nulo", "Chave");
echo str_repeat("-", 80) . "\n";

while ($row = $result->fetch_assoc()) {
    echo sprintf("%-20s | %-30s | %-5s | %-10s\n", 
        $row['Field'], 
        $row['Type'], 
        $row['Null'], 
        $row['Key']);
}

echo str_repeat("-", 80) . "\n";

// Verificar dados na tabela
$sql_count = "SELECT COUNT(*) as total FROM $tabela";
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
echo "Total de registros: " . $row_count['total'] . "\n";

// Mostrar algumas linhas de exemplo
echo "\nExemplo de registros:\n";
$sql_sample = "SELECT * FROM $tabela LIMIT 5";
$result_sample = $conn->query($sql_sample);

if ($result_sample->num_rows > 0) {
    $fields = [];
    while ($field_info = $result_sample->fetch_field()) {
        $fields[] = $field_info->name;
    }
    
    echo implode(" | ", $fields) . "\n";
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $result_sample->fetch_assoc()) {
        $values = [];
        foreach ($fields as $field) {
            $values[] = $row[$field] ?? 'NULL';
        }
        echo implode(" | ", $values) . "\n";
    }
} else {
    echo "Nenhum registro encontrado.\n";
}

$conn->close();
?>
