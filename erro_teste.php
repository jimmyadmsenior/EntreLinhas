<?php
// Verificar configuração de erros do PHP
echo "Verificando configurações de erro do PHP:<br>";
echo "display_errors: " . ini_get('display_errors') . "<br>";
echo "error_reporting: " . ini_get('error_reporting') . "<br>";

// Força a exibição de erros para este script
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<br>Configurações após modificação:<br>";
echo "display_errors: " . ini_get('display_errors') . "<br>";
echo "error_reporting: " . ini_get('error_reporting') . "<br>";

// Teste de erro deliberado para ver se os erros estão sendo exibidos
echo "<br>Tentando gerar um erro de exemplo:<br>";
try {
    // Gere um erro deliberado
    $undefined_var = $this_variable_does_not_exist;
} catch (Error $e) {
    echo "Erro capturado: " . $e->getMessage();
}
?>
