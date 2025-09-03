<?php
// Script para verificar dados do usuário no banco
require_once "backend/config.php";

// Verificar se está logado
session_start();
$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Função para exibir dados do usuário
function display_user_info($conn, $user_id) {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if($row = mysqli_fetch_assoc($result)) {
                echo "<h3>Dados do Banco de Dados:</h3>";
                echo "<ul>";
                echo "<li><strong>ID:</strong> " . $row["id"] . "</li>";
                echo "<li><strong>Nome:</strong> " . htmlspecialchars($row["nome"]) . "</li>";
                echo "<li><strong>Email:</strong> " . htmlspecialchars($row["email"]) . "</li>";
                echo "<li><strong>Tipo:</strong> " . htmlspecialchars($row["tipo"]) . "</li>";
                echo "<li><strong>Status:</strong> " . htmlspecialchars($row["status"]) . "</li>";
                echo "</ul>";
            } else {
                echo "<p>Usuário não encontrado no banco de dados.</p>";
            }
        } else {
            echo "<p>Erro ao executar a consulta: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>Erro ao preparar a consulta: " . mysqli_error($conn) . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Dados do Usuário - EntreLinhas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .data-section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        h2 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
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
        .code {
            font-family: monospace;
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .warning {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verificação de Dados do Usuário</h2>
        
        <?php if($is_logged_in): ?>
            <div class="data-section">
                <h3>Dados da Sessão PHP:</h3>
                <ul>
                    <li><strong>ID:</strong> <?php echo $_SESSION["id"]; ?></li>
                    <li><strong>Nome:</strong> <?php echo htmlspecialchars($_SESSION["nome"]); ?></li>
                    <li><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION["email"]); ?></li>
                    <li><strong>Tipo:</strong> <?php echo htmlspecialchars($_SESSION["tipo"]); ?></li>
                </ul>
                
                <div class="code">
                    $_SESSION = <?php print_r($_SESSION); ?>
                </div>
            </div>
            
            <div class="data-section">
                <?php display_user_info($conn, $_SESSION["id"]); ?>
            </div>
        <?php else: ?>
            <div class="warning">
                <p><strong>Você não está logado!</strong> Para ver os dados do usuário, faça login primeiro.</p>
            </div>
        <?php endif; ?>
        
        <div class="data-section">
            <h3>Cookies:</h3>
            <?php if(count($_COOKIE) > 0): ?>
                <ul>
                    <?php foreach($_COOKIE as $name => $value): ?>
                        <li><strong><?php echo htmlspecialchars($name); ?>:</strong> <?php echo htmlspecialchars(substr($value, 0, 100)); ?><?php echo strlen($value) > 100 ? '...' : ''; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nenhum cookie encontrado.</p>
            <?php endif; ?>
        </div>
        
        <div class="data-section">
            <h3>localStorage (JavaScript):</h3>
            <div id="localStorage-info">Carregando dados do localStorage...</div>
        </div>
        
        <div class="actions">
            <a href="PAGES/login.php" class="btn">Ir para Login</a>
            <a href="index.html" class="btn">Voltar para Início</a>
            <a href="PAGES/auth-bridge.php?to=index.php" class="btn">Executar Auth Bridge</a>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const lsInfo = document.getElementById('localStorage-info');
            let html = '<ul>';
            
            // Verificar dados relevantes no localStorage
            const userItems = ['userLoggedIn', 'userName', 'userEmail', 'userType', 'userId'];
            
            userItems.forEach(item => {
                const value = localStorage.getItem(item) || 'Não definido';
                html += `<li><strong>${item}:</strong> ${value}</li>`;
            });
            
            html += '</ul><div class="code">localStorage = ' + JSON.stringify(localStorage, null, 2) + '</div>';
            lsInfo.innerHTML = html;
        });
    </script>
</body>
</html>
