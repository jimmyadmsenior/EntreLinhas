<?php
// Informações básicas sobre o PHP
echo "Versão do PHP: " . phpversion() . "<br>";
echo "Sistema Operacional do Servidor: " . PHP_OS . "<br>";
echo "Extensões carregadas:<br>";

$extensions = get_loaded_extensions();
sort($extensions);
echo "<ul>";
foreach ($extensions as $ext) {
    echo "<li>" . $ext . "</li>";
}
echo "</ul>";

// Verificar extensões críticas
$required_extensions = array("mysqli", "pdo_mysql", "json", "session");
echo "<br>Verificando extensões críticas:<br>";
foreach ($required_extensions as $ext) {
    if (in_array($ext, $extensions)) {
        echo $ext . ": <span style='color:green'>Disponível</span><br>";
    } else {
        echo $ext . ": <span style='color:red'>NÃO disponível</span><br>";
    }
}
?>
