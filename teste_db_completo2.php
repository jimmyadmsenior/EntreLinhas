<?php
// Configurações de exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste MySQL - InfinityFree</h1>";

// Testar conexão com mysqli
echo "<h2>Tentando método mysqli:</h2>";
try {
    // Utilizando localhost sem porta específica
    $mysqli = new mysqli("localhost", "if0_39798697", "xKIcJzBS13BB50t", "if0_39798697_entrelinhas");
    
    if ($mysqli->connect_error) {
        echo "<p style='color:red'>Erro mysqli: " . $mysqli->connect_error . " (Código: " . $mysqli->connect_errno . ")</p>";
    } else {
        echo "<p style='color:green'>✓ Conexão mysqli bem-sucedida!</p>";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exceção mysqli: " . $e->getMessage() . "</p>";
}

// Testar conexão com PDO
echo "<h2>Tentando método PDO:</h2>";
try {
    // Usando DSN mysql
    $pdo = new PDO("mysql:host=localhost;dbname=if0_39798697_entrelinhas", "if0_39798697", "xKIcJzBS13BB50t");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Conexão PDO bem-sucedida!</p>";
    $pdo = null;
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro PDO: " . $e->getMessage() . "</p>";
}

// Funções mysql_ são obsoletas no PHP moderno
echo "<h2>Sobre funções mysql_:</h2>";
echo "<p style='color:orange'>Funções mysql_ estão depreciadas desde o PHP 5.5.0 e foram removidas no PHP 7.0</p>";

// Informações do sistema
echo "<h2>Informações do sistema:</h2>";
echo "<p>PHP versão: " . phpversion() . "</p>";
echo "<p>Extensões carregadas:</p><ul>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (in_array($ext, ['mysqli', 'pdo', 'pdo_mysql', 'mysql'])) {
        echo "<li><strong>" . $ext . "</strong></li>";
    }
}
echo "</ul>";

// Verificar se estamos em localhost ou servidor remoto
echo "<p>Servidor: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Endereço IP do servidor: " . $_SERVER['SERVER_ADDR'] . "</p>";
?>
