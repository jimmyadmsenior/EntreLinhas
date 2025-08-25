<?php
// Este script força a sessão como administrador e redireciona para o admin dashboard
require_once "backend/config.php";
require_once "backend/session_helper.php";

// Configurações de cookie de sessão mais robustas
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600); // 1 hora

// Destruir qualquer sessão anterior
session_start_safe();
session_unset();
session_destroy();

// Iniciar uma nova sessão limpa
session_start_safe();

echo "<h1>Forçando acesso como administrador</h1>";

// Verificar se o usuário administrador existe
$admin_email = "admin@example.com";
$admin_senha = "admin123";

$sql = "SELECT id, nome, email, tipo FROM usuarios WHERE email = ? AND tipo = 'admin'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $admin_email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 1) {
    mysqli_stmt_bind_result($stmt, $id, $nome, $email, $tipo);
    mysqli_stmt_fetch($stmt);
    
    // Definir variáveis de sessão manualmente
    $_SESSION["loggedin"] = true;
    $_SESSION["id"] = $id;
    $_SESSION["nome"] = $nome;
    $_SESSION["email"] = $email;
    $_SESSION["tipo"] = $tipo;
    
    echo "Sessão definida como administrador:<br>";
    echo "ID de sessão: " . session_id() . "<br>";
    echo "loggedin: " . ($_SESSION["loggedin"] ? "true" : "false") . "<br>";
    echo "tipo: " . $_SESSION["tipo"] . "<br>";
    echo "is_admin(): " . (is_admin() ? "Sim" : "Não") . "<br><br>";
    
    // Links para redirecionamentos
    echo "<p><a href='PAGES/admin_dashboard.php?debug=1&force=1' target='_blank'>Abrir admin_dashboard.php com debug</a></p>";
    echo "<p><a href='PAGES/admin_dashboard.php' target='_blank'>Abrir admin_dashboard.php normal</a></p>";
    echo "<hr>";
    
    // Salvar o ID da sessão em um cookie separado para depuração
    setcookie("admin_session_id", session_id(), time() + 3600, "/");
    
    // Formulário para enviar diretamente para admin_dashboard.php
    echo "<h2>Formulário para envio direto</h2>";
    echo "<form action='PAGES/admin_dashboard.php' method='post'>";
    echo "<input type='hidden' name='session_id' value='" . session_id() . "'>";
    echo "<button type='submit'>Enviar para admin_dashboard.php via POST</button>";
    echo "</form>";
    
} else {
    echo "Administrador não encontrado!";
}
?>
