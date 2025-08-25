<?php
// Script para testes de login diretamente na página
// Sem redirecionamentos ou carregamento de páginas extras
// Usado para depurar problemas de login

// Ativar relatório de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir arquivo de configuração
require_once "backend/config.php";

// Inicializar variáveis
$login_result = null;
$message = '';
$logs = [];
$user_data = [];
$test_email = '';
$test_password = '';
$hash_info = [];

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados
    $test_email = $_POST['email'] ?? '';
    $test_password = $_POST['password'] ?? '';
    
    $logs[] = "Iniciando teste de login para: {$test_email}";
    
    // Consultar usuário no banco de dados
    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $test_email);
        $logs[] = "Query preparada: SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = '{$test_email}'";
        
        if (mysqli_stmt_execute($stmt)) {
            $logs[] = "Query executada com sucesso";
            mysqli_stmt_store_result($stmt);
            $count = mysqli_stmt_num_rows($stmt);
            $logs[] = "Registros encontrados: {$count}";
            
            if ($count === 1) {
                mysqli_stmt_bind_result($stmt, $id, $nome, $email_db, $senha_hash, $tipo);
                mysqli_stmt_fetch($stmt);
                
                $logs[] = "Usuário encontrado: {$nome} (ID: {$id}, Tipo: {$tipo})";
                
                // Mostrar informações sobre o hash da senha
                $hash_info['length'] = strlen($senha_hash);
                $hash_info['prefix'] = substr($senha_hash, 0, 7);
                $hash_info['algo'] = password_get_info($senha_hash)['algo'];
                $logs[] = "Hash da senha: Prefixo={$hash_info['prefix']}..., Comprimento={$hash_info['length']}, Algoritmo={$hash_info['algo']}";
                
                // Verificar senha
                $password_ok = password_verify($test_password, $senha_hash);
                $logs[] = "Verificação de senha: " . ($password_ok ? "SUCESSO" : "FALHA");
                
                if ($password_ok) {
                    $login_result = true;
                    $message = "Login bem-sucedido!";
                    
                    $user_data = [
                        'id' => $id,
                        'nome' => $nome,
                        'email' => $email_db,
                        'tipo' => $tipo
                    ];
                    
                    // Testar criação de sessão
                    session_start();
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["nome"] = $nome;
                    $_SESSION["email"] = $email_db;
                    $_SESSION["tipo"] = $tipo;
                    $logs[] = "Sessão criada com sucesso";
                    
                    // Testar criação de cookies
                    setcookie("userLoggedIn", "true", time() + 86400, "/");
                    setcookie("userName", $nome, time() + 86400, "/");
                    setcookie("userEmail", $email_db, time() + 86400, "/");
                    setcookie("userType", $tipo, time() + 86400, "/");
                    setcookie("userId", $id, time() + 86400, "/");
                    $logs[] = "Cookies definidos com sucesso";
                    
                } else {
                    $login_result = false;
                    $message = "Senha incorreta";
                    
                    // Teste adicional para ver se a senha é armazenada como texto plano
                    if ($test_password === $senha_hash) {
                        $logs[] = "ALERTA: A senha parece estar armazenada como texto plano!";
                    }
                    
                    // Criar novo hash para comparação
                    $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
                    $logs[] = "Novo hash gerado para esta senha: " . substr($new_hash, 0, 13) . "...";
                    $logs[] = "Hash armazenado para comparação: " . substr($senha_hash, 0, 13) . "...";
                }
            } else {
                $login_result = false;
                $message = "Usuário não encontrado";
                $logs[] = "Nenhum usuário encontrado com o email: {$test_email}";
            }
        } else {
            $login_result = false;
            $message = "Erro ao executar consulta: " . mysqli_error($conn);
            $logs[] = "ERRO: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $login_result = false;
        $message = "Erro ao preparar consulta: " . mysqli_error($conn);
        $logs[] = "ERRO: " . mysqli_error($conn);
    }
    
    // Testar AJAX
    if (isset($_POST['ajax_test']) && $_POST['ajax_test'] === '1') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $login_result,
            'message' => $message,
            'logs' => $logs,
            'user_data' => $user_data
        ]);
        exit;
    }
}

