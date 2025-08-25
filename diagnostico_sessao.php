<?php
// Script para diagnosticar problemas de sessão
require_once "backend/session_helper.php";
session_start_safe();

echo "<h1>Diagnóstico de Sessão</h1>";

// Verificar variáveis de sessão
echo "<h2>Variáveis de Sessão</h2>";
echo "ID da Sessão: " . session_id() . "<br>";
echo "Status da Sessão: " . (session_status() == PHP_SESSION_ACTIVE ? "Ativa" : "Inativa") . "<br>";

if (isset($_SESSION["loggedin"])) {
    echo "Logado: " . ($_SESSION["loggedin"] ? "Sim" : "Não") . "<br>";
    
    if (isset($_SESSION["id"])) echo "ID do Usuário: " . $_SESSION["id"] . "<br>";
    if (isset($_SESSION["nome"])) echo "Nome: " . $_SESSION["nome"] . "<br>";
    if (isset($_SESSION["email"])) echo "Email: " . $_SESSION["email"] . "<br>";
    if (isset($_SESSION["tipo"])) echo "Tipo: " . $_SESSION["tipo"] . "<br>";
    
    echo "Is Admin: " . (is_admin() ? "Sim" : "Não") . "<br>";
} else {
    echo "Usuário não está logado (variável de sessão 'loggedin' não existe).<br>";
}

// Verificar cookies
echo "<h2>Cookies</h2>";
foreach ($_COOKIE as $name => $value) {
    echo "$name: $value<br>";
}

// Caminho da sessão
echo "<h2>Informações da Sessão</h2>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "Session Name: " . session_name() . "<br>";

// Tentar definir uma variável de sessão de teste
$_SESSION["teste"] = "funcionando";
echo "Variável de teste definida como: " . $_SESSION["teste"] . "<br>";

// Verificar se as funções do session_helper estão funcionando
echo "<h2>Funções do Session Helper</h2>";
echo "is_logged_in(): " . (is_logged_in() ? "Verdadeiro" : "Falso") . "<br>";
echo "is_admin(): " . (is_admin() ? "Verdadeiro" : "Falso") . "<br>";

// Links úteis
echo "<h2>Links Úteis</h2>";
echo "<a href='teste_login_automatico.php'>Fazer login automático</a><br>";
echo "<a href='PAGES/admin_dashboard.php'>Tentar acessar o painel de administração</a><br>";
echo "<a href='backend/logout.php'>Fazer logout</a><br>";
?>
