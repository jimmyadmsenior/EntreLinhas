<?php
// Script para verificar e corrigir problemas com o login
require_once 'backend/config.php';

echo "<h1>Verificação de Problemas de Login</h1>";

// 1. Verificar se a tabela possui a coluna 'ativo'
$check_ativo = mysqli_query($conn, "SHOW COLUMNS FROM usuarios LIKE 'ativo'");
$ativo_exists = mysqli_num_rows($check_ativo) > 0;

echo "<h2>1. Verificação da coluna 'ativo':</h2>";
if ($ativo_exists) {
    echo "<p style='color:green;'>A coluna 'ativo' existe na tabela usuarios.</p>";
} else {
    echo "<p style='color:red;'>A coluna 'ativo' NÃO existe na tabela usuarios.</p>";
    
    // Adicionar a coluna se não existir
    $add_column = mysqli_query($conn, "ALTER TABLE usuarios ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1");
    if ($add_column) {
        echo "<p style='color:green;'>Coluna 'ativo' adicionada com sucesso!</p>";
    } else {
        echo "<p style='color:red;'>Erro ao adicionar coluna 'ativo': " . mysqli_error($conn) . "</p>";
    }
}

// 2. Listar usuários com o tipo de hash de senha
echo "<h2>2. Lista de usuários e informações de senhas:</h2>";
$users = mysqli_query($conn, "SELECT id, nome, email, senha, tipo FROM usuarios");

if ($users && mysqli_num_rows($users) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Tipo de Hash</th><th>Tamanho da Senha Hash</th></tr>";
    
    while ($user = mysqli_fetch_assoc($users)) {
        $hash_type = "Desconhecido";
        $hash_length = strlen($user['senha']);
        
        if (substr($user['senha'], 0, 4) === '$2y$') {
            $hash_type = "bcrypt (PHP password_hash)";
        } elseif (substr($user['senha'], 0, 4) === '$2a$') {
            $hash_type = "bcrypt (variante)";
        } elseif (strlen($user['senha']) === 60 && substr($user['senha'], 0, 1) === '$') {
            $hash_type = "bcrypt (outro formato)";
        } elseif (strlen($user['senha']) === 32) {
            $hash_type = "MD5 (não seguro)";
        } elseif (strlen($user['senha']) === 40) {
            $hash_type = "SHA-1 (não seguro)";
        }
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['nome']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['tipo']}</td>";
        echo "<td>{$hash_type}</td>";
        echo "<td>{$hash_length}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Nenhum usuário encontrado ou erro na consulta: " . mysqli_error($conn) . "</p>";
}

// 3. Ferramenta para redefinir senha
echo "<h2>3. Redefinir senha de um usuário:</h2>";
echo "<form method='post' action=''>";
echo "<label>Email do usuário: <input type='email' name='email' required></label><br>";
echo "<label>Nova senha: <input type='password' name='senha' required></label><br>";
echo "<input type='submit' name='reset_password' value='Redefinir Senha'>";
echo "</form>";

// Processar redefinição de senha
if (isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    $nova_senha = $_POST['senha'];
    
    // Verificar se o usuário existe
    $check_user = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($check_user, "s", $email);
    mysqli_stmt_execute($check_user);
    mysqli_stmt_store_result($check_user);
    
    if (mysqli_stmt_num_rows($check_user) === 1) {
        // Gerar hash da nova senha
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        // Atualizar senha
        $update_pwd = mysqli_prepare($conn, "UPDATE usuarios SET senha = ? WHERE email = ?");
        mysqli_stmt_bind_param($update_pwd, "ss", $senha_hash, $email);
        
        if (mysqli_stmt_execute($update_pwd)) {
            echo "<p style='color:green;'>Senha atualizada com sucesso para o usuário {$email}!</p>";
        } else {
            echo "<p style='color:red;'>Erro ao atualizar senha: " . mysqli_error($conn) . "</p>";
        }
        
        mysqli_stmt_close($update_pwd);
    } else {
        echo "<p style='color:red;'>Usuário com o email {$email} não encontrado.</p>";
    }
    
    mysqli_stmt_close($check_user);
}

// 4. Adicionar um novo usuário
echo "<h2>4. Adicionar novo usuário:</h2>";
echo "<form method='post' action=''>";
echo "<label>Nome: <input type='text' name='nome' required></label><br>";
echo "<label>Email: <input type='email' name='new_email' required></label><br>";
echo "<label>Senha: <input type='password' name='new_senha' required></label><br>";
echo "<label>Tipo: <select name='tipo'>";
echo "<option value='usuario'>Usuário</option>";
echo "<option value='admin'>Administrador</option>";
echo "</select></label><br>";
echo "<input type='submit' name='add_user' value='Adicionar Usuário'>";
echo "</form>";

// Processar adição de usuário
if (isset($_POST['add_user'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['new_email']);
    $senha = $_POST['new_senha'];
    $tipo = $_POST['tipo'];
    
    // Verificar se o email já existe
    $check_email = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($check_email, "s", $email);
    mysqli_stmt_execute($check_email);
    mysqli_stmt_store_result($check_email);
    
    if (mysqli_stmt_num_rows($check_email) > 0) {
        echo "<p style='color:red;'>Este email já está em uso.</p>";
    } else {
        // Gerar hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Inserir novo usuário
        $insert_user = mysqli_prepare($conn, "INSERT INTO usuarios (nome, email, senha, tipo, data_cadastro) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($insert_user, "ssss", $nome, $email, $senha_hash, $tipo);
        
        if (mysqli_stmt_execute($insert_user)) {
            echo "<p style='color:green;'>Novo usuário {$nome} ({$email}) adicionado com sucesso!</p>";
        } else {
            echo "<p style='color:red;'>Erro ao adicionar usuário: " . mysqli_error($conn) . "</p>";
        }
        
        mysqli_stmt_close($insert_user);
    }
    
    mysqli_stmt_close($check_email);
}

// Fechar a conexão
mysqli_close($conn);
?>
