<?php
// Incluir arquivo de gerenciamento de sessões
require_once "../backend/session_helper.php";
require_once "../backend/config.php";
require_once "../backend/db_connection_fix.php"; // Fix para problemas de conexão

// Verificar se é administrador
if (!is_admin()) {
    header("Location: ../index.php");
    exit;
}

// Consultar estatísticas
$stats = [
    "total_artigos" => 0,
    "artigos_pendentes" => 0,
    "total_usuarios" => 0,
    "comentarios_pendentes" => 0
];

// Total de artigos
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM artigos");
if ($row = mysqli_fetch_assoc($result)) {
    $stats["total_artigos"] = $row["total"];
}

// Artigos pendentes
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM artigos WHERE status = 'pendente'");
if ($row = mysqli_fetch_assoc($result)) {
    $stats["artigos_pendentes"] = $row["total"];
}

// Total de usuários
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios");
if ($row = mysqli_fetch_assoc($result)) {
    $stats["total_usuarios"] = $row["total"];
}

// Comentários pendentes
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM comentarios WHERE status = 'pendente'");
if ($row = mysqli_fetch_assoc($result)) {
    $stats["comentarios_pendentes"] = $row["total"];
}

// Tentar obter a foto de perfil do usuário
$foto_perfil = null;
if (function_exists('obter_foto_perfil') || (!function_exists('obter_foto_perfil') && file_exists(dirname(__FILE__) . "/../backend/usuario_helper.php"))) {
    if (!function_exists('obter_foto_perfil')) {
        require_once dirname(__FILE__) . "/../backend/usuario_helper.php";
    }
    $foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);
}

// Artigos recentes
$artigos_recentes = [];
$result = mysqli_query($conn, "SELECT a.id, a.titulo, a.conteudo, a.status, a.data_criacao, u.nome as autor FROM artigos a JOIN usuarios u ON a.id_usuario = u.id ORDER BY a.data_criacao DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    // Criar um resumo do conteúdo (primeiros 100 caracteres)
    $row['resumo'] = mb_substr(strip_tags($row['conteudo']), 0, 100) . '...';
    unset($row['conteudo']); // Remove o conteúdo completo
    $artigos_recentes[] = $row;
}

