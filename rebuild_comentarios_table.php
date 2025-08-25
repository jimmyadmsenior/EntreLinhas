<?php
// Script para recriar a tabela de comentários

// Incluir arquivo de configuração
require_once "backend/config.php";

// Verificar conexão
if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}

echo "Conectado com sucesso ao banco de dados.<br>";

// Desativar verificação de chaves estrangeiras temporariamente
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

// Dropar a tabela comentarios se existir
$sql = "DROP TABLE IF EXISTS comentarios";
if (mysqli_query($conn, $sql)) {
    echo "Tabela comentarios removida com sucesso.<br>";
} else {
    echo "Erro ao remover tabela: " . mysqli_error($conn) . "<br>";
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

if (mysqli_query($conn, $sql)) {
    echo "Tabela comentarios criada com sucesso.<br>";
} else {
    echo "Erro ao criar tabela: " . mysqli_error($conn) . "<br>";
}

// Reativar verificação de chaves estrangeiras
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

// Fechar conexão
mysqli_close($conn);

echo "Processo concluído.";
?>
