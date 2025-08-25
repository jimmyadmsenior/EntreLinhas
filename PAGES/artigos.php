<?php
// Iniciar a sessão para poder acessar as variáveis de sessão
session_start();

// Incluir arquivo de configuração para conexão com o banco de dados
require_once "../backend/config.php";

// Processar filtros
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'recentes';
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 9;
$inicio = ($pagina - 1) * $por_pagina;

// Construir a consulta SQL com os filtros
$where_clause = "WHERE a.status = 'aprovado'";
if (!empty($categoria)) {
    $where_clause .= " AND a.titulo LIKE '%" . mysqli_real_escape_string($conn, $categoria) . "%'";
}
if (!empty($busca)) {
    $where_clause .= " AND (a.titulo LIKE '%" . mysqli_real_escape_string($conn, $busca) . "%' OR a.conteudo LIKE '%" . mysqli_real_escape_string($conn, $busca) . "%' OR a.resumo LIKE '%" . mysqli_real_escape_string($conn, $busca) . "%')";
}

// Determinar a ordenação
$order_clause = "ORDER BY a.data_criacao DESC";
if ($ordem == 'antigos') {
    $order_clause = "ORDER BY a.data_criacao ASC";
} elseif ($ordem == 'titulo') {
    $order_clause = "ORDER BY a.titulo ASC";
} elseif ($ordem == 'comentarios') {
    $order_clause = "ORDER BY comentarios DESC";
}

// Consultar o número total de artigos com os filtros aplicados
$count_sql = "SELECT COUNT(*) as total 
              FROM artigos a 
              $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_paginas = ceil($total_rows / $por_pagina);

// Consultar os artigos com os filtros aplicados

$sql = "SELECT a.id, a.titulo, LEFT(a.conteudo, 150) as resumo, a.data_criacao as data_publicacao, 
        a.categoria, a.imagem as imagem_capa, u.nome as autor, 
        (SELECT COUNT(*) FROM comentarios c WHERE c.id_artigo = a.id) as comentarios 
        FROM artigos a 
        JOIN usuarios u ON a.id_usuario = u.id 

        $where_clause 
        $order_clause 
        LIMIT $inicio, $por_pagina";
$result = mysqli_query($conn, $sql);

