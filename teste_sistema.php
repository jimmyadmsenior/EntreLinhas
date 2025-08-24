<?php
// Arquivo de teste para verificar se o PHP está funcionando corretamente

// Informações básicas
echo "<h1>Teste PHP</h1>";
echo "<p>PHP está funcionando corretamente!</p>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";
echo "<hr>";

// Verificar conexão com banco de dados
echo "<h2>Verificando conexão com o banco de dados:</h2>";
try {
    require_once "backend/config.php";
    echo "<p style='color: green;'>✅ Conexão com o banco de dados estabelecida com sucesso!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao conectar com o banco de dados: " . $e->getMessage() . "</p>";
}

// Verificar sessões
echo "<h2>Verificando sessões:</h2>";
if (!isset($_SESSION)) {
    session_start();
}
echo "<p>ID da sessão: " . session_id() . "</p>";
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo "<p style='color: green;'>✅ Usuário está logado: " . $_SESSION["nome"] . "</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhum usuário logado</p>";
}

// Verificar permissões de arquivos
echo "<h2>Verificando permissões de arquivos:</h2>";
$important_dirs = ["backend", "PAGES", "assets"];
foreach ($important_dirs as $dir) {
    if (is_dir($dir)) {
        echo "<p>Diretório '$dir': Existe, ";
        if (is_readable($dir)) {
            echo "<span style='color: green;'>Leitura OK</span>, ";
        } else {
            echo "<span style='color: red;'>Sem permissão de leitura</span>, ";
        }
        if (is_writable($dir)) {
            echo "<span style='color: green;'>Escrita OK</span></p>";
        } else {
            echo "<span style='color: red;'>Sem permissão de escrita</span></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Diretório '$dir' não encontrado!</p>";
    }
}

// Verificar cookies
echo "<h2>Verificando cookies:</h2>";
if (count($_COOKIE) > 0) {
    echo "<p>Cookies definidos:</p><ul>";
    foreach ($_COOKIE as $name => $value) {
        echo "<li>" . htmlspecialchars($name) . ": " . htmlspecialchars(substr($value, 0, 30)) . (strlen($value) > 30 ? "..." : "") . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nenhum cookie definido.</p>";
}

echo "<hr>";
echo "<p><a href='PAGES/index.php'>Voltar para a página inicial</a></p>";
?>
