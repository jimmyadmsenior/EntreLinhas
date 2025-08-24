<?php
// Incluir arquivo de configuração
require_once "backend/config.php";

// Função para imprimir a estrutura de uma tabela
function descrever_tabela($conn, $tabela) {
    echo "<h2>Estrutura da tabela: $tabela</h2>";
    $resultado = mysqli_query($conn, "DESCRIBE $tabela");
    
    if ($resultado) {
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Chave</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = mysqli_fetch_assoc($resultado)) {
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
        echo "Erro ao descrever tabela: " . mysqli_error($conn);
    }
}

// Função para listar todas as tabelas no banco de dados
function listar_tabelas($conn) {
    echo "<h2>Tabelas no banco de dados</h2>";
    $resultado = mysqli_query($conn, "SHOW TABLES");
    
    if ($resultado) {
        echo "<ul>";
        while ($row = mysqli_fetch_row($resultado)) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "Erro ao listar tabelas: " . mysqli_error($conn);
    }
}

// Função para adicionar campo à tabela se não existir
function adicionar_campo_se_nao_existir($conn, $tabela, $campo, $definicao) {
    // Verificar se o campo existe
    $resultado = mysqli_query($conn, "SHOW COLUMNS FROM `$tabela` LIKE '$campo'");
    
    if (mysqli_num_rows($resultado) == 0) {
        // O campo não existe, vamos adicioná-lo
        $sql = "ALTER TABLE `$tabela` ADD COLUMN `$campo` $definicao";
        
        if (mysqli_query($conn, $sql)) {
            echo "<div style='color: green'>Campo '$campo' adicionado à tabela '$tabela' com sucesso!</div>";
        } else {
            echo "<div style='color: red'>Erro ao adicionar campo '$campo': " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div>Campo '$campo' já existe na tabela '$tabela'.</div>";
    }
}

// Função para criar tabela se não existir
function criar_tabela_se_nao_existir($conn, $tabela, $sql_create) {
    $verificar = mysqli_query($conn, "SHOW TABLES LIKE '$tabela'");
    
    if (mysqli_num_rows($verificar) == 0) {
        if (mysqli_query($conn, $sql_create)) {
            echo "<div style='color: green'>Tabela '$tabela' criada com sucesso!</div>";
        } else {
            echo "<div style='color: red'>Erro ao criar tabela '$tabela': " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div>Tabela '$tabela' já existe.</div>";
    }
}

// Verificar se a tabela fotos_perfil existe
$sql_criar_fotos_perfil = "CREATE TABLE fotos_perfil (
    id_usuario INT PRIMARY KEY,
    imagem_base64 LONGTEXT NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
)";

// Adicionar o campo 'tipo' à tabela 'usuarios' se não existir
adicionar_campo_se_nao_existir($conn, "usuarios", "tipo", "ENUM('admin', 'usuario', 'editor') DEFAULT 'usuario'");

// Criar a tabela de fotos de perfil se não existir
criar_tabela_se_nao_existir($conn, "fotos_perfil", $sql_criar_fotos_perfil);

// Listar tabelas
listar_tabelas($conn);

// Descrever tabelas importantes
descrever_tabela($conn, "usuarios");
descrever_tabela($conn, "artigos");
descrever_tabela($conn, "fotos_perfil");

echo "<h2>Verificação do banco de dados concluída</h2>";
?>
