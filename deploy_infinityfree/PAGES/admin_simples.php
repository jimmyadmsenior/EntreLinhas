<?php
// Incluir arquivos necessários
require_once "../backend/session_helper.php";
require_once "../backend/config.php";

// Definir administrador para teste
$_SESSION["loggedin"] = true;
$_SESSION["tipo"] = "admin";
$_SESSION["id"] = 1;
$_SESSION["nome"] = "Administrador Teste";
$_SESSION["email"] = "admin@example.com";

// Verificar se está funcionando
$status_admin = is_admin() ? "Sim" : "Não";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin Simplificado - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #333;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        main {
            padding: 20px 0;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>EntreLinhas - Painel Administrativo</h1>
            <p>Usuário: <?php echo $_SESSION["nome"]; ?> | É admin: <?php echo $status_admin; ?></p>
        </div>
    </header>
    
    <main class="container">
        <div class="card">
            <h2>Visão Geral do Sistema</h2>
            <p>Este é um painel administrativo simplificado para testar o funcionamento básico.</p>
            
            <div class="stats-grid">
                <?php
                // Obter algumas estatísticas básicas
                $stats = [];
                
                // Total de artigos
                $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM artigos");
                $stats["total_artigos"] = $result ? mysqli_fetch_assoc($result)["total"] : 0;
                
                // Total de usuários
                $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios");
                $stats["total_usuarios"] = $result ? mysqli_fetch_assoc($result)["total"] : 0;
                
                // Administradores
                $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'admin'");
                $stats["total_admins"] = $result ? mysqli_fetch_assoc($result)["total"] : 0;
                ?>
                
                <div class="stat-card">
                    <h3>Artigos</h3>
                    <div class="stat-number"><?php echo $stats["total_artigos"]; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Usuários</h3>
                    <div class="stat-number"><?php echo $stats["total_usuarios"]; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Administradores</h3>
                    <div class="stat-number"><?php echo $stats["total_admins"]; ?></div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>Informações de Sessão</h2>
            <pre>
SESSION ID: <?php echo session_id(); ?>
is_logged_in(): <?php echo is_logged_in() ? 'true' : 'false'; ?>
is_admin(): <?php echo is_admin() ? 'true' : 'false'; ?>
            </pre>
            <p>Variáveis de sessão:</p>
            <pre>
<?php print_r($_SESSION); ?>
            </pre>
        </div>
        
        <div class="card">
            <h2>Ações</h2>
            <ul>
                <li><a href="admin_dashboard.php">Ir para o painel completo</a></li>
                <li><a href="../index.php">Voltar para a página inicial</a></li>
            </ul>
        </div>
    </main>
    
    <footer class="container">
        <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
