<?php
// Script para redefinir a senha do administrador

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Mostrar erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Redefinição de Senha do Administrador</h1>";

// Verificar conexão
if ($conn) {
    echo "<p style='color:green;'>✅ Conexão com o banco de dados OK!</p>";
    
    // Dados do administrador
    $admin_email = 'jimmycastilho555@gmail.com';
    $senha_plain = 'Admin@123';
    
    // Criar novo hash de senha
    $novo_hash = password_hash($senha_plain, PASSWORD_DEFAULT);
    
    echo "<p>Senha a ser definida: <strong>Admin@123</strong></p>";
    echo "<p>Novo hash: $novo_hash</p>";
    
    // Atualizar a senha do administrador
    $sql = "UPDATE usuarios SET senha = ? WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular parâmetros
        mysqli_stmt_bind_param($stmt, "ss", $novo_hash, $admin_email);
        
        // Executar a consulta
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color:green;'>✅ Senha do administrador atualizada com sucesso!</p>";
        } else {
            echo "<p style='color:red;'>❌ Erro ao atualizar a senha: " . mysqli_error($conn) . "</p>";
        }
        
        // Fechar declaração
        mysqli_stmt_close($stmt);
    } else {
        echo "<p style='color:red;'>❌ Erro na preparação da consulta: " . mysqli_error($conn) . "</p>";
    }
    
    // Verificar se a senha foi realmente atualizada
    $result = mysqli_query($conn, "SELECT id, nome, email, senha FROM usuarios WHERE email = '$admin_email'");
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo "<h2>Informações do administrador após a atualização:</h2>";
        echo "<p>ID: " . $row['id'] . "</p>";
        echo "<p>Nome: " . $row['nome'] . "</p>";
        echo "<p>Email: " . $row['email'] . "</p>";
        echo "<p>Hash da senha: " . $row['senha'] . "</p>";
        
        // Verificar se a nova senha funciona
        if (password_verify($senha_plain, $row['senha'])) {
            echo "<p style='color:green;'>✅ Verificação da nova senha: CORRETA!</p>";
        } else {
            echo "<p style='color:red;'>❌ Verificação da nova senha: FALHOU!</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Usuário administrador não encontrado!</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Falha na conexão com o banco de dados!</p>";
}

echo "<p><a href='teste_login.php'>Voltar para a página de teste de login</a></p>";
?>