// Função para listar todos os usuários
function list_all_users($conn) {
    $sql = "SELECT id, nome, email, tipo, LENGTH(senha) as senha_length FROM usuarios ORDER BY id";
    $result = mysqli_query($conn, $sql);
    
    $users = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    
    return $users;
}

$all_users = list_all_users($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Login Direto - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .card {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
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
        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 15px;
            background-color: #f2f2f2;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
        }
        .tab.active {
            background-color: #3498db;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        #ajax-status {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .hidden {
            display: none;
        }
        .code-block {
            background-color: #272822;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .request-block {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Teste de Login Direto - EntreLinhas</h1>
    
    <div class="nav">
        <div class="tab active" data-tab="login-test">Login Direto</div>
        <div class="tab" data-tab="ajax-test">Teste AJAX</div>
        <div class="tab" data-tab="users-list">Usuários</div>
        <div class="tab" data-tab="session-info">Sessão</div>
        <div class="tab" data-tab="fix-hash">Corrigir Hash</div>
    </div>
    
    <!-- Tab Login Direto -->
    <div id="login-test" class="tab-content active">
        <div class="card">
            <h2>Login Direto (PHP)</h2>
            <p>Esta opção testa o login diretamente sem AJAX, usando apenas PHP.</p>
            
            <form method="post" action="">
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($test_email); ?>" required>
                </div>
                <div>
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Testar Login</button>
            </form>
            
            <?php if ($login_result !== null): ?>
                <div class="<?php echo $login_result ? 'success' : 'error'; ?>" style="margin-top: 20px;">
                    <?php echo $message; ?>
                </div>
                
                <?php if ($login_result): ?>
                    <div class="info">
                        <h3>Dados do usuário:</h3>
                        <ul>
                            <li><strong>ID:</strong> <?php echo $user_data['id']; ?></li>
                            <li><strong>Nome:</strong> <?php echo htmlspecialchars($user_data['nome']); ?></li>
                            <li><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></li>
                            <li><strong>Tipo:</strong> <?php echo htmlspecialchars($user_data['tipo']); ?></li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($hash_info)): ?>
                    <div class="info">
                        <h3>Informações do Hash:</h3>
                        <ul>
                            <li><strong>Comprimento:</strong> <?php echo $hash_info['length']; ?> caracteres</li>
                            <li><strong>Prefixo:</strong> <?php echo htmlspecialchars($hash_info['prefix']); ?>...</li>
                            <li><strong>Algoritmo:</strong> <?php echo $hash_info['algo']; ?></li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <h3>Log de Execução:</h3>
                <pre><?php echo implode("\n", $logs); ?></pre>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tab AJAX -->
    <div id="ajax-test" class="tab-content">
        <div class="card">
            <h2>Teste de Login AJAX</h2>
            <p>Esta opção testa o login via AJAX, como acontece na página real de login.</p>
            
            <form id="ajax-form">
                <div>
                    <label for="ajax-email">Email:</label>
                    <input type="email" id="ajax-email" name="email" required>
                </div>
                <div>
                    <label for="ajax-password">Senha:</label>
                    <input type="password" id="ajax-password" name="password" required>
                </div>
                <button type="submit">Testar Login via AJAX</button>
            </form>
            
            <div id="ajax-status" class="hidden"></div>
            
            <div class="request-block hidden" id="ajax-debug">
                <h3>Detalhes da Requisição AJAX:</h3>
                <pre id="ajax-response"></pre>
            </div>
        </div>
        
        <div class="card">
            <h2>Teste de Login via backend/process_login.php</h2>
            <p>Esta opção testa o endpoint real usado pelo site.</p>
            
            <form id="real-ajax-form">
                <div>
                    <label for="real-ajax-email">Email:</label>
                    <input type="email" id="real-ajax-email" name="email" required>
                </div>
                <div>
                    <label for="real-ajax-password">Senha:</label>
                    <input type="password" id="real-ajax-password" name="password" required>
                </div>
                <button type="submit">Testar Login Real</button>
            </form>
            
            <div id="real-ajax-status" class="hidden"></div>
            
            <div class="request-block hidden" id="real-ajax-debug">
                <h3>Detalhes da Resposta:</h3>
                <pre id="real-ajax-response"></pre>
            </div>
        </div>
    </div>
    
    <!-- Tab Usuários -->
    <div id="users-list" class="tab-content">
        <div class="card">
            <h2>Usuários Cadastrados</h2>
            
            <?php if (!empty($all_users)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Tamanho Senha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['nome']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['tipo']); ?></td>
                                <td><?php echo $user['senha_length']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum usuário encontrado.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tab Sessão -->
    <div id="session-info" class="tab-content">
        <div class="card">
            <h2>Informações de Sessão</h2>
            
            <?php
            // Garantir que a sessão está iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            ?>
            
            <h3>Dados da Sessão:</h3>
            <pre><?php print_r($_SESSION); ?></pre>
            
            <h3>Cookies:</h3>
            <pre><?php print_r($_COOKIE); ?></pre>
            
            <button type="button" id="clear-session">Limpar Sessão</button>
        </div>
    </div>
    
    <!-- Tab Corrigir Hash -->
    <div id="fix-hash" class="tab-content">
        <div class="card">
            <h2>Corrigir Hash de Senha</h2>
            <p>Use esta ferramenta para redefinir a senha de um usuário com o hash correto.</p>
            
            <form id="hash-fix-form" method="post" action="reset_admin_senha.php">
                <div>
                    <label for="fix-email">Email:</label>
                    <input type="email" id="fix-email" name="email" required>
                </div>
                <div>
                    <label for="fix-password">Nova Senha:</label>
                    <input type="password" id="fix-password" name="senha" required>
                </div>
                <button type="submit">Corrigir Hash da Senha</button>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tabs
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remover classe active de todas as tabs e conteúdos
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Adicionar classe active à tab clicada e seu conteúdo
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // AJAX Form
        const ajaxForm = document.getElementById('ajax-form');
        if (ajaxForm) {
            ajaxForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('ajax_test', '1');
                
                // Mostrar status
                const statusEl = document.getElementById('ajax-status');
                statusEl.textContent = 'Enviando requisição...';
                statusEl.className = 'info';
                statusEl.style.display = 'block';
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Atualizar status
                    statusEl.textContent = data.message;
                    statusEl.className = data.success ? 'success' : 'error';
                    
                    // Mostrar resposta
                    const debugEl = document.getElementById('ajax-debug');
                    debugEl.classList.remove('hidden');
                    
                    const responseEl = document.getElementById('ajax-response');
                    responseEl.textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    statusEl.textContent = 'Erro ao enviar requisição: ' + error.message;
                    statusEl.className = 'error';
                });
            });
        }
        
        // Real AJAX Form
        const realAjaxForm = document.getElementById('real-ajax-form');
        if (realAjaxForm) {
            realAjaxForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.set('senha', document.getElementById('real-ajax-password').value);
                
                // Mostrar status
                const statusEl = document.getElementById('real-ajax-status');
                statusEl.textContent = 'Enviando requisição...';
                statusEl.className = 'info';
                statusEl.style.display = 'block';
                
                fetch('../backend/process_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Atualizar status
                    statusEl.textContent = data.message;
                    statusEl.className = data.success ? 'success' : 'error';
                    
                    // Mostrar resposta
                    const debugEl = document.getElementById('real-ajax-debug');
                    debugEl.classList.remove('hidden');
                    
                    const responseEl = document.getElementById('real-ajax-response');
                    responseEl.textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    statusEl.textContent = 'Erro ao enviar requisição: ' + error.message;
                    statusEl.className = 'error';
                    
                    // Mostrar o erro
                    const debugEl = document.getElementById('real-ajax-debug');
                    debugEl.classList.remove('hidden');
                    
                    const responseEl = document.getElementById('real-ajax-response');
                    responseEl.textContent = 'Erro: ' + error.message + '\n\nIsso geralmente indica um problema no script PHP do servidor.';
                });
            });
        }
        
        // Limpar sessão
        const clearSessionBtn = document.getElementById('clear-session');
        if (clearSessionBtn) {
            clearSessionBtn.addEventListener('click', function() {
                // Limpar cookies
                document.cookie.split(";").forEach(function(c) {
                    document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
                });
                
                // Recarregar a página para limpar a sessão PHP
                window.location.reload();
            });
        }
    });
    </script>
</body>
</html>
