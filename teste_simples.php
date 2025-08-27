<?php
// Este arquivo apenas verifica se o PHP está funcionando e imprime algumas informações básicas
echo "<h1>Teste Básico de PHP</h1>";
echo "<p>PHP está funcionando corretamente!</p>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";
echo "<p>Diretório atual: " . getcwd() . "</p>";

// Tentar listar arquivos no diretório atual
echo "<h2>Arquivos no diretório atual:</h2>";
$files = scandir(".");
echo "<ul>";
foreach($files as $file) {
    echo "<li>" . htmlspecialchars($file) . "</li>";
}
echo "</ul>";

// Informações do servidor
echo "<h2>Informações do servidor:</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
?>
