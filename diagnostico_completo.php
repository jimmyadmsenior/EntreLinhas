<?php
// Script para testar cada aspecto do login e acesso de administrador

// Passo 1: Destruir qualquer sessão existente
session_start();
session_destroy();

// Passo 2: Iniciar uma nova sessão
session_start();

// Função para exibir resultado de teste
function test_result($test, $result, $message) {
    $status = $result ? "✓" : "✗";
    $color = $result ? "green" : "red";
    echo "<p style='color:$color'>$status $test: $message</p>";
    return $result;
}

echo "<h1>Diagnóstico passo-a-passo do sistema de administração</h1>";

// Passo 3: Verificar conexão com o banco de dados
echo "<h2>1. Conexão com o banco de dados</h2>";
if (!file_exists("backend/config.php")) {
    test_result("Arquivo config.php", false, "O arquivo backend/config.php não existe");
    die("Não é possível continuar sem o arquivo de configuração.");
}

require_once "backend/config.php";
$db_connected = isset($conn) && $conn instanceof mysqli && !$conn->connect_error;
test_result("Conexão MySQL", $db_connected, $db_connected ? "Conectado com sucesso" : "Falha na conexão: " . ($conn->connect_error ?? "Erro desconhecido"));

if (!$db_connected) {
    die("Não é possível continuar sem conexão ao banco de dados.");
}

// Passo 4: Verificar tabela de usuários
echo "<h2>2. Estrutura do banco de dados</h2>";
$users_table = mysqli_query($conn, "SHOW TABLES LIKE 'usuarios'");
$has_users_table = mysqli_num_rows($users_table) > 0;
test_result("Tabela de usuários", $has_users_table, $has_users_table ? "A tabela 'usuarios' existe" : "A tabela 'usuarios' não existe");

if ($has_users_table) {
    $columns = mysqli_query($conn, "SHOW COLUMNS FROM usuarios");
    $fields = [];
    while ($column = mysqli_fetch_assoc($columns)) {
        $fields[] = $column['Field'];
    }
    
    $required_fields = ['id', 'nome', 'email', 'senha', 'tipo'];
    $missing_fields = array_diff($required_fields, $fields);
    
    $structure_ok = empty($missing_fields);
    test_result("Estrutura da tabela", $structure_ok, 
        $structure_ok ? "A tabela tem todos os campos necessários" : "Campos ausentes: " . implode(", ", $missing_fields));
}

// Passo 5: Verificar administrador no banco de dados
echo "<h2>3. Usuário administrador</h2>";
$check_admin = mysqli_query($conn, "SELECT id, nome, email FROM usuarios WHERE tipo = 'admin' LIMIT 1");
$has_admin = mysqli_num_rows($check_admin) > 0;
$admin_data = $has_admin ? mysqli_fetch_assoc($check_admin) : null;

test_result("Usuário administrador", $has_admin, 
    $has_admin ? "Encontrado: {$admin_data['nome']} ({$admin_data['email']})" : "Nenhum administrador encontrado");

// Se não houver admin, criar um
if (!$has_admin) {
    echo "<p>Criando um novo administrador...</p>";
    
    $nome = "Administrador";
    $email = "admin@example.com";
    $senha = "admin123";
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'admin')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $nome, $email, $senha_hash);
    
    $created = mysqli_stmt_execute($stmt);
    test_result("Criação de administrador", $created, 
        $created ? "Administrador criado com sucesso" : "Erro ao criar: " . mysqli_error($conn));
    
    if ($created) {
        $admin_id = mysqli_insert_id($conn);
        $admin_data = ['id' => $admin_id, 'nome' => $nome, 'email' => $email];
        $has_admin = true;
    }
}

// Passo 6: Verificar arquivos de sessão
echo "<h2>4. Arquivos de sessão</h2>";

$session_files = [
    "session_helper.php" => "backend/session_helper.php",
    "session.php" => "backend/session.php"
];

$all_files_exist = true;
foreach ($session_files as $name => $path) {
    $exists = file_exists($path);
    $all_files_exist = $all_files_exist && $exists;
    test_result("Arquivo $name", $exists, $exists ? "Encontrado em $path" : "Não encontrado em $path");
}

if ($all_files_exist) {
    echo "<p>Incluindo arquivos de sessão...</p>";
    require_once "backend/session_helper.php";
    
    $functions = [
        "session_start_safe", "is_logged_in", "is_admin", "require_login", "require_admin"
    ];
    
    foreach ($functions as $func) {
        $exists = function_exists($func);
        test_result("Função $func", $exists, $exists ? "Definida corretamente" : "Não encontrada");
    }
}

