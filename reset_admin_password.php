<?php
// Script para resetar a senha do admin
require_once "backend/config.php";

// ID do usuário admin que queremos resetar a senha (normalmente 1)
$admin_id = 1;

// Nova senha (sem hash)
$nova_senha = "admin123";

// Hash da senha
$hashed_password = password_hash($nova_senha, PASSWORD_DEFAULT);

// Atualizar a senha no banco de dados
$sql = "UPDATE usuarios SET senha = ? WHERE id = ? AND tipo = 'admin'";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $admin_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Senha do administrador (ID: $admin_id) foi resetada com sucesso para: $nova_senha";
    } else {
        echo "Erro ao resetar senha: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Erro na preparação da consulta: " . mysqli_error($conn);
}

// Listar admins para verificação
echo "<h2>Administradores no sistema:</h2>";
$query = "SELECT id, nome, email, tipo FROM usuarios WHERE tipo = 'admin'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
    
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["nome"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["tipo"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Nenhum administrador encontrado.";
}

// Fechar conexão
mysqli_close($conn);
?>
