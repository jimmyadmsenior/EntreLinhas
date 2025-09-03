<?php
// Incluir arquivo de configuração
require_once "backend/config.php";

// Adicionar a coluna resumo se ela não existir
$resultado = mysqli_query($conn, "SHOW COLUMNS FROM `artigos` LIKE 'resumo'");

if (mysqli_num_rows($resultado) == 0) {
    // A coluna não existe, vamos adicioná-la
    $sql = "ALTER TABLE `artigos` ADD COLUMN `resumo` TEXT AFTER `conteudo`";
    
    if (mysqli_query($conn, $sql)) {
        echo "Coluna 'resumo' adicionada à tabela 'artigos' com sucesso!<br>";
        
        // Agora vamos preencher o resumo para os artigos existentes
        $sql_update = "UPDATE artigos SET resumo = LEFT(conteudo, 200)";
        if (mysqli_query($conn, $sql_update)) {
            echo "Resumos atualizados com sucesso para os artigos existentes!";
        } else {
            echo "Erro ao atualizar resumos: " . mysqli_error($conn);
        }
    } else {
        echo "Erro ao adicionar coluna 'resumo': " . mysqli_error($conn);
    }
} else {
    echo "A coluna 'resumo' já existe na tabela 'artigos'.";
}

echo "<br><br><a href='verificar_tabelas.php'>Verificar estrutura do banco de dados</a>";
?>