// Fechar conexão
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - EntreLinhas</title>
    <meta name="description" content="Painel de administração do EntreLinhas para gerenciar artigos, usuários e configurações do site.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user-menu.css">
    <style>
        .dashboard {
            padding: 2rem 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
            color: var(--primary);
        }
        .recent-table {
            width: 100%;
            border-collapse: collapse;
        }
        .recent-table th,
        .recent-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }
        .recent-table th {
            background-color: var(--bg-alt);
            color: var(--text);
        }
        .badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10px;
        }
        .badge-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        .badge-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .admin-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-light);
            margin-bottom: 2rem;
        }
        .admin-tab {
            padding: 1rem 1.5rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        .admin-tab.active {
            border-bottom-color: var(--primary);
            font-weight: 600;
        }
    </style>
    <script src="../assets/js/user-menu.js" defer></script>
    <script src="../assets/js/theme.js" defer></script>
    <script src="../assets/js/admin-dashboard.js" defer></script>
    <script src="../assets/js/admin-dropdown.js" defer></script>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="artigos.php">Artigos</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="escola.php">A Escola</a></li>
                <li><a href="contato.php">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <!-- Menu dropdown do usuário -->
                <div class="user-menu" id="user-menu">
                    <div class="user-name">
                        <span class="avatar-container">
                            <?php if ($foto_perfil): ?>
                                <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" class="user-avatar">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </span>
                        <?php echo htmlspecialchars($_SESSION["nome"]); ?> <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu" id="user-dropdown-menu">
                        <a href="admin_dashboard.php" class="dropdown-link"><i class="fas fa-tachometer-alt"></i> Painel</a>
                        <a href="perfil.php" class="dropdown-link"><i class="fas fa-id-card"></i> Meu Perfil</a>
                        <a href="meus-artigos.php" class="dropdown-link"><i class="fas fa-newspaper"></i> Meus Artigos</a>
                        <a href="enviar-artigo.php" class="dropdown-link"><i class="fas fa-edit"></i> Enviar Artigo</a>
                        <a href="../backend/logout.php" class="dropdown-link"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    </div>
                </div>
                
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar modo escuro">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="mobile-menu" class="mobile-menu-btn" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container dashboard">
        <h1>Painel de Administração</h1>
        <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION["nome"]); ?>! Gerencie o conteúdo e os usuários do site.</p>
        
        <div class="admin-tabs">
            <div class="admin-tab active" data-tab="overview">Visão Geral</div>
            <div class="admin-tab" data-tab="articles">Artigos</div>
            <div class="admin-tab" data-tab="users">Usuários</div>
            <div class="admin-tab" data-tab="comments">Comentários</div>
        </div>
        
        <section id="overview" class="tab-content active">
            <h2>Estatísticas do Site</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-newspaper fa-2x"></i>
                    <div class="stat-number"><?php echo $stats["total_artigos"]; ?></div>
                    <div>Total de Artigos</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-hourglass-half fa-2x"></i>
                    <div class="stat-number"><?php echo $stats["artigos_pendentes"]; ?></div>
                    <div>Artigos Pendentes</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-users fa-2x"></i>
                    <div class="stat-number"><?php echo $stats["total_usuarios"]; ?></div>
                    <div>Usuários</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-comments fa-2x"></i>
                    <div class="stat-number"><?php echo $stats["comentarios_pendentes"]; ?></div>
                    <div>Comentários Pendentes</div>
                </div>
            </div>
            
            <h2>Artigos Recentes</h2>
            <div class="table-responsive">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($artigos_recentes as $artigo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($artigo["titulo"]); ?></td>
                            <td><?php echo htmlspecialchars($artigo["autor"]); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($artigo["data_criacao"])); ?></td>
                            <td>
                                <?php 
                                switch($artigo["status"]) {
                                    case 'pendente':
                                        echo '<span class="badge badge-pending">Pendente</span>';
                                        break;
                                    case 'aprovado':
                                        echo '<span class="badge badge-approved">Aprovado</span>';
                                        break;
                                    case 'rejeitado':
                                        echo '<span class="badge badge-rejected">Rejeitado</span>';
                                        break;
                                }
                                ?>
                            </td>
                            <td>
                                <a href="editar-artigo.php?id=<?php echo $artigo["id"]; ?>" class="btn btn-small">Editar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($artigos_recentes)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Nenhum artigo encontrado</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-right">
                <a href="gerenciar-artigos.php" class="btn btn-primary">Ver Todos os Artigos</a>
            </div>
        </section>
        
        <section id="articles" class="tab-content" style="display: none;">
            <h2>Gerenciar Artigos</h2>
            <p>Esta seção será implementada em breve.</p>
        </section>
        
        <section id="users" class="tab-content" style="display: none;">
            <h2>Gerenciar Usuários</h2>
            <p>Esta seção será implementada em breve.</p>
        </section>
        
        <section id="comments" class="tab-content" style="display: none;">
            <h2>Gerenciar Comentários</h2>
            <p>Esta seção será implementada em breve.</p>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>EntreLinhas</h3>
                <p>Um jornal digital colaborativo onde vozes diversas se encontram para compartilhar conhecimento, histórias e experiências.</p>
            </div>
            
            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Início</a></li>
                    <li><a href="artigos.php">Artigos</a></li>
                    <li><a href="sobre.php">Sobre</a></li>
                    <li><a href="escola.php">A Escola</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contato</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-envelope"></i> jimmycastilho555@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Av. Marechal Rondon, 3000 - Jardim Bandeirantes, Salto - SP</li>
                    <li><i class="fas fa-phone"></i> (11) 4029-1234</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
