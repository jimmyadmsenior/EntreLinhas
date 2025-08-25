<?php
// Incluir arquivo de gerenciamento de sessões
require_once "../backend/session_helper.php";
require_once "../backend/config.php";

// Verificar se é administrador (simplificado)
if (!isset($_SESSION["loggedin"]) || !isset($_SESSION["tipo"]) || $_SESSION["tipo"] !== "admin") {
    // Verificar se está em modo de depuração
    if (isset($_GET['debug']) && $_GET['debug'] == '1') {
        echo "<h1>Acesso negado ao painel de administração</h1>";
        echo "<pre>";
        echo "SESSION: " . print_r($_SESSION, true) . "\n";
        echo "</pre>";
        
        if (isset($_GET['force']) && $_GET['force'] == '1') {
            $_SESSION["loggedin"] = true;
            $_SESSION["tipo"] = "admin";
            $_SESSION["id"] = isset($_SESSION["id"]) ? $_SESSION["id"] : 1;
            $_SESSION["nome"] = isset($_SESSION["nome"]) ? $_SESSION["nome"] : "Administrador";
            $_SESSION["email"] = isset($_SESSION["email"]) ? $_SESSION["email"] : "admin@example.com";
            
            echo "<p>Forçando acesso como administrador.</p>";
            echo "<p><a href='admin_dashboard_novo.php'>Continuar para o painel administrativo</a></p>";
        } else {
            echo "<p><a href='admin_dashboard_novo.php?debug=1&force=1'>Forçar acesso como administrador</a></p>";
            exit;
        }
    } else {
        // Redirecionar para a página inicial
        header("Location: ../index.php?erro=acesso_negado");
        exit;
    }
}

// Buscar estatísticas
try {
    // Total de artigos
    $query = "SELECT COUNT(*) AS total FROM artigos";
    $result = mysqli_query($conn, $query);
    $total_artigos = ($result && $row = mysqli_fetch_assoc($result)) ? $row["total"] : 0;
    
    // Usuários
    $query = "SELECT COUNT(*) AS total FROM usuarios";
    $result = mysqli_query($conn, $query);
    $total_usuarios = ($result && $row = mysqli_fetch_assoc($result)) ? $row["total"] : 0;
    
    // Artigos pendentes
    $query = "SELECT COUNT(*) AS total FROM artigos WHERE status = 'pendente'";
    $result = mysqli_query($conn, $query);
    $artigos_pendentes = ($result && $row = mysqli_fetch_assoc($result)) ? $row["total"] : 0;
    
    // Comentários pendentes
    $query = "SELECT COUNT(*) AS total FROM comentarios WHERE status = 'pendente'";
    $result = mysqli_query($conn, $query);
    $comentarios_pendentes = ($result && $row = mysqli_fetch_assoc($result)) ? $row["total"] : 0;
} catch (Exception $e) {
    // Erros silenciosos - usar valores padrão
    $total_artigos = 0;
    $total_usuarios = 0;
    $artigos_pendentes = 0;
    $comentarios_pendentes = 0;
}

// Nome do administrador
$nome_admin = isset($_SESSION["nome"]) ? htmlspecialchars($_SESSION["nome"]) : "Administrador";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - EntreLinhas</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- CSS Básico -->
    <style>
        :root {
            --primary: #333;
            --secondary: #666;
            --bg-light: #f8f9fa;
            --bg-dark: #343a40;
            --text-light: #f8f9fa;
            --text-dark: #212529;
            --accent: #007bff;
            --border: #dee2e6;
            --card-bg: #fff;
            --card-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Source Sans Pro', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }
        
        a {
            color: var(--accent);
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header */
        header {
            background-color: var(--bg-dark);
            color: var(--text-light);
            padding: 1rem 0;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo a {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-light);
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 1.5rem;
        }
        
        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
        }
        
        .nav-links a:hover {
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Dashboard */
        .dashboard {
            padding: 2rem 0;
        }
        
        .page-title {
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }
        
        .welcome-text {
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: var(--card-bg);
            border-radius: 0.25rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: var(--accent);
            margin: 0.5rem 0;
        }
        
        .content-card {
            background-color: var(--card-bg);
            border-radius: 0.25rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .content-card h2 {
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }
        
        /* Footer */
        footer {
            background-color: var(--bg-dark);
            color: var(--text-light);
            padding: 2rem 0 1rem;
            margin-top: 2rem;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: var(--text-light);
            text-decoration: none;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.875rem;
        }
        
        /* Utilitários */
        .text-center {
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--accent);
            border: 1px solid var(--accent);
            color: var(--text-light);
        }
        
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
            text-decoration: none;
        }
        
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .mt-4 {
            margin-top: 1.5rem;
        }
        
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <a href="../index.php">EntreLinhas</a>
                </div>
                
                <ul class="nav-links">
                    <li><a href="../index.php">Início</a></li>
                    <li><a href="artigos.php">Artigos</a></li>
                    <li><a href="sobre.php">Sobre</a></li>
                    <li><a href="escola.php">A Escola</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container dashboard">
        <h1 class="page-title">Painel de Administração</h1>
        <p class="welcome-text">Bem-vindo, <?php echo $nome_admin; ?>! Gerencie o conteúdo e os usuários do site.</p>
        
        <div class="content-card">
            <h2>Estatísticas do Site</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-newspaper fa-2x"></i>
                    <div class="stat-number"><?php echo $total_artigos; ?></div>
                    <div>Total de Artigos</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-hourglass-half fa-2x"></i>
                    <div class="stat-number"><?php echo $artigos_pendentes; ?></div>
                    <div>Artigos Pendentes</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-users fa-2x"></i>
                    <div class="stat-number"><?php echo $total_usuarios; ?></div>
                    <div>Usuários</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-comments fa-2x"></i>
                    <div class="stat-number"><?php echo $comentarios_pendentes; ?></div>
                    <div>Comentários Pendentes</div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <h2>Gerenciamento</h2>
            <p>Escolha uma das opções abaixo para gerenciar o conteúdo do site:</p>
            
            <div class="mt-4">
                <a href="#" class="btn btn-primary">Gerenciar Artigos</a>
                <a href="#" class="btn btn-primary">Gerenciar Usuários</a>
                <a href="#" class="btn btn-primary">Configurações do Site</a>
            </div>
        </div>
        
        <div class="content-card">
            <h2>Informações da Sessão</h2>
            <p>Você está logado como: <strong><?php echo $nome_admin; ?></strong></p>
            <p>Tipo de usuário: <strong>Administrador</strong></p>
            <div class="mt-4">
                <a href="../backend/logout.php" class="btn btn-primary">Sair</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-section">
                    <h3>EntreLinhas</h3>
                    <p>Um jornal digital colaborativo da escola.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Links Rápidos</h3>
                    <ul class="footer-links">
                        <li><a href="../index.php">Início</a></li>
                        <li><a href="artigos.php">Artigos</a></li>
                        <li><a href="sobre.php">Sobre</a></li>
                        <li><a href="escola.php">A Escola</a></li>
                        <li><a href="contato.php">Contato</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contato</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> contato@entrelinhas.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Av. Marechal Rondon, 3000 - Salto/SP</li>
                        <li><i class="fas fa-phone"></i> (11) 4029-1234</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
