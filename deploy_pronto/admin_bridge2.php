<?php
// Este arquivo usa outro método para definir a sessão admin
// Salva variáveis de sessão diretamente no arquivo da sessão

// Iniciar sessão
session_start();

// Definir sessão como admin
$_SESSION["loggedin"] = true;
$_SESSION["id"] = 1;
$_SESSION["nome"] = "Jimmy Castilho";
$_SESSION["email"] = "jimmycastilho555@gmail.com";
$_SESSION["tipo"] = "admin";

// Debug
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Link para admin
echo "<a href='PAGES/admin_dashboard.php'>Ir para o painel admin</a>";
?>