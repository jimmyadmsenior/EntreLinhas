<?php
// Script para testar a função de verificação de senha

echo "<h1>Teste de Verificação de Senha</h1>";

// Senha em texto puro
$senha = "Admin@123";

// Hash da senha original
$hash_original = '$2y$10$uJRfPaOfDvHWQBx14oj.wOZA4ZRAVa6vsZ2qixG0xHzK0p6SjaxSq';

// Criar novo hash
$novo_hash = password_hash($senha, PASSWORD_DEFAULT);

echo "<p>Senha em texto puro: <strong>$senha</strong></p>";
echo "<p>Hash original: $hash_original</p>";
echo "<p>Novo hash: $novo_hash</p>";

// Testar verificação com o hash original
$verificacao_original = password_verify($senha, $hash_original);
echo "<p>Verificação com hash original: " . ($verificacao_original ? "✅ SUCESSO" : "❌ FALHA") . "</p>";

// Testar verificação com o novo hash
$verificacao_novo = password_verify($senha, $novo_hash);
echo "<p>Verificação com novo hash: " . ($verificacao_novo ? "✅ SUCESSO" : "❌ FALHA") . "</p>";

// Informações sobre a versão do PHP e as funções de hash disponíveis
echo "<h2>Informações do ambiente:</h2>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";
echo "<p>Algoritmos de hash disponíveis: " . implode(", ", hash_algos()) . "</p>";
echo "<p>Password Hash Default: " . PASSWORD_DEFAULT . "</p>";

echo "<p><a href='teste_login.php'>Voltar para a página de teste de login</a></p>";
?>
