<?php
// criar_tabela_email_log.php - Cria a tabela de log de e-mails

require_once __DIR__ . '/backend/config.php';

// Verificar conexão
if (!isset($conn) || $conn->connect_error) {
    echo "Erro de conexão: " . $conn->connect_error . "\n";
    exit(1);
}

// SQL para criar a tabela
$sql = "CREATE TABLE IF NOT EXISTS email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artigo_id INT,
    destinatario VARCHAR(255) NOT NULL,
    assunto VARCHAR(255) NOT NULL,
    status_envio ENUM('enviado', 'falha') NOT NULL,
    metodo_envio VARCHAR(50) NOT NULL,
    data_envio DATETIME NOT NULL,
    INDEX (artigo_id),
    INDEX (destinatario),
    INDEX (data_envio)
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela email_log criada com sucesso!\n";
} else {
    echo "Erro ao criar tabela: " . $conn->error . "\n";
    exit(1);
}

// Verificar se a tabela foi criada
$result = $conn->query("DESCRIBE email_log");
if ($result && $result->num_rows > 0) {
    echo "Estrutura da tabela email_log:\n";
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
    }
} else {
    echo "Não foi possível verificar a estrutura da tabela.\n";
}

$conn->close();
echo "Operação concluída.\n";
?>
