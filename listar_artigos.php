<?php
// listar_artigos.php - Lista artigos do banco de dados

require_once __DIR__ . '/backend/config.php';

// Verificar conexão
if (!isset($conn) || $conn->connect_error) {
    echo "Erro de conexão: " . $conn->connect_error . "\n";
    exit(1);
}

// Consultar artigos
$query = "SELECT a.id, a.titulo, a.status, u.nome, u.email 
          FROM artigos a 
          JOIN usuarios u ON a.id_usuario = u.id 
          ORDER BY a.id DESC 
          LIMIT 10";
          
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "==== ARTIGOS ENCONTRADOS ====\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Status: {$row['status']}\n";
        echo "Título: {$row['titulo']}\n";
        echo "Autor: {$row['nome']} ({$row['email']})\n";
        echo "--------------------------------\n";
    }
    echo "{$result->num_rows} artigo(s) encontrado(s)\n";
} else {
    echo "Nenhum artigo encontrado.\n";
}

$conn->close();
?>
