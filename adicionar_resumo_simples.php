<?php
// Arquivo para adicionar a coluna resumo à tabela artigos

// Incluir arquivo de configuração
require_once "backend/config.php";

// Função para verificar se uma coluna existe em uma tabela
function coluna_existe($conn, $tabela, $coluna) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$tabela` LIKE '$coluna'");
    return (mysqli_num_rows($result) > 0);
}

echo "<h1>Manutenção do Banco de Dados</h1>";

// Verificar se a coluna resumo existe na tabela artigos
if (!coluna_existe($conn, "artigos", "resumo")) {
    echo "<p>A coluna 'resumo' não existe na tabela 'artigos'. Adicionando...</p>";
    
    // Adicionar a coluna resumo
    $sql = "ALTER TABLE `artigos` ADD COLUMN `resumo` TEXT AFTER `conteudo`";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>Coluna 'resumo' adicionada com sucesso!</p>";
        
        // Preencher a coluna resumo com os dados da coluna conteudo
        $sql_update = "UPDATE `artigos` SET `resumo` = LEFT(`conteudo`, 200)";
        
        if (mysqli_query($conn, $sql_update)) {
            echo "<p style='color:green'>Dados da coluna 'resumo' atualizados com sucesso!</p>";
        } else {
            echo "<p style='color:red'>Erro ao atualizar dados da coluna 'resumo': " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color:red'>Erro ao adicionar coluna 'resumo': " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p>A coluna 'resumo' já existe na tabela 'artigos'.</p>";
}

// Verificar a estrutura da tabela artigos
echo "<h2>Estrutura da tabela 'artigos'</h2>";
$result = mysqli_query($conn, "DESCRIBE artigos");

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
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
} else {
    echo "<p style='color:red'>Erro ao obter estrutura da tabela 'artigos': " . mysqli_error($conn) . "</p>";
}

// Verificar se o campo usuario_id existe na tabela artigos (deve ser id_usuario)
echo "<h2>Verificação de consistência de nomes de campos</h2>";

if (coluna_existe($conn, "artigos", "usuario_id") && !coluna_existe($conn, "artigos", "id_usuario")) {
    echo "<p>O campo 'usuario_id' existe, mas deveria ser 'id_usuario'. Corrigindo...</p>";
    
    $sql = "ALTER TABLE `artigos` CHANGE `usuario_id` `id_usuario` INT NOT NULL";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>Campo renomeado de 'usuario_id' para 'id_usuario' com sucesso!</p>";
    } else {
        echo "<p style='color:red'>Erro ao renomear campo: " . mysqli_error($conn) . "</p>";
    }
} elseif (coluna_existe($conn, "artigos", "id_usuario")) {
    echo "<p>O campo 'id_usuario' existe corretamente.</p>";
} else {
    echo "<p style='color:red'>Nenhum dos campos 'id_usuario' ou 'usuario_id' existe na tabela!</p>";
}

echo "<p><a href='teste_php_simples.php'>Voltar ao teste principal</a></p>";
?>
