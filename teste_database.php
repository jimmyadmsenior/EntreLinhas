<?php
// Script para testar a conexão com o banco de dados e verificar tabelas

require_once __DIR__ . '/backend/config.php';

echo "Testando conexão com o banco de dados...\n";

if ($conn) {
    echo "✅ Conexão bem sucedida ao MySQL!\n";
    
    // Verificar tabelas
    echo "\nVerificando tabelas existentes:\n";
    $result = $conn->query("SHOW TABLES");
    
    if ($result) {
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        echo "Tabelas encontradas: " . implode(", ", $tables) . "\n\n";
        
        // Verificar estrutura da tabela artigos
        echo "Estrutura da tabela artigos:\n";
        $result = $conn->query("DESCRIBE artigos");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
            }
        } else {
            echo "ERRO ao obter estrutura da tabela artigos: " . $conn->error . "\n";
        }
        
        // Verificar se há artigos no banco
        echo "\nVerificando se existem artigos:\n";
        $result = $conn->query("SELECT COUNT(*) as total FROM artigos");
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo "Total de artigos: {$row['total']}\n";
            
            if ($row['total'] > 0) {
                echo "\nÚltimos 5 artigos:\n";
                $result = $conn->query("SELECT id, titulo, status, data_criacao FROM artigos ORDER BY data_criacao DESC LIMIT 5");
                
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        echo "ID: {$row['id']} | Título: {$row['titulo']} | Status: {$row['status']} | Data: {$row['data_criacao']}\n";
                    }
                }
            }
        } else {
            echo "ERRO ao contar artigos: " . $conn->error . "\n";
        }
    } else {
        echo "ERRO ao listar tabelas: " . $conn->error . "\n";
    }
} else {
    echo "❌ Falha na conexão com o MySQL\n";
}

echo "\nVerificação concluída!\n";
?>
