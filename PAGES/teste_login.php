<?php
// Este é um script simples para testar o login diretamente sem o JavaScript

// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Mostrar erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Login</h1>";

// Se o usuário enviou o formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? "";
    $senha = $_POST["senha"] ?? "";
    
    echo "<p>Tentando fazer login com: Email: $email, Senha: ******</p>";
    
    // Verificar se o usuário existe no banco de dados
    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $nome, $db_email, $hashed_password, $tipo);
                
                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($senha, $hashed_password)) {
                        echo "<p style='color:green;'>✅ Login bem-sucedido! Redirecionando...</p>";
                        echo "<script>
                            setTimeout(function() {
                                window.location.href = 'index.html';
                            }, 2000);
                        </script>";
                        
                        // Definir sessão
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["nome"] = $nome;
                        $_SESSION["email"] = $db_email;
                        $_SESSION["tipo"] = $tipo;
                    } else {
                        echo "<p style='color:red;'>❌ Senha incorreta!</p>";
                    }
                }
            } else {
                echo "<p style='color:red;'>❌ Usuário não encontrado com este e-mail!</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Erro ao executar a consulta: " . mysqli_error($conn) . "</p>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<p style='color:red;'>❌ Erro na preparação da consulta: " . mysqli_error($conn) . "</p>";
    }
}

// Informações do banco de dados para depuração
echo "<h2>Informações de conexão:</h2>";
echo "<p>Servidor: " . DB_SERVER . "</p>";
echo "<p>Usuário: " . DB_USERNAME . "</p>";
echo "<p>Banco de dados: " . DB_NAME . "</p>";

// Verificar conexão
if ($conn) {
    echo "<p style='color:green;'>✅ Conexão com o banco de dados OK!</p>";
    
    // Listar usuários para depuração
    $result = mysqli_query($conn, "SELECT id, nome, email, tipo, status FROM usuarios");
    
    echo "<h2>Usuários cadastrados:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Status</th></tr>";
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['nome'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['tipo'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Nenhum usuário encontrado</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ Falha na conexão com o banco de dados!</p>";
}
?>

<h2>Teste de Login Manual</h2>
<form method="post" action="">
    <div>
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required value="jimmycastilho555@gmail.com">
    </div>
    <div style="margin-top: 10px;">
        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required value="Admin@123">
    </div>
    <div style="margin-top: 10px;">
        <button type="submit">Entrar</button>
    </div>
</form>

<p><a href="login.html">Voltar para a página de login</a></p>
