<?php
// Exibir todos os erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Conexão no InfinityFree</h1>";

echo "<h2>1. Informações do Servidor</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
echo "HTTP Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "Remote Addr: " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "</pre>";

echo "<h2>2. Verificação de Extensões</h2>";
echo "<pre>";
foreach (['pdo', 'pdo_mysql', 'mysqli', 'mbstring'] as $ext) {
    echo "Extensão {$ext}: " . (extension_loaded($ext) ? "OK" : "NÃO INSTALADA") . "\n";
}
echo "</pre>";

echo "<h2>3. Teste de Conexão com MySQL</h2>";
echo "<pre>";

// Definir parâmetros de conexão manualmente para este teste
$db_host = "localhost"; // Tente com localhost
$db_user = "if0_39798697";
$db_pass = "xKIcJzBS13BB50t";
$db_name = "if0_39798697_entrelinhas";

// Teste 1: mysqli
echo "Teste mysqli com localhost:\n";
try {
    $mysqli = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli) {
        echo "- Conexão mysqli bem-sucedida!\n";
        echo "- Informações de conexão: " . mysqli_get_host_info($mysqli) . "\n";
        echo "- Versão do servidor: " . mysqli_get_server_info($mysqli) . "\n";
        mysqli_close($mysqli);
    } else {
        echo "- ERRO mysqli: " . mysqli_connect_error() . "\n";
    }
} catch (Exception $e) {
    echo "- EXCEÇÃO mysqli: " . $e->getMessage() . "\n";
}

// Teste 2: PDO
echo "\nTeste PDO com localhost:\n";
try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    echo "- Conexão PDO bem-sucedida!\n";
    echo "- Versão do servidor: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    echo "- Status da conexão: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
    $pdo = null;
} catch (PDOException $e) {
    echo "- ERRO PDO: " . $e->getMessage() . "\n";
}

// Teste 3: mysqli com SQL server explícito
echo "\nTeste mysqli com nome de servidor SQL explícito:\n";
try {
    $sql_host = "sql312.infinityfree.com"; // Substitua pelo seu servidor SQL real
    $mysqli = mysqli_connect($sql_host, $db_user, $db_pass, $db_name);
    if ($mysqli) {
        echo "- Conexão mysqli bem-sucedida com servidor SQL explícito!\n";
        echo "- Informações de conexão: " . mysqli_get_host_info($mysqli) . "\n";
        mysqli_close($mysqli);
    } else {
        echo "- ERRO mysqli com servidor SQL explícito: " . mysqli_connect_error() . "\n";
    }
} catch (Exception $e) {
    echo "- EXCEÇÃO mysqli com servidor SQL explícito: " . $e->getMessage() . "\n";
}

// Teste 4: Verificar se o banco existe
echo "\nVerificar banco de dados e tabelas:\n";
try {
    $mysqli = mysqli_connect($db_host, $db_user, $db_pass);
    if ($mysqli) {
        $result = mysqli_query($mysqli, "SHOW DATABASES LIKE '{$db_name}'");
        if (mysqli_num_rows($result) > 0) {
            echo "- Banco de dados '{$db_name}' existe.\n";
            
            mysqli_select_db($mysqli, $db_name);
            $tables_result = mysqli_query($mysqli, "SHOW TABLES");
            if (mysqli_num_rows($tables_result) > 0) {
                echo "- Tabelas no banco de dados:\n";
                while ($row = mysqli_fetch_array($tables_result)) {
                    echo "  - " . $row[0] . "\n";
                }
            } else {
                echo "- Não há tabelas no banco de dados.\n";
            }
        } else {
            echo "- ERRO: Banco de dados '{$db_name}' não existe!\n";
        }
        mysqli_close($mysqli);
    }
} catch (Exception $e) {
    echo "- EXCEÇÃO ao verificar banco de dados: " . $e->getMessage() . "\n";
}

echo "</pre>";

echo "<h2>4. Arquivos de Configuração</h2>";
echo "<pre>";
echo "Verificando arquivos de configuração:\n";

// Funções para verificação segura
function verificarArquivo($caminho) {
    if (file_exists($caminho)) {
        echo "- " . basename($caminho) . ": EXISTE (" . filesize($caminho) . " bytes)\n";
        return true;
    } else {
        echo "- " . basename($caminho) . ": NÃO EXISTE\n";
        return false;
    }
}

// Verificar arquivos principais
verificarArquivo(__DIR__ . "/backend/config.php");
verificarArquivo(__DIR__ . "/backend/config.infinityfree.php");
verificarArquivo(__DIR__ . "/backend/config_infinityfree.php");
verificarArquivo(__DIR__ . "/backend/config.local.php");
verificarArquivo(__DIR__ . "/backend/db_adapter.php");

echo "</pre>";

echo "<h2>Conclusão</h2>";
echo "<p>Este diagnóstico ajuda a identificar problemas com a conexão do banco de dados.</p>";
echo "<p>Se nenhum dos testes acima conseguir se conectar ao banco de dados, verifique:</p>";
echo "<ul>";
echo "<li>As credenciais do banco de dados estão corretas?</li>";
echo "<li>O banco de dados foi criado no painel do InfinityFree?</li>";
echo "<li>O usuário tem permissão para acessar o banco de dados?</li>";
echo "</ul>";

// Salvar resultado em um arquivo de log
$log_content = ob_get_contents();
$log_file = __DIR__ . '/diagnostic_log_' . date('Y-m-d_H-i-s') . '.html';
file_put_contents($log_file, $log_content);
?>
