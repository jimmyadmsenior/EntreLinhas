<?php
// Ferramenta para diagnóstico completo de problemas de login
// Isso irá testar a conexão, estrutura do banco de dados e autenticação

// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once "backend/config.php";

// Função para limpar entradas
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Função para verificar a conexão com o banco de dados
function check_db_connection($conn) {
    if ($conn) {
        echo "<div class='success'>✓ Conexão com o banco de dados estabelecida.</div>";
        return true;
    } else {
        echo "<div class='error'>✗ Falha na conexão com o banco de dados: " . mysqli_connect_error() . "</div>";
        return false;
    }
}

// Função para verificar a estrutura da tabela de usuários
function check_users_table($conn) {
    $sql = "SHOW TABLES LIKE 'usuarios'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 0) {
        echo "<div class='error'>✗ A tabela 'usuarios' não existe!</div>";
        return false;
    }
    
    echo "<div class='success'>✓ A tabela 'usuarios' existe.</div>";
    
    $sql = "DESCRIBE usuarios";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo "<div class='error'>✗ Erro ao verificar a estrutura da tabela: " . mysqli_error($conn) . "</div>";
        return false;
    }
    
    $expected_columns = ['id', 'nome', 'email', 'senha', 'tipo'];
    $missing_columns = [];
    $found_columns = [];
    
    echo "<h3>Estrutura da tabela 'usuarios':</h3>";
    echo "<table class='data-table'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        
        $found_columns[] = $row['Field'];
    }
    echo "</table>";
    
    // Verificar colunas obrigatórias
    foreach ($expected_columns as $column) {
        if (!in_array($column, $found_columns)) {
            $missing_columns[] = $column;
        }
    }
    
    if (count($missing_columns) > 0) {
        echo "<div class='error'>✗ Colunas obrigatórias faltando: " . implode(', ', $missing_columns) . "</div>";
        return false;
    }
    
    echo "<div class='success'>✓ Todas as colunas obrigatórias estão presentes.</div>";
    return true;
}

// Função para listar todos os usuários
function list_users($conn) {
    $sql = "SELECT id, nome, email, tipo, LENGTH(senha) as tamanho_senha FROM usuarios";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo "<div class='error'>✗ Erro ao consultar usuários: " . mysqli_error($conn) . "</div>";
        return false;
    }
    
    if (mysqli_num_rows($result) == 0) {
        echo "<div class='warning'>⚠️ Nenhum usuário encontrado no banco de dados.</div>";
        return false;
    }
    
    echo "<h3>Usuários cadastrados:</h3>";
    echo "<table class='data-table'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Tamanho da Senha (Hash)</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
        echo "<td>" . $row['tamanho_senha'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    return true;
}

// Função para testar autenticação
function test_authentication($conn, $email, $senha) {
    echo "<h3>Testando autenticação para: " . htmlspecialchars($email) . "</h3>";
    
    // Verificar se o email existe
    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) == 0) {
            echo "<div class='error'>✗ Usuário não encontrado com o email: " . htmlspecialchars($email) . "</div>";
            return false;
        }
        
        mysqli_stmt_bind_result($stmt, $id, $nome, $email_db, $hashed_password, $tipo);
        mysqli_stmt_fetch($stmt);
        
        echo "<div class='info'>Usuario encontrado: " . htmlspecialchars($nome) . " (Tipo: " . htmlspecialchars($tipo) . ")</div>";
        
        // Testar verificação de senha
        if (password_verify($senha, $hashed_password)) {
            echo "<div class='success'>✓ Senha correta!</div>";
            
            // Mostrar informações da sessão e cookies que seriam criados
            echo "<h4>Dados de sessão que seriam criados:</h4>";
            echo "<pre>";
            echo "SESSION['loggedin'] = true\n";
            echo "SESSION['id'] = {$id}\n";
            echo "SESSION['nome'] = '{$nome}'\n";
            echo "SESSION['email'] = '{$email_db}'\n";
            echo "SESSION['tipo'] = '{$tipo}'\n";
            echo "</pre>";
            
            echo "<h4>Cookies que seriam definidos:</h4>";
            echo "<pre>";
            echo "Cookie 'userLoggedIn' = 'true'\n";
            echo "Cookie 'userName' = '{$nome}'\n";
            echo "Cookie 'userEmail' = '{$email_db}'\n";
            echo "Cookie 'userType' = '{$tipo}'\n";
            echo "Cookie 'userId' = '{$id}'\n";
            echo "</pre>";
            
            mysqli_stmt_close($stmt);
            return true;
        } else {
            echo "<div class='error'>✗ Senha incorreta!</div>";
            echo "<p>Detalhes técnicos do hash:</p>";
            echo "<ul>";
            echo "<li>Comprimento do hash armazenado: " . strlen($hashed_password) . "</li>";
            echo "<li>Primeiros 20 caracteres do hash: " . htmlspecialchars(substr($hashed_password, 0, 20)) . "...</li>";
            echo "</ul>";
            
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        echo "<div class='error'>✗ Erro na preparação da consulta: " . mysqli_error($conn) . "</div>";
        return false;
    }
}

