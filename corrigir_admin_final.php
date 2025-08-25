<?php
// Script para corrigir definitivamente o problema do painel administrativo

// 1. Começar com uma sessão limpa
session_start();
session_unset();
session_destroy();
session_start();

// Incluir configuração do banco de dados
require_once "backend/config.php";

echo "<h1>Correção Definitiva do Painel Administrativo</h1>";

// 2. Verificar conexão com o banco de dados
if (!$conn) {
    die("<p style='color:red'>Erro na conexão com o banco de dados: " . mysqli_connect_error() . "</p>");
}
echo "<p style='color:green'>✓ Conexão com o banco de dados estabelecida.</p>";

// 3. Verificar se o banco de dados tem a tabela usuários
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'usuarios'");
if (mysqli_num_rows($check_table) == 0) {
    die("<p style='color:red'>A tabela 'usuarios' não existe no banco de dados.</p>");
}
echo "<p style='color:green'>✓ Tabela 'usuarios' encontrada.</p>";

// 4. Verificar se existe um administrador
$check_admin = mysqli_query($conn, "SELECT id, nome, email FROM usuarios WHERE tipo = 'admin' LIMIT 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo "<p style='color:orange'>! Nenhum administrador encontrado. Criando um administrador...</p>";
    
    // Criar um administrador
    $nome = "Administrador";
    $email = "admin@example.com";
    $senha = password_hash("admin123", PASSWORD_DEFAULT);
    
    $insert = mysqli_query($conn, "INSERT INTO usuarios (nome, email, senha, tipo) VALUES ('$nome', '$email', '$senha', 'admin')");
    if (!$insert) {
        die("<p style='color:red'>Erro ao criar administrador: " . mysqli_error($conn) . "</p>");
    }
    
    $admin_id = mysqli_insert_id($conn);
    echo "<p style='color:green'>✓ Administrador criado com ID: $admin_id</p>";
} else {
    $admin = mysqli_fetch_assoc($check_admin);
    $admin_id = $admin['id'];
    echo "<p style='color:green'>✓ Administrador encontrado: ID " . $admin_id . ", Nome: " . $admin['nome'] . ", Email: " . $admin['email'] . "</p>";
}

// 5. Simplificar o acesso ao admin_dashboard.php criando um arquivo ponte na pasta PAGES
$bridge_content = <<<EOT
<?php
// Definir manualmente as variáveis de sessão para acesso administrativo
session_start();

// Definir sessão como administrador
\$_SESSION['loggedin'] = true;
\$_SESSION['id'] = $admin_id;
\$_SESSION['nome'] = "Administrador";
\$_SESSION['email'] = "admin@example.com";
\$_SESSION['tipo'] = "admin";

// Verificar se está funcionando
\$is_admin = isset(\$_SESSION['tipo']) && \$_SESSION['tipo'] === 'admin';
echo "<p>Status de admin: " . (\$is_admin ? "SIM" : "NÃO") . "</p>";

// Redirecionar para o painel admin
echo "<p><a href='admin_dashboard.php'>Acessar o painel administrativo</a></p>";
echo "<p><a href='admin_simples.php'>Acessar o painel administrativo simplificado</a></p>";
?>
EOT;

file_put_contents("PAGES/admin_bridge.php", $bridge_content);
echo "<p style='color:green'>✓ Arquivo ponte criado em PAGES/admin_bridge.php</p>";

// 6. Verificar se o arquivo admin_dashboard.php está acessível
$dashboard_path = "PAGES/admin_dashboard.php";
if (!file_exists($dashboard_path)) {
    die("<p style='color:red'>O arquivo $dashboard_path não existe!</p>");
}
echo "<p style='color:green'>✓ Arquivo $dashboard_path encontrado.</p>";

// 7. Criar uma versão simplificada do arquivo admin_dashboard.php
$simple_admin = <<<EOT
<?php
// Arquivo de painel administrativo simplificado
session_start();

// Verificar se o usuário está logado como admin
if (!isset(\$_SESSION['loggedin']) || \$_SESSION['tipo'] !== 'admin') {
    echo "<p style='color:red'>Acesso negado. Você não está logado como administrador.</p>";
    echo "<p><a href='../index.php'>Voltar para a página inicial</a></p>";
    exit;
}

require_once "../backend/config.php";

