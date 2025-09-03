<?php
/**
 * Exemplo de como converter um código de mysqli para PDO
 * Este arquivo mostra exemplos lado a lado para servir como referência
 */

// Exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Exemplo de Migração mysqli para PDO</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .example { display: flex; margin: 30px 0; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
        .mysqli, .pdo { flex: 1; padding: 15px; }
        .mysqli { background-color: #f8d7da; }
        .pdo { background-color: #d4edda; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; }
        code { font-family: Monaco, Consolas, 'Courier New', monospace; }
    </style>
</head>
<body>
    <h1>Exemplos de Migração de mysqli para PDO</h1>
    
    <div class='example'>
        <div class='mysqli'>
            <h3>mysqli - Configuração</h3>
            <pre><code>
// Configuração mysqli
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'user');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'database');

// Conexão
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if (!$conn) {
    die("ERRO: Não foi possível conectar ao MySQL. " . mysqli_connect_error());
}

// Configurar charset
mysqli_set_charset($conn, "utf8mb4");
            </code></pre>
        </div>
        
        <div class='pdo'>
            <h3>PDO - Configuração</h3>
            <pre><code>
// Configuração PDO
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'user');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'database');

// Conexão com PDO
try {
    $pdo = new PDO(
        "mysql:host=".DB_SERVER.";dbname=".DB_NAME.";charset=utf8mb4", 
        DB_USERNAME, 
        DB_PASSWORD
    );
    
    // Configurar PDO para lançar exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Retornar resultados como arrays associativos
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Para compatibilidade com código existente
    $conn = $pdo;
    
} catch (PDOException $e) {
    die("ERRO: Conexão falhou: " . $e->getMessage());
}
            </code></pre>
        </div>
    </div>

    <div class='example'>
        <div class='mysqli'>
            <h3>mysqli - Consulta Simples</h3>
            <pre><code>
// Consulta simples com mysqli
$sql = "SELECT * FROM usuarios WHERE id = 1";
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Nome: " . $row['nome'];
} else {
    echo "Erro: " . mysqli_error($conn);
}
            </code></pre>
        </div>
        
        <div class='pdo'>
            <h3>PDO - Consulta Simples</h3>
            <pre><code>
// Consulta simples com PDO
$sql = "SELECT * FROM usuarios WHERE id = 1";

try {
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch();
    echo "Nome: " . $row['nome'];
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

// OU usando a função auxiliar pdo_query_first
require_once "pdo_helper.php";
$row = pdo_query_first($pdo, $sql);
if ($row) {
    echo "Nome: " . $row['nome'];
}
            </code></pre>
        </div>
    </div>

    <div class='example'>
        <div class='mysqli'>
            <h3>mysqli - Prepared Statement</h3>
            <pre><code>
// Prepared Statement com mysqli
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo "Usuário encontrado: " . $row['nome'];
} else {
    echo "Usuário não encontrado";
}

mysqli_stmt_close($stmt);
            </code></pre>
        </div>
        
        <div class='pdo'>
            <h3>PDO - Prepared Statement</h3>
            <pre><code>
// Prepared Statement com PDO
$sql = "SELECT * FROM usuarios WHERE email = ?";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    
    if ($row = $stmt->fetch()) {
        echo "Usuário encontrado: " . $row['nome'];
    } else {
        echo "Usuário não encontrado";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

// OU usando parâmetros nomeados (mais legível)
$sql = "SELECT * FROM usuarios WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
            </code></pre>
        </div>
    </div>

    <div class='example'>
        <div class='mysqli'>
            <h3>mysqli - INSERT</h3>
            <pre><code>
// INSERT com mysqli
$sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $nome, $email, $senha);

