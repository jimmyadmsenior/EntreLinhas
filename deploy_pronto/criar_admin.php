<?php
// Script para criar um usuário administrador
require_once 'backend/config.php';

// Dados do administrador
$nome = "Administrador";
$email = "jimmycastilho555@gmail.com"; // Este é o email que aparece na sua configuração
$senha = "admin123"; // Você deve alterar esta senha depois
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Verificar se o email já existe
$check_sql = "SELECT * FROM usuarios WHERE email = '$email'";
$result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($result) > 0) {
    echo "Administrador já existe no sistema!";
} else {
    // Inserir o usuário administrador
    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES ('$nome', '$email', '$senha_hash')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Administrador criado com sucesso!<br>";
        echo "Email: $email<br>";
        echo "Senha: $senha<br>";
        echo "Lembre-se de alterar esta senha após o primeiro login.";
    } else {
        echo "Erro ao criar administrador: " . mysqli_error($conn);
    }
}
?>
