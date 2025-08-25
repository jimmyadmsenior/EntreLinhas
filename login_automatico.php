<?php
// Script para fazer login automático como administrador
require_once "backend/config.php";
require_once "backend/session_helper.php";

$admin_email = "admin@example.com";
$admin_senha = "admin123";

echo "<h1>Login Automático como Administrador</h1>";
echo "Tentando fazer login com:<br>";
echo "Email: $admin_email<br>";
echo "Senha: $admin_senha<br><br>";

// Consultar o banco de dados
$sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $admin_email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 1) {
    mysqli_stmt_bind_result($stmt, $id, $nome, $email, $hashed_password, $tipo);
    mysqli_stmt_fetch($stmt);
    
    // Verificar a senha
    if (password_verify($admin_senha, $hashed_password)) {
        echo "Senha válida!<br>";
        echo "ID: $id<br>";
        echo "Nome: $nome<br>";
        echo "Tipo: $tipo<br><br>";
        
        // Definir as variáveis de sessão
        session_start_safe();
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $id;
        $_SESSION["nome"] = $nome;
        $_SESSION["email"] = $email;
        $_SESSION["tipo"] = $tipo;
        
        echo "Variáveis de sessão definidas:<br>";
        echo "loggedin: " . ($_SESSION["loggedin"] ? "true" : "false") . "<br>";
        echo "id: " . $_SESSION["id"] . "<br>";
        echo "nome: " . $_SESSION["nome"] . "<br>";
        echo "email: " . $_SESSION["email"] . "<br>";
        echo "tipo: " . $_SESSION["tipo"] . "<br><br>";
        
        echo "Status de login: " . (is_logged_in() ? "Logado" : "Não logado") . "<br>";
        echo "É admin: " . (is_admin() ? "Sim" : "Não") . "<br><br>";
        
        echo "<a href='PAGES/admin_dashboard.php'>Ir para o painel de administração</a>";
    } else {
        echo "Senha inválida!<br>";
    }
} else {
    echo "Nenhum usuário encontrado com esse email.<br>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
