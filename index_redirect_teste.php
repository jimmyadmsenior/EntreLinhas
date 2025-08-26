<?php
// Teste de redirecionamento explícito
echo "Teste de redirecionamento para PAGES/index.php";
echo "<br><br>Se você continuar vendo esta página após 3 segundos, o redirecionamento falhou.";
echo "<br><br>Redirecionando em 3 segundos...";

// Adicione um caminho absoluto para teste
$base_url = "http://" . $_SERVER['HTTP_HOST'];
header("refresh:3;url=" . $base_url . "/PAGES/index.php");
?>