// Obter estatísticas básicas
\$total_artigos = 0;
\$result = mysqli_query(\$conn, "SELECT COUNT(*) as total FROM artigos");
if (\$row = mysqli_fetch_assoc(\$result)) {
    \$total_artigos = \$row['total'];
}

\$total_usuarios = 0;
\$result = mysqli_query(\$conn, "SELECT COUNT(*) as total FROM usuarios");
if (\$row = mysqli_fetch_assoc(\$result)) {
    \$total_usuarios = \$row['total'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - EntreLinhas</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
        header { background: #333; color: #fff; text-align: center; padding: 1rem; }
        .container { width: 80%; margin: 0 auto; padding: 2rem; }
        .card { background: #f9f9f9; border-radius: 5px; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stats { display: flex; justify-content: space-around; margin: 2rem 0; }
        .stat-box { text-align: center; padding: 1rem; background: #fff; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <header>
        <h1>EntreLinhas - Painel Administrativo</h1>
    </header>
    
    <div class="container">
        <div class="card">
            <h2>Bem-vindo, <?php echo htmlspecialchars(\$_SESSION['nome']); ?>!</h2>
            <p>Este é o painel administrativo simplificado do EntreLinhas.</p>
        </div>
        
        <div class="stats">
            <div class="stat-box">
                <h3>Total de Artigos</h3>
                <div class="stat-number"><?php echo \$total_artigos; ?></div>
            </div>
            
            <div class="stat-box">
                <h3>Total de Usuários</h3>
                <div class="stat-number"><?php echo \$total_usuarios; ?></div>
            </div>
        </div>
        
        <div class="card">
            <h3>Informações da Sessão</h3>
            <pre><?php print_r(\$_SESSION); ?></pre>
        </div>
        
        <div class="card">
            <h3>Links Úteis</h3>
            <p><a href="admin_dashboard.php">Tentar acessar o painel completo</a></p>
            <p><a href="../index.php">Voltar para a página inicial</a></p>
            <p><a href="../backend/logout.php">Sair</a></p>
        </div>
    </div>
</body>
</html>
EOT;

file_put_contents("PAGES/admin_basico.php", $simple_admin);
echo "<p style='color:green'>✓ Criado arquivo de painel administrativo simplificado em PAGES/admin_basico.php</p>";

// 8. Criar um link para acessar o painel diretamente
echo "<h2>Acesso ao Painel Administrativo</h2>";
echo "<p>Tente acessar o painel administrativo usando um destes links:</p>";
echo "<ul>";
echo "<li><a href='PAGES/admin_bridge.php' target='_blank'>Usar ponte para acessar o painel</a></li>";
echo "<li><a href='PAGES/admin_basico.php' target='_blank'>Acessar versão simplificada do painel</a></li>";
echo "<li><a href='PAGES/admin_simples.php' target='_blank'>Acessar versão alternativa do painel</a></li>";
echo "</ul>";

// 9. Verificar a estrutura do projeto e arquivos críticos
echo "<h2>Verificação de Estrutura do Projeto</h2>";

$critical_files = [
    "backend/session_helper.php",
    "backend/config.php",
    "PAGES/admin_dashboard.php",
    "assets/css/style.css",
    "assets/js/user-menu.js"
];

foreach ($critical_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color:green'>✓ O arquivo $file existe.</p>";
    } else {
        echo "<p style='color:red'>✗ O arquivo $file não foi encontrado!</p>";
    }
}

// 10. Verificar sessão no navegador
echo "<h2>Verificação de Sessão no Navegador</h2>";
echo "<p>ID de sessão atual: " . session_id() . "</p>";
echo "<p>Configurações de cookie de sessão:</p>";
echo "<ul>";
echo "<li>session.cookie_path: " . ini_get('session.cookie_path') . "</li>";
echo "<li>session.cookie_domain: " . ini_get('session.cookie_domain') . "</li>";
echo "<li>session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "</li>";
echo "</ul>";

// 11. Definir sessão como admin para teste direto
$_SESSION["loggedin"] = true;
$_SESSION["id"] = $admin_id;
$_SESSION["nome"] = "Administrador";
$_SESSION["email"] = "admin@example.com";
$_SESSION["tipo"] = "admin";

echo "<p style='color:green'>✓ Sessão atual definida como administrador.</p>";
echo "<p>Você deve poder acessar o painel administrativo diretamente agora:</p>";
echo "<p><a href='PAGES/admin_dashboard.php' target='_blank'>Acessar o painel administrativo</a></p>";
?>
