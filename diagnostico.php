<?php
// Arquivo de diagnóstico básico para testar PHP e banco de dados

// Mostrar informações do PHP
echo "<h1>Diagnóstico PHP e Banco de Dados</h1>";

echo "<h2>Informações do PHP</h2>";
echo "<ul>";
echo "<li>Versão PHP: " . phpversion() . "</li>";
echo "<li>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>Script Path: " . $_SERVER['SCRIPT_FILENAME'] . "</li>";
echo "</ul>";

// Testar se o arquivo config.php pode ser incluído
echo "<h2>Teste de inclusão de arquivos</h2>";
try {
    require_once "backend/config.php";
    echo "<p style='color:green'>✓ Arquivo config.php incluído com sucesso!</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Erro ao incluir config.php: " . $e->getMessage() . "</p>";
}

// Testar conexão com banco de dados
echo "<h2>Teste de conexão com banco de dados</h2>";
if (isset($conn) && $conn) {
    echo "<p style='color:green'>✓ Conexão com o banco de dados estabelecida!</p>";
    
    // Verificar tabelas
    echo "<h3>Verificando tabelas</h3>";
    $tables_result = mysqli_query($conn, "SHOW TABLES");
    if ($tables_result) {
        echo "<ul>";
        while ($table = mysqli_fetch_array($tables_result)) {
            echo "<li>" . $table[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>✗ Erro ao consultar tabelas: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:red'>✗ Falha na conexão com o banco de dados!</p>";
}

// Testar sessão
echo "<h2>Teste de sessão</h2>";
session_start();
$_SESSION['test_value'] = "Teste de sessão: " . date("Y-m-d H:i:s");
echo "<p>Valor armazenado na sessão: " . $_SESSION['test_value'] . "</p>";

// Testar cookies
echo "<h2>Teste de cookies</h2>";
setcookie("test_cookie", "Funcionando!", time() + 3600, "/");
echo "<p>Cookie 'test_cookie' definido. Recarregue a página para verificar.</p>";
if (isset($_COOKIE['test_cookie'])) {
    echo "<p style='color:green'>✓ Cookie encontrado: " . $_COOKIE['test_cookie'] . "</p>";
}

// Verificar permissões de arquivos
echo "<h2>Verificação de permissões</h2>";
$paths_to_check = [
    ".",
    "./backend",
    "./PAGES",
    "./assets"
];

foreach ($paths_to_check as $path) {
    echo "<h3>$path</h3>";
    if (file_exists($path)) {
        echo "<p>Existe: Sim</p>";
        echo "<p>Permissões: " . substr(sprintf('%o', fileperms($path)), -4) . "</p>";
        echo "<p>Leitura: " . (is_readable($path) ? "Sim" : "Não") . "</p>";
        echo "<p>Escrita: " . (is_writable($path) ? "Sim" : "Não") . "</p>";
    } else {
        echo "<p style='color:red'>✗ Caminho não encontrado!</p>";
    }
}

echo "<h2>Links de teste</h2>";
echo "<ul>";
echo "<li><a href='adicionar_resumo.php'>Adicionar coluna resumo à tabela artigos</a></li>";
echo "<li><a href='verificar_tabelas.php'>Verificar estrutura do banco de dados</a></li>";
echo "<li><a href='PAGES/index.php'>Página inicial</a></li>";
echo "<li><a href='PAGES/contato.php'>Página de contato</a></li>";
echo "</ul>";
?>
