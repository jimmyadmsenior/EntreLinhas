<?php
require_once 'backend/config.php';

try {
    // Teste simples para verificar a conexão
    $stmt = $conn->query("SELECT 1");
    echo "Conexão PDO realizada com sucesso!";
    
    // Exibir algumas informações do banco
    echo "<br>Versão do servidor: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "<br>Informações da conexão: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
