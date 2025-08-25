<?php
// Script para testar o login diretamente
require_once 'backend/config.php';

// Função para testar login
function testar_login($email, $senha) {
    global $conn;
    
    echo "<h3>Testando login para: {$email}</h3>";
    
    // Consultar o usuário
    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Usuário encontrado
        echo "<p>✓ Usuário encontrado: {$row['nome']} ({$row['tipo']})</p>";
        
        // Verificar a senha
        if (password_verify($senha, $row['senha'])) {
            echo "<p style='color:green;'>✓ Senha correta! O login deve funcionar.</p>";
            return true;
        } else {
            echo "<p style='color:red;'>✗ Senha incorreta!</p>";
            echo "<p>Senha fornecida: {$senha}</p>";
            echo "<p>Hash armazenado: {$row['senha']}</p>";
            
            // Testar se é uma senha plain text por coincidência
            if ($senha === $row['senha']) {
                echo "<p style='color:orange;'>⚠️ A senha está armazenada como texto puro!</p>";
            }
            
            return false;
        }
    } else {
        echo "<p style='color:red;'>✗ Usuário não encontrado!</p>";
        return false;
    }
}

// Interface para testar login
echo "<h1>Teste de Login</h1>";
echo "<form method='post'>";
echo "<label>Email: <input type='email' name='test_email' value='" . ($_POST['test_email'] ?? '') . "' required></label><br>";
echo "<label>Senha: <input type='password' name='test_senha' required></label><br>";
echo "<button type='submit' name='test_login'>Testar Login</button>";
echo "</form>";

// Processar o teste de login
if (isset($_POST['test_login'])) {
    $test_email = $_POST['test_email'];
    $test_senha = $_POST['test_senha'];
    
    testar_login($test_email, $test_senha);
}

// Listar todos os usuários para referência
echo "<h2>Usuários cadastrados:</h2>";
$users_query = "SELECT id, nome, email, tipo, LEFT(senha, 10) as senha_parcial, LENGTH(senha) as tamanho_senha FROM usuarios";
$users_result = mysqli_query($conn, $users_query);

if ($users_result && mysqli_num_rows($users_result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Início do Hash</th><th>Tamanho Hash</th></tr>";
    
    while ($user = mysqli_fetch_assoc($users_result)) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['nome']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['tipo']}</td>";
        echo "<td>{$user['senha_parcial']}...</td>";
        echo "<td>{$user['tamanho_senha']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Nenhum usuário encontrado.</p>";
}

// Criar um formulário para redefinir a senha de um usuário
echo "<h2>Redefinir senha:</h2>";
echo "<form method='post'>";
echo "<label>Email: <input type='email' name='reset_email' required></label><br>";
echo "<label>Nova senha: <input type='password' name='reset_senha' required></label><br>";
echo "<button type='submit' name='reset_password'>Redefinir Senha</button>";
echo "</form>";

// Processar redefinição de senha
if (isset($_POST['reset_password'])) {
    $reset_email = $_POST['reset_email'];
    $reset_senha = $_POST['reset_senha'];
    
    // Verificar se o usuário existe
    $check_user = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($check_user, "s", $reset_email);
    mysqli_stmt_execute($check_user);
    mysqli_stmt_store_result($check_user);
    
    if (mysqli_stmt_num_rows($check_user) === 1) {
        // Gerar hash da nova senha
        $senha_hash = password_hash($reset_senha, PASSWORD_DEFAULT);
        
        // Atualizar senha
        $update_stmt = mysqli_prepare($conn, "UPDATE usuarios SET senha = ? WHERE email = ?");
        mysqli_stmt_bind_param($update_stmt, "ss", $senha_hash, $reset_email);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo "<p style='color:green;'>✓ Senha redefinida com sucesso para {$reset_email}</p>";
        } else {
            echo "<p style='color:red;'>✗ Erro ao redefinir senha: " . mysqli_error($conn) . "</p>";
        }
        
        mysqli_stmt_close($update_stmt);
    } else {
        echo "<p style='color:red;'>✗ Usuário não encontrado: {$reset_email}</p>";
    }
    
    mysqli_stmt_close($check_user);
}

// Fechar conexão
mysqli_close($conn);
?>
