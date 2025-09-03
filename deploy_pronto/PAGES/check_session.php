<?php
// Script para verificar informações da sessão atual

// Iniciar a sessão
session_start();

echo "<h1>Informações da Sessão</h1>";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo "<p style='color: green;'>✅ Usuário está logado</p>";
    
    echo "<h2>Dados da sessão:</h2>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $_SESSION["id"] . "</li>";
    echo "<li><strong>Nome:</strong> " . $_SESSION["nome"] . "</li>";
    echo "<li><strong>Email:</strong> " . $_SESSION["email"] . "</li>";
    echo "<li><strong>Tipo:</strong> " . ($_SESSION["tipo"] ?? "Não definido") . "</li>";
    echo "</ul>";
    
    echo "<p>O usuário " . ($_SESSION["tipo"] === "admin" ? "<strong>é administrador</strong>" : "não é administrador") . ".</p>";
} else {
    echo "<p style='color: red;'>❌ Nenhum usuário está logado</p>";
}

echo "<h2>Dados completos da sessão:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<p><a href='index.html'>Voltar para a página inicial</a></p>";
echo "<p><a href='admin_dashboard.php'>Ir para o painel de administração</a></p>";
echo "<p><a href='logout.php'>Sair (Logout)</a></p>";
?>
