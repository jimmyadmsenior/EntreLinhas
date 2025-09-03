<?php
// Script para recriar a tabela de comentários

// Incluir arquivo de configuração - usar o arquivo de configuração PDO
require_once "config_pdo.php";

try {
    echo "Conectado com sucesso ao banco de dados.<br>";

    // Desativar verificação de chaves estrangeiras temporariamente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Dropar a tabela comentarios se existir
    $sql = "DROP TABLE IF EXISTS comentarios";
    $pdo->exec($sql);
    echo "Tabela comentarios removida com sucesso.<br>";
} catch (PDOException $e) {
    die("Erro ao remover tabela: " . $e->getMessage() . "<br>");
}

// Recriar a tabela comentarios
$sql = "CREATE TABLE comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    artigo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    conteudo TEXT NOT NULL,
    status ENUM('pendente', 'aprovado', 'rejeitado') NOT NULL DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artigo_id) REFERENCES artigos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)";

try {
    $pdo->exec($sql);
    echo "Tabela comentarios criada com sucesso.<br>";
    
    // Reativar verificação de chaves estrangeiras
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Conexão PDO é fechada automaticamente ao final do script
    // ou pode ser explicitamente fechada:
    $pdo = null;
    
    echo "Processo concluído.";
} catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage() . "<br>";
}
?>