// Passo 7: Teste de sessão
echo "<h2>5. Teste de sessão</h2>";

// Definir variáveis de sessão com base no admin encontrado/criado
if ($has_admin) {
    $_SESSION["loggedin"] = true;
    $_SESSION["id"] = $admin_data["id"];
    $_SESSION["nome"] = $admin_data["nome"];
    $_SESSION["email"] = $admin_data["email"];
    $_SESSION["tipo"] = "admin";
    
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    $session_set = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
    test_result("Variáveis de sessão", $session_set, $session_set ? "Definidas corretamente" : "Problema ao definir variáveis de sessão");
    
    // Verificar funções de teste
    if (function_exists('is_logged_in') && function_exists('is_admin')) {
        $logged_in = is_logged_in();
        $is_admin = is_admin();
        
        test_result("Função is_logged_in()", $logged_in, $logged_in ? "Retorna true" : "Retorna false mesmo com sessão definida");
        test_result("Função is_admin()", $is_admin, $is_admin ? "Retorna true" : "Retorna false mesmo com tipo='admin'");
    }
}

// Passo 8: Página admin_dashboard.php
echo "<h2>6. Página admin_dashboard.php</h2>";
$dashboard_file = "PAGES/admin_dashboard.php";
$dashboard_exists = file_exists($dashboard_file);

test_result("Arquivo admin_dashboard.php", $dashboard_exists, 
    $dashboard_exists ? "Encontrado em $dashboard_file" : "Não encontrado em $dashboard_file");

if ($dashboard_exists) {
    $content = file_get_contents($dashboard_file);
    $has_require = strpos($content, "require_once \"../backend/session_helper.php\"") !== false || 
                  strpos($content, "require_once '../backend/session_helper.php'") !== false;
    
    test_result("Include de session_helper", $has_require, 
        $has_require ? "O arquivo inclui session_helper.php corretamente" : "Problema com o include de session_helper.php");
    
    $has_require_admin = strpos($content, "require_admin") !== false;
    test_result("Uso de require_admin()", $has_require_admin, 
        $has_require_admin ? "A função require_admin() é chamada" : "A função require_admin() não é chamada");
}

// Passo 9: Link para acessar o dashboard
echo "<h2>7. Acesso ao painel de administração</h2>";

if ($has_admin && isset($is_admin) && $is_admin) {
    echo "<p>Tudo pronto! Você deve poder acessar o painel de administração agora.</p>";
    echo "<p><a href='PAGES/admin_dashboard.php' target='_blank'>Acessar o painel de administração</a></p>";
    echo "<p><a href='PAGES/admin_dashboard.php?debug=1' target='_blank'>Acessar com informações de debug</a></p>";
} else {
    echo "<p>Há problemas que precisam ser resolvidos antes de acessar o painel.</p>";
}

// Informações adicionais
echo "<h2>8. Informações adicionais</h2>";
echo "<p>ID da sessão atual: " . session_id() . "</p>";
echo "<p>Caminho de armazenamento de sessão: " . session_save_path() . "</p>";
echo "<p>Configurações de cookie: path=" . ini_get('session.cookie_path') . 
     ", domain=" . ini_get('session.cookie_domain') . 
     ", secure=" . ini_get('session.cookie_secure') . 
     ", httponly=" . ini_get('session.cookie_httponly') . "</p>";

// Criar bridge file alternativo
$bridge_content = <<<EOT
<?php
// Este arquivo usa outro método para definir a sessão admin
// Salva variáveis de sessão diretamente no arquivo da sessão

// Iniciar sessão
session_start();

// Definir sessão como admin
\$_SESSION["loggedin"] = true;
\$_SESSION["id"] = {$admin_data["id"]};
\$_SESSION["nome"] = "{$admin_data["nome"]}";
\$_SESSION["email"] = "{$admin_data["email"]}";
\$_SESSION["tipo"] = "admin";

// Debug
echo "<pre>";
print_r(\$_SESSION);
echo "</pre>";

// Link para admin
echo "<a href='PAGES/admin_dashboard.php'>Ir para o painel admin</a>";
?>
EOT;

file_put_contents("admin_bridge2.php", $bridge_content);
echo "<p><a href='admin_bridge2.php' target='_blank'>Tentar método alternativo de login</a></p>";
?>
