<?php
echo "<h1>Teste Simples de PHP</h1>";
echo "<p>Esta é uma página de teste para verificar se o servidor PHP está funcionando corretamente.</p>";
echo "<p>Data e hora atual: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
?>