// Consultar categorias para o filtro
$categorias_sql = "SELECT DISTINCT 'Artigo' as categoria FROM artigos WHERE status = 'aprovado' ORDER BY 1";
$categorias_result = mysqli_query($conn, $categorias_sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos - EntreLinhas</title>
    <meta name="description" content="Leia os mais recentes artigos publicados no jornal EntreLinhas.">
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
        .filters {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: var(--bg-light);
            border-radius: 8px;
            box-shadow: var(--shadow-light);
            transition: background-color var(--transition-speed), box-shadow var(--transition-speed);
        }
        
        .dark-mode .filters {
            background-color: var(--card-bg-dark);
            box-shadow: var(--shadow-dark);
        }
        
        .filters-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .filter-group select,
        .filter-group input[type="text"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text-color);
            font-size: 1rem;
        }
        
        .filter-group select:focus,
        .filter-group input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .filter-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .filter-buttons button {
            padding: 0.5rem 1rem;
        }
        
        .filter-info {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            list-style-type: none;
            padding: 0;
        }
        
        .pagination li {
            margin: 0 0.25rem;
        }
        
        .pagination a,
        .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            text-decoration: none;
            color: var(--text-color);
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .pagination a:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .pagination .active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .pagination .disabled {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="../index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="../index.php">Início</a></li>
                <li><a href="artigos.php" class="active">Artigos</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="escola.php">A Escola</a></li>
                <li><a href="contato.php">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <!-- Menu dropdown do usuário -->
                    <div class="user-menu">
                        <div class="user-name">
                            <span class="avatar-container">
                                <?php 
                                // Tentar obter a foto de perfil
                                $foto_perfil = null;
                                if (isset($conn)) {
                                    // Carregar helper se ainda não estiver carregado
                                    if (!function_exists('obter_foto_perfil')) {
                                        require_once dirname(__FILE__) . "/../backend/usuario_helper.php";
                                    }
                                    
                                    // Obter foto de perfil
                                    if (function_exists('obter_foto_perfil')) {
                                        $foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);
                                    }
                                }
                                
                                if ($foto_perfil): 
                                ?>
                                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" class="user-avatar">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </span>
                            <?php echo htmlspecialchars($_SESSION["nome"]); ?> <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu" id="user-dropdown-menu">
                            <a href="perfil.php" class="dropdown-link"><i class="fas fa-id-card"></i> Meu Perfil</a>
                            <a href="meus-artigos.php" class="dropdown-link"><i class="fas fa-newspaper"></i> Meus Artigos</a>
                            <a href="enviar-artigo.php" class="dropdown-link"><i class="fas fa-edit"></i> Enviar Artigo</a>
                            <?php if (isset($_SESSION["tipo"]) && $_SESSION["tipo"] === 'admin'): ?>
                                <a href="admin_dashboard.php" class="dropdown-link"><i class="fas fa-cogs"></i> Painel de Admin</a>
                            <?php endif; ?>
                            <a href="logout.php" class="dropdown-link"><i class="fas fa-sign-out-alt"></i> Sair</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Links de login e cadastro -->
                    <a href="login.php" class="btn btn-secondary">Entrar</a>
                    <a href="registro.php" class="btn btn-primary">Cadastrar</a>
                <?php endif; ?>
                
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar modo escuro">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>


    <main>
        <section class="articles-section">
            <div class="container">
                <div class="section-header">
                    <h1>Artigos</h1>
                    <p>Explore os artigos publicados no EntreLinhas</p>
                </div>
                
                <div class="filters">
                    <form action="artigos.php" method="GET">
                        <div class="filters-row">
                            <div class="filter-group">
                                <label for="categoria">Categoria</label>
                                <select id="categoria" name="categoria">
                                    <option value="">Todas as categorias</option>
                                    <?php 
                                    while ($cat = mysqli_fetch_assoc($categorias_result)) {
                                        $selected = ($categoria == $cat['categoria']) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($cat['categoria']) . '" ' . $selected . '>' . htmlspecialchars($cat['categoria']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="ordem">Ordenar por</label>
                                <select id="ordem" name="ordem">
                                    <option value="recentes" <?php if ($ordem == 'recentes') echo 'selected'; ?>>Mais recentes</option>
                                    <option value="antigos" <?php if ($ordem == 'antigos') echo 'selected'; ?>>Mais antigos</option>
                                    <option value="titulo" <?php if ($ordem == 'titulo') echo 'selected'; ?>>Título (A-Z)</option>
                                    <option value="comentarios" <?php if ($ordem == 'comentarios') echo 'selected'; ?>>Mais comentados</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="busca">Buscar</label>
                                <input type="text" id="busca" name="busca" placeholder="Pesquisar artigos..." value="<?php echo htmlspecialchars($busca); ?>">
                            </div>
                            
                            <div class="filter-group filter-buttons">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="artigos.php" class="btn btn-secondary">Limpar filtros</a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="filter-info">
                        <?php 
                        echo "Exibindo $total_rows artigo(s) ";
                        if (!empty($categoria) || !empty($busca)) {
                            echo "filtrado(s) ";
                            if (!empty($categoria)) {
                                echo "por categoria \"" . htmlspecialchars($categoria) . "\" ";
                            }
                            if (!empty($busca)) {
                                echo "contendo \"" . htmlspecialchars($busca) . "\" ";
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="articles-grid">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($artigo = mysqli_fetch_assoc($result)) {
                            // Formatar a data
                            $data = date("d/m/Y", strtotime($artigo['data_publicacao']));
                            
                            // Imagem padrão se não houver imagem
                            $imagem = !empty($artigo['imagem_capa']) 
                                    ? $artigo['imagem_capa'] 
                                    : "../assets/images/jornal.png";
                                    
                            echo '
                            <article class="article-card">
                                <div class="article-img">
                                    <img src="' . htmlspecialchars($imagem) . '" alt="' . htmlspecialchars($artigo['titulo']) . '">
                                    <span class="category">' . htmlspecialchars($artigo['categoria']) . '</span>
                                </div>
                                <div class="article-content">
                                    <h3><a href="artigo.php?id=' . $artigo['id'] . '">' . htmlspecialchars($artigo['titulo']) . '</a></h3>
                                    <p class="article-meta">
                                        <span class="author">Por ' . htmlspecialchars($artigo['autor']) . '</span>
                                        <span class="date">' . $data . '</span>
                                        <span class="comments"><i class="fas fa-comment"></i> ' . $artigo['comentarios'] . '</span>
                                    </p>
                                    <p class="excerpt">' . htmlspecialchars($artigo['resumo']) . '</p>
                                    <a href="artigo.php?id=' . $artigo['id'] . '" class="read-more">Ler mais</a>
                                </div>
                            </article>';
                        }
                    } else {
                        echo '<div class="no-articles"><p>Nenhum artigo encontrado com os filtros aplicados.</p></div>';
                    }
                    ?>
                </div>
                
                <?php if ($total_paginas > 1): ?>
                <ul class="pagination">
                    <?php if ($pagina > 1): ?>
                        <li><a href="?pagina=1<?php echo !empty($categoria) ? "&categoria=$categoria" : ""; echo !empty($busca) ? "&busca=$busca" : ""; echo !empty($ordem) ? "&ordem=$ordem" : ""; ?>"><i class="fas fa-angle-double-left"></i></a></li>
                        <li><a href="?pagina=<?php echo $pagina-1; echo !empty($categoria) ? "&categoria=$categoria" : ""; echo !empty($busca) ? "&busca=$busca" : ""; echo !empty($ordem) ? "&ordem=$ordem" : ""; ?>"><i class="fas fa-angle-left"></i></a></li>
                    <?php else: ?>
                        <li class="disabled"><span><i class="fas fa-angle-double-left"></i></span></li>
                        <li class="disabled"><span><i class="fas fa-angle-left"></i></span></li>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $pagina - 2);
                    $end = min($start + 4, $total_paginas);
                    $start = max(1, $end - 4);
                    
                    for ($i = $start; $i <= $end; $i++) {
                        if ($i == $pagina) {
                            echo '<li><span class="active">' . $i . '</span></li>';
                        } else {
                            echo '<li><a href="?pagina=' . $i . (!empty($categoria) ? "&categoria=$categoria" : "") . (!empty($busca) ? "&busca=$busca" : "") . (!empty($ordem) ? "&ordem=$ordem" : "") . '">' . $i . '</a></li>';
                        }
                    }
                    ?>
                    
                    <?php if ($pagina < $total_paginas): ?>
                        <li><a href="?pagina=<?php echo $pagina+1; echo !empty($categoria) ? "&categoria=$categoria" : ""; echo !empty($busca) ? "&busca=$busca" : ""; echo !empty($ordem) ? "&ordem=$ordem" : ""; ?>"><i class="fas fa-angle-right"></i></a></li>
                        <li><a href="?pagina=<?php echo $total_paginas; echo !empty($categoria) ? "&categoria=$categoria" : ""; echo !empty($busca) ? "&busca=$busca" : ""; echo !empty($ordem) ? "&ordem=$ordem" : ""; ?>"><i class="fas fa-angle-double-right"></i></a></li>
                    <?php else: ?>
                        <li class="disabled"><span><i class="fas fa-angle-right"></i></span></li>
                        <li class="disabled"><span><i class="fas fa-angle-double-right"></i></span></li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->

    <script src="../assets/js/auth-cookies.js"></script>
    <script src="../assets/js/verificar-sincronizar-login.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/user-menu.js"></script>
    <script src="../assets/js/dropdown-menu.js"></script>
    <script src="../assets/js/header-nav.js"></script>

</body>
</html>
