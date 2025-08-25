<?php
// Definir manualmente as variáveis de sessão para acesso administrativo
session_start();

// Definir sessão como administrador
$_SESSION['loggedin'] = true;
$_SESSION['id'] = 1;
$_SESSION['nome'] = "Administrador";
$_SESSION['email'] = "admin@example.com";
$_SESSION['tipo'] = "admin";

// Verificar se está funcionando
$is_admin = isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
echo "<p>Status de admin: " . ($is_admin ? "SIM" : "NÃO") . "</p>";

// Redirecionar para o painel admin
echo "<p><a href='admin_dashboard.php'>Acessar o painel administrativo</a></p>";
echo "<p><a href='admin_simples.php'>Acessar o painel administrativo simplificado</a></p>";
?>