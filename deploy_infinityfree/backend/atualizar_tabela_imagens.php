<?php
// Incluir configuração do banco de dados
require_once "config.php";

// Definir script SQL para criar/atualizar a tabela de imagens
$sql = "
-- Verificar se a tabela de imagens existe e criá-la se não existir
CREATE TABLE IF NOT EXISTS imagens_artigos (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    imagem_base64 MEDIUMTEXT NOT NULL, -- Pode armazenar até 16MB de texto (adequado para Base64)
    tipo_mime VARCHAR(50) NOT NULL,
    data_upload DATETIME NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índices para melhorar a performance
CREATE INDEX IF NOT EXISTS idx_imagens_usuario ON imagens_artigos(usuario_id);
";

// Executar o script
if (mysqli_multi_query($conn, $sql)) {
    echo "<h2>Tabela de imagens atualizada com sucesso!</h2>";
    
    // Processar resultados múltiplos
    do {
        // Descartar resultado
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    
    if (mysqli_error($conn)) {
        echo "<p style='color:red'>Erro após execução: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<h2>Erro ao atualizar tabela de imagens:</h2>";
    echo "<p style='color:red'>" . mysqli_error($conn) . "</p>";
}

// Verificar se a tabela foi criada corretamente
$sql_check = "SHOW TABLES LIKE 'imagens_artigos'";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    echo "<h3>A tabela 'imagens_artigos' existe!</h3>";
    
    // Mostrar estrutura da tabela
    $sql_desc = "DESCRIBE imagens_artigos";
    $result_desc = mysqli_query($conn, $sql_desc);
    
    if ($result_desc) {
        echo "<h4>Estrutura da tabela:</h4>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result_desc)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
} else {
    echo "<h3 style='color:red'>A tabela 'imagens_artigos' NÃO foi criada corretamente!</h3>";
}

// Fechar conexão
mysqli_close($conn);
?>
