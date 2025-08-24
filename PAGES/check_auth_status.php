<?php
// Este arquivo verifica e mostra o status de autenticação atual
// tanto no PHP (sessão) quanto no JavaScript (localStorage/cookies)

// Iniciar a sessão
session_start();

// Verificar a sessão PHP
$php_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$php_user_id = $_SESSION["id"] ?? "Não definido";
$php_user_name = $_SESSION["nome"] ?? "Não definido";
$php_user_email = $_SESSION["email"] ?? "Não definido";
$php_user_type = $_SESSION["tipo"] ?? "Não definido";

// Verificar os cookies
$cookie_logged_in = isset($_COOKIE["userLoggedIn"]) ? $_COOKIE["userLoggedIn"] : "Não definido";
$cookie_user_name = $_COOKIE["userName"] ?? "Não definido";
$cookie_user_email = $_COOKIE["userEmail"] ?? "Não definido";
$cookie_user_type = $_COOKIE["userType"] ?? "Não definido";
$cookie_user_id = $_COOKIE["userId"] ?? "Não definido";
$cookie_php_auth = $_COOKIE["php_auth"] ?? "Não definido";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status de Autenticação - EntreLinhas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-status {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .auth-item {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .auth-label {
            width: 180px;
            font-weight: bold;
        }
        .auth-value {
            flex: 1;
        }
        .status-section {
            margin-bottom: 30px;
        }
        .status-title {
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 3px;
            margin-bottom: 15px;
        }
        .actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
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
    <div class="auth-status">
        <h1>Status de Autenticação</h1>
        
        <div class="status-section">
            <h2 class="status-title">Sessão PHP</h2>
            <div class="auth-item">
                <div class="auth-label">Logado:</div>
                <div class="auth-value"><?php echo $php_logged_in ? 'Sim' : 'Não'; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">ID:</div>
                <div class="auth-value"><?php echo $php_user_id; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">Nome:</div>
                <div class="auth-value"><?php echo $php_user_name; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">Email:</div>
                <div class="auth-value"><?php echo $php_user_email; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">Tipo:</div>
                <div class="auth-value"><?php echo $php_user_type; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">Todos os dados da sessão:</div>
                <div class="auth-value">
                    <pre><?php print_r($_SESSION); ?></pre>
                </div>
            </div>
        </div>
        
        <div class="status-section">
            <h2 class="status-title">Cookies do Navegador</h2>
            <div class="auth-item">
                <div class="auth-label">Logado:</div>
                <div class="auth-value"><?php echo $cookie_logged_in; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">Nome:</div>
                <div class="auth-value"><?php echo $cookie_user_name; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">Email:</div>
                <div class="auth-value"><?php echo $cookie_user_email; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">Tipo:</div>
                <div class="auth-value"><?php echo $cookie_user_type; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">ID:</div>
                <div class="auth-value"><?php echo $cookie_user_id; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">PHP Auth:</div>
                <div class="auth-value"><?php echo $cookie_php_auth; ?></div>
            </div>
            <div class="auth-item">
                <div class="auth-label">Todos os cookies:</div>
                <div class="auth-value">
                    <pre><?php print_r($_COOKIE); ?></pre>
                </div>
            </div>
        </div>
        
        <div class="status-section">
            <h2 class="status-title">localStorage (JavaScript)</h2>
            <div id="localStorage-data">
                <p>Carregando dados do localStorage...</p>
            </div>
        </div>
        
        <div class="actions">
            <a href="auth-bridge.php" class="btn">Executar Auth Bridge</a>
            <a href="logout.php" class="btn">Fazer Logout</a>
            <a href="index.html" class="btn">Ir para Início</a>
        </div>
    </div>

    <script>
        // Mostrar os dados do localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const localStorageData = document.getElementById('localStorage-data');
            let html = '';
            
            // Verificar os valores relevantes no localStorage
            const userLoggedIn = localStorage.getItem('userLoggedIn') || 'Não definido';
            const userName = localStorage.getItem('userName') || 'Não definido';
            const userEmail = localStorage.getItem('userEmail') || 'Não definido';
            const userType = localStorage.getItem('userType') || 'Não definido';
            const userId = localStorage.getItem('userId') || 'Não definido';
            
            html += `
                <div class="auth-item">
                    <div class="auth-label">Logado:</div>
                    <div class="auth-value">${userLoggedIn}</div>
                </div>
                <div class="auth-item">
                    <div class="auth-label">Nome:</div>
                    <div class="auth-value">${userName}</div>
                </div>
                <div class="auth-item">
                    <div class="auth-label">Email:</div>
                    <div class="auth-value">${userEmail}</div>
                </div>
                <div class="auth-item">
                    <div class="auth-label">Tipo:</div>
                    <div class="auth-value">${userType}</div>
                </div>
                <div class="auth-item">
                    <div class="auth-label">ID:</div>
                    <div class="auth-value">${userId}</div>
                </div>
                <div class="auth-item">
                    <div class="auth-label">Todos os itens:</div>
                    <div class="auth-value">
                        <pre>${JSON.stringify(localStorage, null, 2)}</pre>
                    </div>
                </div>
            `;
            
            localStorageData.innerHTML = html;
        });
    </script>
</body>
</html>