// Função para criar um novo usuário com senha hash correta
function create_test_user($conn, $nome, $email, $senha, $tipo) {
    // Verificar se o email já existe
    $check_sql = "SELECT id FROM usuarios WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        echo "<div class='warning'>⚠️ Um usuário com o email " . htmlspecialchars($email) . " já existe.</div>";
        mysqli_stmt_close($check_stmt);
        return false;
    }
    
    mysqli_stmt_close($check_stmt);
    
    // Criar hash da senha
    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);
    
    // Inserir novo usuário
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssss", $nome, $email, $hashed_password, $tipo);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='success'>✓ Usuário de teste criado com sucesso: " . htmlspecialchars($nome) . " (" . htmlspecialchars($email) . ")</div>";
            mysqli_stmt_close($stmt);
            return true;
        } else {
            echo "<div class='error'>✗ Erro ao criar usuário: " . mysqli_error($conn) . "</div>";
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        echo "<div class='error'>✗ Erro na preparação da consulta: " . mysqli_error($conn) . "</div>";
        return false;
    }
}

// Função para redefinir senha
function reset_password($conn, $email, $nova_senha) {
    // Verificar se o usuário existe
    $check_sql = "SELECT id FROM usuarios WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) == 0) {
        echo "<div class='error'>✗ Nenhum usuário encontrado com o email: " . htmlspecialchars($email) . "</div>";
        mysqli_stmt_close($check_stmt);
        return false;
    }
    
    mysqli_stmt_close($check_stmt);
    
    // Criar hash da nova senha
    $hashed_password = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    // Atualizar a senha
    $sql = "UPDATE usuarios SET senha = ? WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='success'>✓ Senha redefinida com sucesso para: " . htmlspecialchars($email) . "</div>";
            mysqli_stmt_close($stmt);
            return true;
        } else {
            echo "<div class='error'>✗ Erro ao redefinir senha: " . mysqli_error($conn) . "</div>";
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        echo "<div class='error'>✗ Erro na preparação da consulta: " . mysqli_error($conn) . "</div>";
        return false;
    }
}

