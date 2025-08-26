<?php
// Página muito simples para testar se o PHP está funcionando

// Informações do PHP
echo "<h1>Informações do PHP</h1>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";
echo "<p>Data atual: " . date("Y-m-d H:i:s") . "</p>";

// Outras informações úteis
echo "<h2>Diretório atual</h2>";
echo "<p>" . getcwd() . "</p>";

echo "<h2>Servidor</h2>";
echo "<p>SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>PHP_SELF: " . $_SERVER['PHP_SELF'] . "</p>";
echo "<p>DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

echo "<h2>Arquivos importantes</h2>";
$arquivos = [
    "../backend/config.php",
    "../backend/usuario_helper.php",
    "includes/header.php",
    "includes/footer.php",
    "../assets/css/style.css"
];

echo "<ul>";
foreach ($arquivos as $arquivo) {
    echo "<li>" . $arquivo . ": " . (file_exists($arquivo) ? "Existe" : "Não existe") . "</li>";
}
echo "</ul>";

echo "<h2>Teste de sessão</h2>";
session_start();
$_SESSION['teste'] = "Teste de sessão: " . date("H:i:s");
echo "<p>Variável de sessão definida: " . $_SESSION['teste'] . "</p>";

echo "<h2>Link para enviar-artigo.php</h2>";
echo "<p><a href='enviar-artigo.php'>Acessar enviar-artigo.php</a></p>";
?>
