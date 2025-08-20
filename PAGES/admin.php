<?php
// Iniciar sessão
session_start();

// Verificar se o usuário é um administrador (isso deve ser implementado com base na sua lógica de administração)
$is_admin = false;
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["email"]) && $_SESSION["email"] === "jimmycastilho555@gmail.com") {
    $is_admin = true;
} 

// Se não for administrador, redirecionar
if (!$is_admin) {
    header("location: index.html");
    exit;
}

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Obter artigos pendentes
$sql = "SELECT a.id, a.titulo, a.categoria, a.data_criacao, u.nome, u.email 
        FROM artigos a 
        INNER JOIN usuarios u ON a.id_usuario = u.id 
        WHERE a.status = 'pendente' 
        ORDER BY a.data_criacao DESC";

$artigos_pendentes = [];
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $artigos_pendentes[] = $row;
    }
    mysqli_free_result($result);
}

// Obter artigos aprovados
$sql = "SELECT a.id, a.titulo, a.categoria, a.data_criacao, a.data_publicacao, u.nome 
        FROM artigos a 
        INNER JOIN usuarios u ON a.id_usuario = u.id 
        WHERE a.status = 'aprovado' 
        ORDER BY a.data_publicacao DESC";

$artigos_aprovados = [];
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $artigos_aprovados[] = $row;
    }
    mysqli_free_result($result);
}

// Obter estatísticas
$estatisticas = [
    'total_artigos' => 0,
    'pendentes' => count($artigos_pendentes),
    'aprovados' => count($artigos_aprovados),
    'rejeitados' => 0,
    'total_usuarios' => 0
];

// Total de artigos
$sql = "SELECT COUNT(*) as total FROM artigos";
if ($result = mysqli_query($conn, $sql)) {
    if ($row = mysqli_fetch_assoc($result)) {
        $estatisticas['total_artigos'] = $row['total'];
    }
    mysqli_free_result($result);
}

// Artigos rejeitados
$sql = "SELECT COUNT(*) as total FROM artigos WHERE status = 'rejeitado'";
if ($result = mysqli_query($conn, $sql)) {
    if ($row = mysqli_fetch_assoc($result)) {
        $estatisticas['rejeitados'] = $row['total'];
    }
    mysqli_free_result($result);
}

// Total de usuários
$sql = "SELECT COUNT(*) as total FROM usuarios";
if ($result = mysqli_query($conn, $sql)) {
    if ($row = mysqli_fetch_assoc($result)) {
        $estatisticas['total_usuarios'] = $row['total'];
    }
    mysqli_free_result($result);
}

