<?php
// Exibir todos os erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste das Credenciais Atualizadas do Banco de Dados</h1>";

// Definir as credenciais atualizadas
$db_host = 'sql302.infinityfree.com';
$db_user = 'if0_39798697';
$db_pass = 'jimmysenai123';
$db_name = 'if0_39798697_entrelinhas';

echo "<h2>Credenciais configuradas</h2>";
echo "<ul>";
echo "<li>Host: $db_host</li>";
echo "<li>Usuário: $db_user</li>";
echo "<li>Banco: $db_name</li>";
echo "</ul>";

echo "<h2>Tentativa de conexão com PDO</h2>";

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    echo "<p style='color:green;font-weight:bold;'>✅ Conexão PDO bem-sucedida!</p>";
    echo "<p>Versão do servidor: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    echo "<p>Status da conexão: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "</p>";
    
    // Testar se consegue listar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tabelas encontradas:</h3>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        echo "<li>$tabela</li>";
    }
    echo "</ul>";
    
    $pdo = null;
} catch (PDOException $e) {
    echo "<p style='color:red;font-weight:bold;'>❌ Erro na conexão PDO: " . $e->getMessage() . "</p>";
}

echo "<h2>Tentativa de conexão com mysqli</h2>";

try {
    $mysqli = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli) {
        echo "<p style='color:green;font-weight:bold;'>✅ Conexão mysqli bem-sucedida!</p>";
        echo "<p>Informações de conexão: " . mysqli_get_host_info($mysqli) . "</p>";
        echo "<p>Versão do servidor: " . mysqli_get_server_info($mysqli) . "</p>";
        
        // Fechar a conexão
        mysqli_close($mysqli);
    } else {
        echo "<p style='color:red;font-weight:bold;'>❌ Erro na conexão mysqli: " . mysqli_connect_error() . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;font-weight:bold;'>❌ Exceção ao conectar com mysqli: " . $e->getMessage() . "</p>";
}

echo "<h2>Teste com Host 'localhost'</h2>";
echo "<p>Algumas hospedagens compartilhadas exigem 'localhost' como host do banco de dados.</p>";

try {
    $local_host = 'localhost';
    $dsn = "mysql:host=$local_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    echo "<p style='color:green;font-weight:bold;'>✅ Conexão PDO com 'localhost' bem-sucedida!</p>";
    $pdo = null;
} catch (PDOException $e) {
    echo "<p style='color:red;font-weight:bold;'>❌ Erro na conexão PDO com 'localhost': " . $e->getMessage() . "</p>";
}

echo "<h2>Verificação dos arquivos de configuração</h2>";

function verificarArquivo($caminho) {
    if (file_exists($caminho)) {
        $conteudo = file_get_contents($caminho);
        $tem_sql302 = strpos($conteudo, 'sql302.infinityfree.com') !== false;
        $tem_usuario = strpos($conteudo, 'if0_39798697') !== false;
        $tem_senha = strpos($conteudo, 'jimmysenai123') !== false;
        
        echo "<p>" . basename($caminho) . ": ";
        echo $tem_sql302 ? "✅" : "❌";
        echo " host, ";
        echo $tem_usuario ? "✅" : "❌";
        echo " usuário, ";
        echo $tem_senha ? "✅" : "❌";
        echo " senha</p>";
    } else {
        echo "<p>" . basename($caminho) . ": Arquivo não encontrado</p>";
    }
}

// Verificar os principais arquivos de configuração
verificarArquivo(__DIR__ . "/backend/config.php");
verificarArquivo(__DIR__ . "/backend/config.infinityfree.php");
verificarArquivo(__DIR__ . "/backend/config_infinityfree.php");
verificarArquivo(__DIR__ . "/backend/db_connection_fix.php");

echo "<h2>Recomendações</h2>";
echo "<ul>";
echo "<li>Se a conexão com 'sql302.infinityfree.com' falhar, tente usar 'localhost' como host</li>";
echo "<li>Verifique se o banco de dados 'if0_39798697_entrelinhas' existe no painel do InfinityFree</li>";
echo "<li>Certifique-se de que o usuário 'if0_39798697' tem permissões no banco de dados</li>";
echo "</ul>";
?>
