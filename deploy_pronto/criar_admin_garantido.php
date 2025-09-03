<?php
// Script para garantir que temos um administrador no sistema
require_once "backend/config.php";

// Dados do administrador
$admin_email = "admin@example.com";
$admin_nome = "Administrador";
$admin_senha = "admin123"; // Será hasheada abaixo
$admin_tipo = "admin";

// Verificar se o email já existe
$sql_check = "SELECT id FROM usuarios WHERE email = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $admin_email);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

if (mysqli_stmt_num_rows($stmt_check) > 0) {
    // O administrador já existe, atualizar a senha
    mysqli_stmt_bind_result($stmt_check, $admin_id);
    mysqli_stmt_fetch($stmt_check);
    
    $hashed_password = password_hash($admin_senha, PASSWORD_DEFAULT);
    
    $sql_update = "UPDATE usuarios SET nome = ?, senha = ?, tipo = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "sssi", $admin_nome, $hashed_password, $admin_tipo, $admin_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        echo "Administrador atualizado com sucesso.<br>";
        echo "Email: $admin_email<br>";
        echo "Senha: $admin_senha<br>";
        echo "ID: $admin_id<br>";
    } else {
        echo "Erro ao atualizar administrador: " . mysqli_error($conn) . "<br>";
    }
    
    mysqli_stmt_close($stmt_update);
} else {
    // Criar um novo administrador
    $hashed_password = password_hash($admin_senha, PASSWORD_DEFAULT);
    
    $sql_insert = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "ssss", $admin_nome, $admin_email, $hashed_password, $admin_tipo);
    
    if (mysqli_stmt_execute($stmt_insert)) {
        $admin_id = mysqli_insert_id($conn);
        echo "Administrador criado com sucesso.<br>";
        echo "Email: $admin_email<br>";
        echo "Senha: $admin_senha<br>";
        echo "ID: $admin_id<br>";
    } else {
        echo "Erro ao criar administrador: " . mysqli_error($conn) . "<br>";
    }
    
    mysqli_stmt_close($stmt_insert);
}

mysqli_stmt_close($stmt_check);

// Mostrar todos os administradores
echo "<h2>Administradores no sistema:</h2>";
$sql_list = "SELECT id, nome, email FROM usuarios WHERE tipo = 'admin'";
$result = mysqli_query($conn, $sql_list);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nome'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Nenhum administrador encontrado.";
}

// Link para fazer login com o admin
echo "<br><br><a href='PAGES/login.php'>Ir para a página de login</a>";
echo "<br><a href='diagnostico_sessao.php'>Ir para o diagnóstico de sessão</a>";

// Fechar conexão
mysqli_close($conn);
?>
