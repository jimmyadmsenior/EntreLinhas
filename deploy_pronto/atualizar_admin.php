<?php
// Este arquivo força a atualização do nome do administrador na sessão

// Iniciar a sessão
session_start();

// Incluir arquivo de configuração
require_once "backend/config.php";

// ID do administrador
$admin_id = 1; // ID do Jimmy Castilho

// Buscar dados do administrador no banco de dados
$sql = "SELECT id, nome, email, tipo FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Atualizar dados da sessão
    $_SESSION["loggedin"] = true;
    $_SESSION["id"] = $row["id"];
    $_SESSION["nome"] = $row["nome"];
    $_SESSION["email"] = $row["email"];
    $_SESSION["tipo"] = $row["tipo"];
    
    // Atualizar cookies
    setcookie("userLoggedIn", "true", time() + 86400, "/");
    setcookie("userName", $row["nome"], time() + 86400, "/");
    setcookie("userEmail", $row["email"], time() + 86400, "/");
    setcookie("userType", $row["tipo"], time() + 86400, "/");
    setcookie("userId", $row["id"], time() + 86400, "/");
    
    $message = "Sessão de administrador atualizada com sucesso para: " . $row["nome"];
    $success = true;
} else {
    $message = "Erro: Administrador não encontrado no banco de dados";
    $success = false;
}

// Fechar conexão
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Sessão de Admin - EntreLinhas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Atualização de Sessão de Admin</h1>
        
        <div class="message <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
        
        <div class="session-details">
            <h3>Dados atuais da sessão:</h3>
            <pre><?php print_r($_SESSION); ?></pre>
            
            <h3>Cookies atuais:</h3>
            <pre><?php print_r($_COOKIE); ?></pre>
        </div>
        
        <div class="actions">
            <a href="index.html" class="btn">Voltar para o Início</a>
            <a href="verificar_usuario.php" class="btn">Verificar Usuário</a>
            <a href="PAGES/logout.php" class="btn">Fazer Logout</a>
        </div>
    </div>
    
    <script>
        // Atualizar o localStorage com os dados da sessão
        localStorage.setItem('userLoggedIn', 'true');
        localStorage.setItem('userName', '<?php echo $_SESSION["nome"]; ?>');
        localStorage.setItem('userEmail', '<?php echo $_SESSION["email"]; ?>');
        localStorage.setItem('userType', '<?php echo $_SESSION["tipo"]; ?>');
        localStorage.setItem('userId', '<?php echo $_SESSION["id"]; ?>');
        
        console.log('localStorage atualizado com os dados da sessão');
    </script>
</body>
</html>
