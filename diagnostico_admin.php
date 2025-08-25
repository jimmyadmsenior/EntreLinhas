<?php
// Script para diagnosticar problemas de sessão admin
require_once "backend/config.php";
require_once "backend/session_helper.php";

echo "<h1>Diagnóstico de Sessão de Administrador</h1>";

// Exibir informações da sessão
echo "<h2>Informações da sessão</h2>";
echo "ID da sessão: " . session_id() . "<br>";
echo "Status da sessão: ";
switch (session_status()) {
    case PHP_SESSION_DISABLED:
        echo "Sessões desabilitadas";
        break;
    case PHP_SESSION_NONE:
        echo "Sessões habilitadas, mas nenhuma existe";
        break;
    case PHP_SESSION_ACTIVE:
        echo "Sessões habilitadas, e uma existe";
        break;
}
echo "<br><br>";

echo "<h2>Variáveis de sessão</h2>";
echo "loggedin: " . (isset($_SESSION["loggedin"]) ? ($_SESSION["loggedin"] ? "true" : "false") : "não definida") . "<br>";
echo "id: " . (isset($_SESSION["id"]) ? $_SESSION["id"] : "não definida") . "<br>";
echo "nome: " . (isset($_SESSION["nome"]) ? $_SESSION["nome"] : "não definida") . "<br>";
echo "email: " . (isset($_SESSION["email"]) ? $_SESSION["email"] : "não definida") . "<br>";
echo "tipo: " . (isset($_SESSION["tipo"]) ? $_SESSION["tipo"] : "não definida") . "<br><br>";

echo "<h2>Funções de verificação</h2>";
echo "is_logged_in(): " . (is_logged_in() ? "true" : "false") . "<br>";
echo "is_admin(): " . (is_admin() ? "true" : "false") . "<br><br>";

echo "<h2>Definir manualmente como admin</h2>";
$_SESSION["loggedin"] = true;
$_SESSION["tipo"] = "admin";
echo "Definido manualmente como admin.<br>";
echo "is_logged_in() agora: " . (is_logged_in() ? "true" : "false") . "<br>";
echo "is_admin() agora: " . (is_admin() ? "true" : "false") . "<br><br>";

// Informações de caminhos
echo "<h2>Informações de caminhos</h2>";
echo "Caminho atual: " . __DIR__ . "<br>";
echo "Document root: " . $_SERVER["DOCUMENT_ROOT"] . "<br>";
echo "Session save path: " . session_save_path() . "<br><br>";

echo "<h2>Configurações de cookies</h2>";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "<br>";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "<br>";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "<br>";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "<br>";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "<br><br>";

// Tentar acessar o dashboard diretamente
echo "<a href='PAGES/admin_dashboard.php'>Ir para o painel de administração</a><br>";
echo "<a href='PAGES/admin_dashboard.php?debug=1'>Ir para o painel com debug</a>";
?>
