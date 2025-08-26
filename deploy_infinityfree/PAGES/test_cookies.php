<?php
// Este arquivo testa a funcionalidade de cookies
// Iniciar a sessão
session_start();

// Definir alguns cookies de teste
setcookie("test_cookie", "cookie_value", time() + 3600, "/");
setcookie("test_cookie2", "another_value", time() + 3600, "/");

// Verificar se os cookies existem
$cookie_exists = isset($_COOKIE["test_cookie"]);
$previous_visit = isset($_COOKIE["last_visit"]) ? $_COOKIE["last_visit"] : "Esta é sua primeira visita";

// Atualizar cookie de última visita
$current_time = date("d/m/Y H:i:s");
setcookie("last_visit", $current_time, time() + 86400 * 30, "/");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Cookies - EntreLinhas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .result {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
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
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .cookie-list {
            margin-top: 20px;
        }
        .cookie-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0069d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Teste de Cookies</h1>
        
        <?php if ($cookie_exists): ?>
            <div class="result success">
                <h2>Sucesso!</h2>
                <p>Os cookies estão funcionando corretamente no seu navegador.</p>
                <p><strong>Sua última visita foi em:</strong> <?php echo $previous_visit; ?></p>
            </div>
        <?php else: ?>
            <div class="result error">
                <h2>Erro!</h2>
                <p>Os cookies não estão funcionando no seu navegador. Isso pode impedir que o sistema de login funcione corretamente.</p>
                <p>Por favor, verifique se os cookies estão habilitados nas configurações do seu navegador.</p>
            </div>
        <?php endif; ?>
        
        <div class="info result">
            <h3>Como funcionam os cookies no nosso sistema de autenticação:</h3>
            <p>O sistema EntreLinhas usa cookies para manter você conectado entre as páginas PHP e HTML. Se os cookies não estiverem funcionando, você pode ter problemas para acessar páginas protegidas.</p>
        </div>
        
        <div class="cookie-list">
            <h3>Cookies atuais:</h3>
            <?php if (count($_COOKIE) > 0): ?>
                <?php foreach($_COOKIE as $name => $value): ?>
                <div class="cookie-item">
                    <strong><?php echo htmlspecialchars($name); ?>:</strong> 
                    <?php echo htmlspecialchars(substr($value, 0, 50) . (strlen($value) > 50 ? '...' : '')); ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum cookie encontrado.</p>
            <?php endif; ?>
        </div>
        
        <div class="actions">
            <a href="check_auth_status.php" class="btn">Verificar Status de Autenticação</a>
            <a href="index.html" class="btn">Voltar para Início</a>
            <button id="create-js-cookie" class="btn">Criar Cookie via JavaScript</button>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createCookieBtn = document.getElementById('create-js-cookie');
            
            createCookieBtn.addEventListener('click', function() {
                // Função para definir cookies via JavaScript
                function setCookie(name, value, days) {
                    let expires = "";
                    if (days) {
                        const date = new Date();
                        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                        expires = "; expires=" + date.toUTCString();
                    }
                    document.cookie = name + "=" + value + expires + "; path=/";
                }
                
                // Definir um cookie via JavaScript
                const now = new Date().toISOString();
                setCookie("js_test_cookie", "Criado via JavaScript em " + now, 1);
                
                // Recarregar a página para mostrar o novo cookie
                alert("Cookie criado via JavaScript! A página será recarregada.");
                location.reload();
            });
        });
    </script>
</body>
</html>
