<?php
// verificar_tabela_artigos.php - Verifica a estrutura da tabela de artigos

require_once __DIR__ . '/backend/config.php';

// Verificar conexão
if (!isset($conn) || $conn->connect_error) {
    echo "Erro de conexão: " . $conn->connect_error . "\n";
    exit(1);
}

// Verificar estrutura da tabela
$query = "DESCRIBE artigos";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "==== ESTRUTURA DA TABELA ARTIGOS ====\n";
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
    }
    echo "--------------------------------\n";
} else {
    echo "Não foi possível obter a estrutura da tabela artigos.\n";
}

// Verificar primeiro registro
$query = "SELECT * FROM artigos LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "\n==== EXEMPLO DE REGISTRO ====\n";
    $row = $result->fetch_assoc();
    foreach ($row as $field => $value) {
        echo "{$field}: {$value}\n";
    }
    echo "--------------------------------\n";
} else {
    echo "Nenhum registro encontrado na tabela artigos.\n";
}

$conn->close();
?>