// Verificar cookies e sessão
function check_session_cookies() {
    echo "<h3>Status da sessão atual:</h3>";
    
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        echo "<div class='success'>✓ Sessão ativa</div>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    } else {
        echo "<div class='info'>ℹ️ Nenhuma sessão ativa</div>";
    }
    
    echo "<h3>Cookies atuais:</h3>";
    
    if (count($_COOKIE) > 0) {
        echo "<pre>";
        foreach ($_COOKIE as $name => $value) {
            echo htmlspecialchars($name) . " = " . htmlspecialchars($value) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<div class='info'>ℹ️ Nenhum cookie definido</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Login - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #2c3e50;
            margin-top: 20px;
        }
        h3 {
            color: #3498db;
            margin-top: 15px;
        }
        .section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        .success {
            padding: 10px;
            margin: 10px 0;
            background-color: #d4edda;
            border-left: 5px solid #28a745;
            color: #155724;
        }
        .error {
            padding: 10px;
            margin: 10px 0;
            background-color: #f8d7da;
            border-left: 5px solid #dc3545;
            color: #721c24;
        }
        .warning {
            padding: 10px;
            margin: 10px 0;
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            color: #856404;
        }
        .info {
            padding: 10px;
            margin: 10px 0;
            background-color: #d1ecf1;
            border-left: 5px solid #17a2b8;
            color: #0c5460;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .data-table th, .data-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
        }
        form {
            margin: 15px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="password"] {
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
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de Login - EntreLinhas</h1>
        
        <div class="section">
            <h2>1. Verificação do Banco de Dados</h2>
            <?php check_db_connection($conn); ?>
            <?php check_users_table($conn); ?>
        </div>
        
        <div class="section">
            <h2>2. Usuários Cadastrados</h2>
            <?php list_users($conn); ?>
        </div>
        
        <div class="section">
            <h2>3. Teste de Login</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label for="test_email">Email:</label>
                    <input type="email" id="test_email" name="test_email" required>
                </div>
                <div class="form-group">
                    <label for="test_senha">Senha:</label>
                    <input type="password" id="test_senha" name="test_senha" required>
                </div>
                <button type="submit" name="test_auth">Testar Login</button>
            </form>
            
            <?php
            if (isset($_POST['test_auth'])) {
                $email = clean_input($_POST['test_email']);
                $senha = $_POST['test_senha']; // Não limpar a senha, pois pode conter caracteres especiais
                test_authentication($conn, $email, $senha);
                
                // Debug para mostrar a senha que foi fornecida
                echo "<div class='info'>Senha fornecida para teste: " . htmlspecialchars($senha) . "</div>";
            }
            ?>
        </div>
        
        <div class="section">
            <h2>4. Criar Usuário de Teste</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label for="create_nome">Nome:</label>
                    <input type="text" id="create_nome" name="create_nome" required>
                </div>
                <div class="form-group">
                    <label for="create_email">Email:</label>
                    <input type="email" id="create_email" name="create_email" required>
                </div>
                <div class="form-group">
                    <label for="create_senha">Senha:</label>
                    <input type="password" id="create_senha" name="create_senha" required>
                </div>
                <div class="form-group">
                    <label for="create_tipo">Tipo:</label>
                    <select id="create_tipo" name="create_tipo">
                        <option value="user">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <button type="submit" name="create_user">Criar Usuário</button>
            </form>
            
            <?php
            if (isset($_POST['create_user'])) {
                $nome = clean_input($_POST['create_nome']);
                $email = clean_input($_POST['create_email']);
                $senha = $_POST['create_senha'];
                $tipo = clean_input($_POST['create_tipo']);
                create_test_user($conn, $nome, $email, $senha, $tipo);
            }
            ?>
        </div>
        
        <div class="section">
            <h2>5. Redefinir Senha</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label for="reset_email">Email:</label>
                    <input type="email" id="reset_email" name="reset_email" required>
                </div>
                <div class="form-group">
                    <label for="reset_senha">Nova Senha:</label>
                    <input type="password" id="reset_senha" name="reset_senha" required>
                </div>
                <button type="submit" name="reset_pass">Redefinir Senha</button>
            </form>
            
            <?php
            if (isset($_POST['reset_pass'])) {
                $email = clean_input($_POST['reset_email']);
                $nova_senha = $_POST['reset_senha'];
                reset_password($conn, $email, $nova_senha);
            }
            ?>
        </div>
        
        <div class="section">
            <h2>6. Status da Sessão e Cookies</h2>
            <?php check_session_cookies(); ?>
        </div>
    </div>
</body>
</html>
