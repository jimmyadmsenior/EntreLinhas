<?php
// Arquivo de teste para conexão local
echo "<h1>Teste de Conexão Local</h1>";

// Incluir o arquivo de configuração
require_once 'backend/config.php';

// Testar a conexão
if ($conn && !mysqli_connect_errno()) {
    echo "<p style='color:green'>✅ Conexão com o banco de dados estabelecida com sucesso!</p>";
    echo "<p>Servidor: " . DB_SERVER . "</p>";
    echo "<p>Banco de Dados: " . DB_NAME . "</p>";
    echo "<p>Usuário: " . DB_USERNAME . "</p>";
    
    // Testar uma consulta
    $result = mysqli_query($conn, "SHOW TABLES");
    if ($result) {
        echo "<p>Tabelas no banco de dados:</p>";
        echo "<ul>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'>⚠️ Conexão ok, mas não foi possível listar as tabelas.</p>";
    }
} else {
    echo "<p style='color:red'>❌ Erro na conexão com o banco de dados: " . mysqli_connect_error() . "</p>";
}
?>
