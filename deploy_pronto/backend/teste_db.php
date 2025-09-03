<?php
// Habilitar relatório de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir arquivo de configuração
require_once "config.php";

// Verificar conexão
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}

echo "Conexão com banco de dados bem-sucedida!";
echo "<br>Versão do PHP: " . phpversion();
echo "<br>Versão do MySQL: " . mysqli_get_server_info($conn);

// Testar consulta
$result = mysqli_query($conn, "SHOW TABLES");
if ($result) {
    echo "<h3>Tabelas no banco de dados:</h3>";
    while ($row = mysqli_fetch_row($result)) {
        echo $row[0] . "<br>";
    }
    mysqli_free_result($result);
} else {
    echo "Erro ao consultar tabelas: " . mysqli_error($conn);
}

// Fechar conexão
mysqli_close($conn);
?>
