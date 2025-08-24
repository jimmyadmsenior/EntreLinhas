<?php
// Este script força a atualização do menu do usuário
// Iniciar a sessão
session_start();

// Incluir arquivo de configuração
require_once "backend/config.php";

// Força a atualização do nome do usuário
$_SESSION["nome"] = "Jimmy Castilho";
$_SESSION["tipo"] = "admin";
$_SESSION["loggedin"] = true;
$_SESSION["id"] = 1;
$_SESSION["email"] = "jimmycastilho555@gmail.com";

// Limpar e redefinir os cookies
setcookie("userLoggedIn", "", time() - 3600, "/");
setcookie("userName", "", time() - 3600, "/");
setcookie("userEmail", "", time() - 3600, "/");
setcookie("userType", "", time() - 3600, "/");
setcookie("userId", "", time() - 3600, "/");
setcookie("php_auth", "", time() - 3600, "/");

// Definir novos cookies com o nome correto
setcookie("userLoggedIn", "true", time() + 86400, "/");
setcookie("userName", "Jimmy Castilho", time() + 86400, "/");
setcookie("userEmail", "jimmycastilho555@gmail.com", time() + 86400, "/");
setcookie("userType", "admin", time() + 86400, "/");
setcookie("userId", "1", time() + 86400, "/");
setcookie("php_auth", "true", time() + 86400, "/");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrigir Menu do Usuário - EntreLinhas</title>
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
            margin: 20px 0;
            border-radius: 4px;
            background-color: #d1e7dd;
            color: #0f5132;
            border-left: 5px solid #0f5132;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0069d9;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 5px solid #856404;
            padding: 15px;
            margin: 20px 0;
        }
        .code {
            background-color: #f8f9fa;
            border: 1px solid #eaeaea;
            border-radius: 3px;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Correção do Menu do Usuário</h1>
        
        <div class="message">
            <p><strong>Dados do usuário atualizados com sucesso!</strong></p>
            <p>O seu nome foi definido manualmente como "Jimmy Castilho".</p>
        </div>
        
        <div class="warning">
            <p><strong>Importante:</strong> Para que as alterações tenham efeito completo:</p>
            <ol>
                <li>Clique no botão abaixo para limpar o localStorage</li>
                <li>Volte para a página inicial</li>
                <li>Seu nome deve aparecer corretamente no menu</li>
            </ol>
        </div>
        
        <div class="session-details">
            <h3>Dados da sessão PHP:</h3>
            <div class="code">
                <?php print_r($_SESSION); ?>
            </div>
            
            <h3>Cookies definidos:</h3>
            <div class="code">
                userLoggedIn: true<br>
                userName: Jimmy Castilho<br>
                userEmail: jimmycastilho555@gmail.com<br>
                userType: admin<br>
                userId: 1<br>
            </div>
        </div>
        
        <div class="actions">
            <button id="clear-storage" class="btn">Limpar localStorage</button>
            <a href="index.html" class="btn">Voltar para o Início</a>
        </div>
    </div>
    
    <script>
        // Limpar o localStorage atual
        document.getElementById('clear-storage').addEventListener('click', function() {
            // Limpar itens específicos
            localStorage.removeItem('userLoggedIn');
            localStorage.removeItem('userName');
            localStorage.removeItem('userEmail');
            localStorage.removeItem('userType');
            localStorage.removeItem('userId');
            
            // Definir novos valores
            localStorage.setItem('userLoggedIn', 'true');
            localStorage.setItem('userName', 'Jimmy Castilho');
            localStorage.setItem('userEmail', 'jimmycastilho555@gmail.com');
            localStorage.setItem('userType', 'admin');
            localStorage.setItem('userId', '1');
            
            alert('localStorage limpo e redefinido com sucesso! Agora você pode voltar para a página inicial.');
        });
    </script>
</body>
</html>