// Processar a aprovação ou rejeição de artigos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $artigo_id = intval($_POST['id']);
        
        if ($_POST['action'] == 'aprovar') {
            $sql = "UPDATE artigos SET status = 'aprovado', data_publicacao = NOW() WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $artigo_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Redirecionar para atualizar a página
                    header("location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    echo "Erro ao aprovar artigo.";
                }
                
                mysqli_stmt_close($stmt);
            }
        } elseif ($_POST['action'] == 'rejeitar') {
            $sql = "UPDATE artigos SET status = 'rejeitado' WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $artigo_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Redirecionar para atualizar a página
                    header("location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    echo "Erro ao rejeitar artigo.";
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador - EntreLinhas</title>
    <meta name="description" content="Painel de administração do EntreLinhas.">
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
    <style>
        /* Estilos específicos para o dashboard */
        .dashboard-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr 3fr;
            }
        }
        
        .sidebar {
            background-color: var(--bg-secondary);
            border-radius: 0.5rem;
            padding: 1.5rem;
            position: sticky;
            top: 2rem;
            height: fit-content;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: var(--primary);
            color: white;
        }
        
        .sidebar-menu li a i {
            margin-right: 0.5rem;
            width: 1.25rem;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .stat-card {
            background-color: var(--bg-secondary);
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .content-card {
            background-color: var(--bg-secondary);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            text-align: left;
            padding: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        
        .btn-sm {
            padding: 0.35rem 0.6rem;
            font-size: 0.8rem;
        }
        
        .btn-approve {
            background-color: var(--success);
            color: white;
        }
        
        .btn-approve:hover {
            background-color: #218838;
        }
        
        .btn-reject {
            background-color: var(--error);
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #bd2130;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.html">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.html">Início</a></li>
                <li><a href="artigos.html">Artigos</a></li>
                <li><a href="sobre.html">Sobre</a></li>
                <li><a href="escola.html">A Escola</a></li>
                <li><a href="contato.html">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle">
                        <i class="fas fa-user-shield"></i> Administrador
                    </button>
                    <div class="dropdown-menu">
                        <a href="admin.php" class="active">Painel Admin</a>
                        <a href="../backend/logout.php">Sair</a>
                    </div>
                </div>
                
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar modo escuro">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="page-header fade-in">
            <h1>Painel do Administrador</h1>
            <p>Gerencie artigos, usuários e conteúdo do EntreLinhas.</p>
        </div>
        
        <div class="dashboard-container fade-in">
            <div class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="#dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#artigos-pendentes"><i class="fas fa-clock"></i> Artigos Pendentes</a></li>
                    <li><a href="#artigos-publicados"><i class="fas fa-check-circle"></i> Artigos Publicados</a></li>
                    <li><a href="index.html" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Site</a></li>
                    <li><a href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </div>
            
            <div class="main-content">
                <section id="dashboard" class="content-card">
                    <h2><i class="fas fa-chart-line"></i> Estatísticas Gerais</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon" style="color: var(--primary);">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="stat-number"><?php echo $estatisticas['total_artigos']; ?></div>
                            <div class="stat-label">Total de Artigos</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="color: #f0ad4e;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-number"><?php echo $estatisticas['pendentes']; ?></div>
                            <div class="stat-label">Artigos Pendentes</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="color: #5cb85c;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-number"><?php echo $estatisticas['aprovados']; ?></div>
                            <div class="stat-label">Artigos Aprovados</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="color: #d9534f;">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-number"><?php echo $estatisticas['rejeitados']; ?></div>
                            <div class="stat-label">Artigos Rejeitados</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="color: var(--primary);">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?php echo $estatisticas['total_usuarios']; ?></div>
                            <div class="stat-label">Usuários Cadastrados</div>
                        </div>
                    </div>
                </section>
                
                <section id="artigos-pendentes" class="content-card">
                    <h2><i class="fas fa-clock"></i> Artigos Pendentes de Aprovação</h2>
                    
                    <?php if (count($artigos_pendentes) > 0): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Autor</th>
                                        <th>Data de Envio</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($artigos_pendentes as $artigo): ?>
                                        <tr>
                                            <td><a href="visualizar-artigo.php?id=<?php echo $artigo['id']; ?>" target="_blank"><?php echo htmlspecialchars($artigo['titulo']); ?></a></td>
                                            <td><?php echo htmlspecialchars($artigo['categoria']); ?></td>
                                            <td><?php echo htmlspecialchars($artigo['nome']); ?> (<?php echo htmlspecialchars($artigo['email']); ?>)</td>
                                            <td><?php echo date("d/m/Y H:i", strtotime($artigo['data_criacao'])); ?></td>
                                            <td>
                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?php echo $artigo['id']; ?>">
                                                    <input type="hidden" name="action" value="aprovar">
                                                    <button type="submit" class="btn btn-sm btn-approve"><i class="fas fa-check"></i> Aprovar</button>
                                                </form>
                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline; margin-left: 5px;">
                                                    <input type="hidden" name="id" value="<?php echo $artigo['id']; ?>">
                                                    <input type="hidden" name="action" value="rejeitar">
                                                    <button type="submit" class="btn btn-sm btn-reject"><i class="fas fa-times"></i> Rejeitar</button>
                                                </form>
                                                <a href="editar-admin-artigo.php?id=<?php echo $artigo['id']; ?>" class="btn btn-sm btn-secondary" style="margin-left: 5px;"><i class="fas fa-edit"></i> Editar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Não há artigos pendentes de aprovação no momento.</p>
                    <?php endif; ?>
                </section>
                
                <section id="artigos-publicados" class="content-card">
                    <h2><i class="fas fa-check-circle"></i> Artigos Publicados</h2>
                    
                    <?php if (count($artigos_aprovados) > 0): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Autor</th>
                                        <th>Data de Publicação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($artigos_aprovados as $artigo): ?>
                                        <tr>
                                            <td><a href="artigo.php?id=<?php echo $artigo['id']; ?>" target="_blank"><?php echo htmlspecialchars($artigo['titulo']); ?></a></td>
                                            <td><?php echo htmlspecialchars($artigo['categoria']); ?></td>
                                            <td><?php echo htmlspecialchars($artigo['nome']); ?></td>
                                            <td><?php echo date("d/m/Y H:i", strtotime($artigo['data_publicacao'])); ?></td>
                                            <td>
                                                <a href="editar-admin-artigo.php?id=<?php echo $artigo['id']; ?>" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i> Editar</a>
                                                <a href="visualizar-artigo.php?id=<?php echo $artigo['id']; ?>" class="btn btn-sm btn-primary" style="margin-left: 5px;"><i class="fas fa-eye"></i> Visualizar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Não há artigos publicados no momento.</p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
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
                    <li><a href="index.html">Início</a></li>
                    <li><a href="artigos.html">Artigos</a></li>
                    <li><a href="sobre.html">Sobre</a></li>
                    <li><a href="escola.html">A Escola</a></li>
                    <li><a href="contato.html">Contato</a></li>
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
    <script>
        // Rolagem suave para as seções
        document.querySelectorAll('.sidebar-menu a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                // Remover classe active de todos os links
                document.querySelectorAll('.sidebar-menu a').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Adicionar classe active ao link clicado
                this.classList.add('active');
                
                // Rolar suavemente para a seção
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>

<?php
// Fechar conexão
mysqli_close($conn);
?>
