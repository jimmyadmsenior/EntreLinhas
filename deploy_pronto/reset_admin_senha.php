<?php
// Script para redefinir a senha de administrador de forma rápida
require_once 'backend/config.php';

// Verificar se o script está sendo executado em uma solicitação POST
$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Configurações para o usuário admin
    $admin_email = $_POST['email'] ?? 'jimmycastilho555@gmail.com';
    $admin_senha = $_POST['senha'] ?? 'admin123';
    
    // Criar hash da senha
    $hashed_password = password_hash($admin_senha, PASSWORD_DEFAULT);
    
    // Verificar se o usuário existe
    $check_sql = "SELECT id FROM usuarios WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $admin_email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if(mysqli_stmt_num_rows($check_stmt) > 0) {
        // Usuário existe, atualizar a senha
        $update_sql = "UPDATE usuarios SET senha = ? WHERE email = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $admin_email);
        
        if(mysqli_stmt_execute($update_stmt)) {
            $success = true;
            $message = "Senha de administrador redefinida com sucesso para o email: " . htmlspecialchars($admin_email);
        } else {
            $message = "Erro ao atualizar a senha: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($update_stmt);
    } else {
        // Usuário não existe, criar novo
        $admin_nome = "Jimmy Castilho";
        $admin_tipo = "admin";
        
        $insert_sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "ssss", $admin_nome, $admin_email, $hashed_password, $admin_tipo);
        
        if(mysqli_stmt_execute($insert_stmt)) {
            $success = true;
            $message = "Usuário administrador criado com sucesso com o email: " . htmlspecialchars($admin_email);
        } else {
            $message = "Erro ao criar usuário administrador: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($insert_stmt);
    }
    
    mysqli_stmt_close($check_stmt);
}

// Verificar usuários administradores existentes
$admins = [];
$admin_query = "SELECT id, nome, email FROM usuarios WHERE tipo = 'admin'";
$result = mysqli_query($conn, $admin_query);

if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $admins[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha Admin - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #2c3e50;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        form {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }
        .senha-info {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .button-secondary {
            background-color: #6c757d;
        }
        .button-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Redefinir Senha de Administrador</h1>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <?php if ($success): ?>
                <div class="alert-success">
                    <?php echo $message; ?>
                </div>
            <?php else: ?>
                <div class="alert-danger">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="senha-info">
            <p><strong>Importante:</strong> Após a redefinição, a senha será definida exatamente como inserida no campo abaixo. 
            A ferramenta criará um hash bcrypt correto da senha fornecida.</p>
        </div>
        
        <h2>Redefinir Senha</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email do Administrador:</label>
                <input type="email" id="email" name="email" value="jimmycastilho555@gmail.com" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Nova Senha:</label>
                <input type="password" id="senha" name="senha" value="admin123" required>
            </div>
            
            <button type="submit">Redefinir Senha</button>
        </form>
        
        <h2>Administradores Cadastrados</h2>
        <?php if (count($admins) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo $admin['id']; ?></td>
                            <td><?php echo htmlspecialchars($admin['nome']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum administrador encontrado.</p>
        <?php endif; ?>
        
        <div class="button-group">
            <a href="diagnostico_login.php"><button type="button">Voltar para Diagnóstico</button></a>
            <a href="index.php"><button type="button" class="button-secondary">Página Inicial</button></a>
        </div>
        
        <div class="footer">
            <p>EntreLinhas - Ferramenta de Manutenção</p>
        </div>
    </div>
</body>
</html>
