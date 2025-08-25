<?php
// Script para resolver definitivamente o problema de acesso ao admin dashboard
require_once "backend/config.php";

// 1. Limpar cookies e sessão
setcookie("PHPSESSID", "", time() - 3600, "/"); 
session_start();
session_unset();
session_destroy();

// 2. Iniciar uma nova sessão
session_start();

echo "<h1>Resolvendo o acesso ao painel de administração</h1>";

// 3. Verificar se temos um administrador no banco de dados
$admin_email = "admin@example.com";
$admin_senha = "admin123";

$check_admin = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'admin'");
$row = mysqli_fetch_assoc($check_admin);

if ($row["total"] == 0) {
    // Criar um novo administrador se não existir
    $nome = "Administrador";
    $senha_hash = password_hash($admin_senha, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'admin')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $nome, $admin_email, $senha_hash);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green'>✓ Administrador criado com sucesso!</p>";
        $admin_id = mysqli_insert_id($conn);
    } else {
        echo "<p style='color:red'>✗ Erro ao criar administrador: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p>✓ Administrador já existe no sistema.</p>";
    
    // Obter o ID do administrador
    $admin = mysqli_query($conn, "SELECT id FROM usuarios WHERE tipo = 'admin' LIMIT 1");
    $admin_row = mysqli_fetch_assoc($admin);
    $admin_id = $admin_row["id"];
}

// 4. Definir variáveis de sessão
$_SESSION["loggedin"] = true;
$_SESSION["id"] = $admin_id;
$_SESSION["nome"] = "Administrador";
$_SESSION["email"] = $admin_email;
$_SESSION["tipo"] = "admin";

echo "<p>✓ Variáveis de sessão definidas:</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 5. Definir o cookie da sessão explicitamente
echo "<p>✓ Cookie da sessão definido: PHPSESSID=" . session_id() . "</p>";

// 6. Verificar se os arquivos estão acessíveis
$files_to_check = [
    "backend/session_helper.php",
    "backend/session.php",
    "backend/config.php",
    "PAGES/admin_dashboard.php"
];

echo "<h2>Verificação de arquivos</h2>";
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color:green'>✓ $file existe e está acessível</p>";
    } else {
        echo "<p style='color:red'>✗ $file não foi encontrado!</p>";
    }
}

// 7. Criar arquivo de ponte para admin_dashboard.php
$bridge_file = "admin_bridge.php";
$bridge_content = <<<EOT
<?php
// Este arquivo é uma ponte para o admin_dashboard.php
session_start();

// Definir as variáveis de sessão
\$_SESSION["loggedin"] = true;
\$_SESSION["id"] = $admin_id;
\$_SESSION["nome"] = "Administrador";
\$_SESSION["email"] = "$admin_email";
\$_SESSION["tipo"] = "admin";

// Redirecionar para o admin_dashboard.php
header("Location: PAGES/admin_dashboard.php");
exit;
?>
EOT;

file_put_contents($bridge_file, $bridge_content);
echo "<p>✓ Arquivo de ponte criado: $bridge_file</p>";

// 8. Links para testar
echo "<h2>Links para acessar o painel</h2>";
echo "<p><a href='admin_bridge.php' target='_blank'>Usar a ponte para acessar o painel</a></p>";
echo "<p><a href='PAGES/admin_dashboard.php?debug=1&force=1' target='_blank'>Acessar painel com debug</a></p>";

// 9. Instruções adicionais
echo "<h2>Instruções adicionais</h2>";
echo "<p>Se você ainda estiver tendo problemas para acessar o painel de administração:</p>";
echo "<ol>";
echo "<li>Verifique se o arquivo PAGES/admin_dashboard.php está usando corretamente require_once '../backend/session_helper.php'</li>";
echo "<li>Certifique-se de que não há redirecionamentos indesejados no arquivo admin_dashboard.php</li>";
echo "<li>Verifique se os arquivos de sessão não têm erros de PHP</li>";
echo "<li>Tente usar outro navegador ou modo privado para evitar problemas com cookies</li>";
echo "</ol>";
?>
