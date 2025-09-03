<?php
// Este arquivo testa a sessão diretamente na pasta PAGES
require_once "../backend/session_helper.php";
require_once "../backend/config.php";

echo "<h1>Teste de Sessão na Pasta PAGES</h1>";

// Informações sobre a sessão
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

// Exibir variáveis de sessão
echo "<h2>Variáveis de sessão</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Funções de verificação
echo "<h2>Funções de verificação</h2>";
echo "is_logged_in(): " . (is_logged_in() ? "true" : "false") . "<br>";
echo "is_admin(): " . (is_admin() ? "true" : "false") . "<br><br>";

// Caminhos
echo "<h2>Caminhos</h2>";
echo "Diretório atual: " . __DIR__ . "<br>";
echo "Backend path: " . realpath(__DIR__ . "/../backend") . "<br>";

// Verificar se podemos acessar o admin_dashboard
echo "<h2>Teste de acesso ao dashboard</h2>";
if (is_admin()) {
    echo "<p style='color:green'>Acesso permitido ao dashboard!</p>";
    echo "<a href='admin_dashboard.php'>Ir para o painel de administração</a>";
} else {
    echo "<p style='color:red'>Acesso negado ao dashboard!</p>";
    echo "<p>Definindo sessão como admin manualmente...</p>";
    
    $_SESSION["loggedin"] = true;
    $_SESSION["tipo"] = "admin";
    
    echo "is_admin() agora: " . (is_admin() ? "true" : "false") . "<br>";
    echo "<a href='admin_dashboard.php'>Tentar acessar o painel agora</a>";
}
?>
