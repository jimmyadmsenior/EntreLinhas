<?php
// Incluir arquivo de configuração do banco de dados
require_once 'backend/config.php';

// Criar conexão
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Consultar os últimos registros da tabela email_log
$sql = "SELECT * FROM email_log ORDER BY data_envio DESC LIMIT 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "=== REGISTROS DE LOG DE E-MAIL ===\n";
    echo str_repeat("-", 80) . "\n";
    echo sprintf("%-5s | %-8s | %-35s | %-10s | %-15s | %-20s\n", 
        "ID", "Artigo", "Destinatário", "Status", "Método", "Data Envio");
    echo str_repeat("-", 80) . "\n";
    
    // Exibir dados de cada linha
    while($row = $result->fetch_assoc()) {
        echo sprintf("%-5s | %-8s | %-35s | %-10s | %-15s | %-20s\n", 
            $row["id"], 
            $row["artigo_id"], 
            $row["destinatario"], 
            $row["status_envio"], 
            $row["metodo_envio"], 
            $row["data_envio"]);
    }
    echo str_repeat("-", 80) . "\n";
} else {
    echo "Nenhum registro de log encontrado na tabela.\n";
}

$conn->close();
echo "Consulta concluída.\n";
?>
