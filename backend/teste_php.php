<?php
// Definindo a codificação
header('Content-Type: text/html; charset=utf-8');

// Verificar se PHP está funcionando
echo "<h1>Teste de PHP</h1>";
echo "<p>PHP está funcionando corretamente!</p>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";

// Verificar informações do ambiente
echo "<h2>Informações do ambiente:</h2>";
echo "<ul>";
echo "<li>Sistema operacional: " . php_uname() . "</li>";
echo "<li>Servidor web: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Diretório do script: " . __DIR__ . "</li>";
echo "</ul>";

// Verificar extensões carregadas
echo "<h2>Extensões PHP carregadas:</h2>";
$extensions = get_loaded_extensions();
echo "<p>Total de extensões: " . count($extensions) . "</p>";
echo "<ul>";
foreach (array_slice($extensions, 0, 10) as $ext) {
    echo "<li>$ext</li>";
}
echo "...</ul>";
?>