if (mysqli_stmt_execute($stmt)) {
    $id_inserido = mysqli_insert_id($conn);
    echo "Usuário cadastrado com ID: " . $id_inserido;
} else {
    echo "Erro: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
            </code></pre>
        </div>
        
        <div class='pdo'>
            <h3>PDO - INSERT</h3>
            <pre><code>
// INSERT com PDO
$sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $email, $senha]);
    $id_inserido = $pdo->lastInsertId();
    echo "Usuário cadastrado com ID: " . $id_inserido;
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

// OU com parâmetros nomeados
$sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':senha', $senha, PDO::PARAM_STR);
$stmt->execute();
            </code></pre>
        </div>
    </div>

    <div class='example'>
        <div class='mysqli'>
            <h3>mysqli - Transações</h3>
            <pre><code>
// Transações com mysqli
mysqli_begin_transaction($conn);

try {
    mysqli_query($conn, "INSERT INTO usuarios (nome) VALUES ('João')");
    mysqli_query($conn, "UPDATE usuarios SET creditos = creditos + 100 WHERE id = 1");
    
    // Se tudo OK, commit
    mysqli_commit($conn);
    echo "Transação concluída com sucesso";
} catch (Exception $e) {
    // Se erro, rollback
    mysqli_rollback($conn);
    echo "Erro: " . $e->getMessage();
}
            </code></pre>
        </div>
        
        <div class='pdo'>
            <h3>PDO - Transações</h3>
            <pre><code>
// Transações com PDO
try {
    $pdo->beginTransaction();
    
    $pdo->exec("INSERT INTO usuarios (nome) VALUES ('João')");
    $pdo->exec("UPDATE usuarios SET creditos = creditos + 100 WHERE id = 1");
    
    // Se tudo OK, commit
    $pdo->commit();
    echo "Transação concluída com sucesso";
} catch (PDOException $e) {
    // Se erro, rollback
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Erro: " . $e->getMessage();
}
            </code></pre>
        </div>
    </div>

    <div class='example'>
        <div class='mysqli'>
            <h3>mysqli - Loop em Resultados</h3>
            <pre><code>
// Loop em resultados com mysqli
$sql = "SELECT id, nome FROM usuarios";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>ID: " . $row['id'] . " - Nome: " . $row['nome'] . "</li>";
    }
    echo "</ul>";
    
    // Liberar resultados
    mysqli_free_result($result);
} else {
    echo "Erro: " . mysqli_error($conn);
}

// Fechar conexão
mysqli_close($conn);
            </code></pre>
        </div>
        
        <div class='pdo'>
            <h3>PDO - Loop em Resultados</h3>
            <pre><code>
// Loop em resultados com PDO
$sql = "SELECT id, nome FROM usuarios";

try {
    $stmt = $pdo->query($sql);
    
    echo "<ul>";
    while ($row = $stmt->fetch()) {
        echo "<li>ID: " . $row['id'] . " - Nome: " . $row['nome'] . "</li>";
    }
    echo "</ul>";
    
    // Liberar statement
    $stmt = null;
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

// Fechar conexão (opcional, acontece automaticamente)
$pdo = null;
            </code></pre>
        </div>
    </div>

    <h2>Vantagens do PDO</h2>
    <ul>
        <li>Suporte a múltiplos bancos de dados (MySQL, PostgreSQL, SQLite, etc.)</li>
        <li>Prepared statements mais seguros e eficientes</li>
        <li>Tratamento de erros via exceções</li>
        <li>Interface orientada a objetos mais moderna</li>
        <li>Parâmetros nomeados para mais legibilidade</li>
        <li>Métodos de recuperação de dados mais flexíveis</li>
    </ul>

    <h2>Dicas para a migração</h2>
    <ul>
        <li>Comece atualizando os arquivos de configuração para usar PDO</li>
        <li>Use funções auxiliares para simplificar a migração</li>
        <li>Utilize o arquivo de verificação para monitorar o progresso</li>
        <li>Teste cada arquivo após a conversão</li>
        <li>Prefira prepared statements para consultas com parâmetros</li>
    </ul>
</body>
</html>";
?>
