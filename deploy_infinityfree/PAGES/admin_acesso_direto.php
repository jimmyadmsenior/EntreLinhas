<?php
// Este arquivo serve como ponte para o painel administrativo
// Ele garante que as variáveis de sessão estão corretas

// Iniciar sessão
session_start();

// Definir como administrador para teste
$_SESSION["loggedin"] = true;
$_SESSION["tipo"] = "admin";
$_SESSION["id"] = 1;
$_SESSION["nome"] = "Administrador";
$_SESSION["email"] = "admin@example.com";

// Verificar se estamos configurados como admin
if ($_SESSION["loggedin"] === true && $_SESSION["tipo"] === "admin") {
    echo "<p style='color:green'>Sessão configurada com sucesso!</p>";
    echo "<p>Redirecionando para o painel administrativo...</p>";
    
    // Links para as duas versões do painel
    echo "<p><a href='admin_dashboard.php'>Acessar painel original</a></p>";
    echo "<p><a href='admin_dashboard_novo.php'>Acessar painel novo</a></p>";
    
    // Redirecionar automaticamente após 2 segundos
    echo "<script>
        setTimeout(function() {
            window.location.href = 'admin_dashboard_novo.php';
        }, 2000);
    </script>";
} else {
    echo "<p style='color:red'>Erro ao configurar a sessão.</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}
?>
