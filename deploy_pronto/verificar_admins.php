<?php
// Incluir arquivo de configuração do banco de dados
require_once 'backend/config.php';

// Criar conexão
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Consultar todos os administradores
$sql = "SELECT id, nome, email, tipo FROM usuarios WHERE tipo = 'admin'";
$result = $conn->query($sql);

echo "=== ADMINISTRADORES CADASTRADOS ===\n";
echo str_repeat("-", 80) . "\n";

if ($result->num_rows > 0) {
    echo sprintf("%-5s | %-30s | %-35s\n", "ID", "Nome", "Email");
    echo str_repeat("-", 80) . "\n";
    
    // Exibir dados de cada administrador
    while($row = $result->fetch_assoc()) {
        echo sprintf("%-5s | %-30s | %-35s\n", 
            $row["id"], 
            $row["nome"], 
            $row["email"]);
    }
    echo str_repeat("-", 80) . "\n";
    echo "Total: " . $result->num_rows . " administrador(es) encontrado(s).\n";
} else {
    echo "Nenhum administrador encontrado no sistema.\n";
    echo "Para testar o sistema de notificação, você precisa criar pelo menos um administrador.\n";
}

$conn->close();
?>
