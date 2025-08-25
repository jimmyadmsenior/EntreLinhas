<?php
// Script para testar o processo de login
require_once "backend/config.php";
require_once "backend/session_helper.php";

// Definir variáveis
$email = 'admin@example.com'; // Email do administrador
$senha = 'admin123';          // Senha que definimos no script de reset

echo "<h1>Teste de Login</h1>";
echo "Tentando fazer login com email: $email e senha: $senha<br><br>";

// Simular o processo de login que acontece em process_login.php
$sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $email);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) == 1) {
            // O email existe, verificar a senha
            mysqli_stmt_bind_result($stmt, $id, $nome, $db_email, $hashed_password, $tipo);
            
            if (mysqli_stmt_fetch($stmt)) {
                echo "Usuário encontrado:<br>";
                echo "ID: $id<br>";
                echo "Nome: $nome<br>";
                echo "Email: $db_email<br>";
                echo "Tipo: $tipo<br><br>";
                
                // Verificar senha
                if (password_verify($senha, $hashed_password)) {
                    echo "Senha válida! Login bem-sucedido.<br><br>";
                    
                    // Iniciar a sessão e definir variáveis de sessão
                    session_start_safe();
                    
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["nome"] = $nome;
                    $_SESSION["email"] = $db_email;
                    $_SESSION["tipo"] = $tipo;
                    
                    echo "Variáveis de sessão definidas:<br>";
                    echo "loggedin: " . ($_SESSION["loggedin"] ? "true" : "false") . "<br>";
                    echo "id: " . $_SESSION["id"] . "<br>";
                    echo "nome: " . $_SESSION["nome"] . "<br>";
                    echo "email: " . $_SESSION["email"] . "<br>";
                    echo "tipo: " . $_SESSION["tipo"] . "<br><br>";
                    
                    echo "Verificando is_admin(): " . (is_admin() ? "É admin" : "Não é admin") . "<br>";
                    
                    echo "<a href='PAGES/admin_dashboard.php'>Ir para o painel de administração</a>";
                } else {
                    echo "Senha inválida!<br>";
                }
            }
        } else {
            echo "Nenhum usuário encontrado com este email.<br>";
        }
    } else {
        echo "Erro ao executar consulta: " . mysqli_error($conn) . "<br>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Erro na preparação da consulta: " . mysqli_error($conn) . "<br>";
}

// Fechar conexão
mysqli_close($conn);
?>
