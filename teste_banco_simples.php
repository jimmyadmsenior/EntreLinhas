<?php
// Configurações de exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão ao Banco de Dados</h1>";

// Testando se o módulo mysqli está disponível
if (!extension_loaded('mysqli')) {
    echo "<p style='color:red'>ERRO: A extensão mysqli não está instalada ou habilitada!</p>";
    exit();
} else {
    echo "<p style='color:green'>✓ Extensão mysqli está disponível</p>";
}

// Informações de conexão
echo "<h2>Tentando conectar ao banco de dados usando 'localhost'...</h2>";

try {
    $conn = new mysqli("localhost", "if0_39798697", "xKIcJzBS13BB50t", "if0_39798697_entrelinhas");
    
    if ($conn->connect_error) {
        echo "<p style='color:red'>Falha na conexão usando 'localhost': " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color:green'>✓ Conexão bem-sucedida usando 'localhost'!</p>";
        echo "<p>Versão do servidor: " . $conn->server_info . "</p>";
        echo "<p>Host info: " . $conn->host_info . "</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exceção capturada: " . $e->getMessage() . "</p>";
}

// Mostrar a documentação do PHP sobre conexão mysqli
echo "<h2>Outras informações úteis:</h2>";
echo "<p>O servidor web é: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>O nome do host é: " . gethostname() . "</p>";
echo "<p>O IP do servidor é: " . $_SERVER['SERVER_ADDR'] . "</p>";
echo "<p>O diretório raiz é: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
?>
