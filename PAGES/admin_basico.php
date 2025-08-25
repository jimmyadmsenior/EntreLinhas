<?php
// Arquivo de painel administrativo simplificado
session_start();

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['loggedin']) || $_SESSION['tipo'] !== 'admin') {
    echo "<p style='color:red'>Acesso negado. Você não está logado como administrador.</p>";
    echo "<p><a href='../index.php'>Voltar para a página inicial</a></p>";
    exit;
}

require_once "../backend/config.php";

// Obter estatísticas básicas
$total_artigos = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM artigos");
if ($row = mysqli_fetch_assoc($result)) {
    $total_artigos = $row['total'];
}

$total_usuarios = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios");
if ($row = mysqli_fetch_assoc($result)) {
    $total_usuarios = $row['total'];
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
            <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h2>
            <p>Este é o painel administrativo simplificado do EntreLinhas.</p>
        </div>
        
        <div class="stats">
            <div class="stat-box">
                <h3>Total de Artigos</h3>
                <div class="stat-number"><?php echo $total_artigos; ?></div>
            </div>
            
            <div class="stat-box">
                <h3>Total de Usuários</h3>
                <div class="stat-number"><?php echo $total_usuarios; ?></div>
            </div>
        </div>
        
        <div class="card">
            <h3>Informações da Sessão</h3>
            <pre><?php print_r($_SESSION); ?></pre>
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