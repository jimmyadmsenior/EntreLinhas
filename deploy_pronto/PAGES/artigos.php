<?php
// Iniciar a sessão para poder acessar as variáveis de sessão
session_start();

// Incluir arquivo de configuração PDO para conexão com o banco de dados
require_once "../config_pdo.php";
require_once "../pdo_helper.php";

// Processar filtros
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'recentes';
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 9;
$inicio = ($pagina - 1) * $por_pagina;

// Construir a consulta SQL com os filtros
$where_clause = "WHERE a.status = 'aprovado'";
$params = []; // Array para parâmetros PDO

if (!empty($categoria)) {
    $where_clause .= " AND a.titulo LIKE ?";
    $params[] = "%" . $categoria . "%";
}
if (!empty($busca)) {
    $where_clause .= " AND (a.titulo LIKE ? OR a.conteudo LIKE ? OR a.resumo LIKE ?)";
    $params[] = "%" . $busca . "%";
    $params[] = "%" . $busca . "%";
    $params[] = "%" . $busca . "%";
}

// Determinar a ordenação
$order_clause = "ORDER BY a.data_criacao DESC";
if ($ordem == 'antigos') {
    $order_clause = "ORDER BY a.data_criacao ASC";
} elseif ($ordem == 'titulo') {
    $order_clause = "ORDER BY a.titulo ASC";
} elseif ($ordem == 'popularidade') {
    $order_clause = "ORDER BY visualizacoes DESC";
}

// Consulta para contar o total de artigos (para paginação)
$sql_count = "SELECT COUNT(*) as total FROM artigos a {$where_clause}";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_artigos = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

// Calcular o número total de páginas
$total_paginas = ceil($total_artigos / $por_pagina);

// Garantir que a página atual está dentro dos limites
$pagina = max(1, min($pagina, $total_paginas));
$inicio = ($pagina - 1) * $por_pagina;

// Consulta para obter os artigos da página atual
$sql_artigos = "SELECT a.*, 
                u.nome as autor_nome, 
                u.id as autor_id,
                (SELECT COUNT(*) FROM comentarios c WHERE c.id_artigo = a.id) as total_comentarios,
                (SELECT imagem_base64 FROM imagens_artigos WHERE id_artigo = a.id ORDER BY ordem ASC LIMIT 1) as imagem_principal
                FROM artigos a 
                LEFT JOIN usuarios u ON a.id_autor = u.id 
                {$where_clause} 
                {$order_clause}
                LIMIT {$inicio}, {$por_pagina}";

try {
    $stmt_artigos = $pdo->prepare($sql_artigos);
    $stmt_artigos->execute($params);
    $artigos = $stmt_artigos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar artigos: " . $e->getMessage();
    $artigos = [];
}

// Consulta para obter todas as categorias para o filtro
$sql_categorias = "SELECT DISTINCT categoria FROM artigos WHERE status = 'aprovado' ORDER BY categoria";
try {
    $stmt_categorias = $pdo->query($sql_categorias);
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    echo "Erro ao buscar categorias: " . $e->getMessage();
    $categorias = [];
}

// Incluir arquivo com funções do cabeçalho versão PDO
require_once 'includes/cabecalho_helper_pdo.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos - EntreLinhas</title>
    <meta name="description" content="Explore nossa coleção de artigos no EntreLinhas.">
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
    <link rel="stylesheet" href="../assets/css/articles.css">
    <link rel="stylesheet" href="../assets/css/user-menu.css">
    <style>
        .filter-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            color: var(--primary);
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .pagination a:hover {
            background-color: #f1f1f1;
        }
        .pagination .active {
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }
        .pagination .disabled {
            color: #ddd;
            pointer-events: none;
        }
        .no-results {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <?php echo gerar_cabecalho($pdo, 'artigos.php'); ?>

    <!-- Main Content -->
    <main class="main-content container">
        <h1 class="page-title">Artigos</h1>
        
        <!-- Filtros -->
        <div class="filter-container">
            <form class="filter-form" method="get">
                <div class="filter-group">
                    <label for="categoria">Categoria</label>
                    <select name="categoria" id="categoria" class="form-control">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoria === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="ordem">Ordenar por</label>
                    <select name="ordem" id="ordem" class="form-control">
                        <option value="recentes" <?php echo $ordem === 'recentes' ? 'selected' : ''; ?>>Mais recentes</option>
                        <option value="antigos" <?php echo $ordem === 'antigos' ? 'selected' : ''; ?>>Mais antigos</option>
                        <option value="titulo" <?php echo $ordem === 'titulo' ? 'selected' : ''; ?>>Título (A-Z)</option>
                        <option value="popularidade" <?php echo $ordem === 'popularidade' ? 'selected' : ''; ?>>Popularidade</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="busca">Buscar</label>
                    <input type="text" name="busca" id="busca" class="form-control" placeholder="Pesquisar artigos..." value="<?php echo htmlspecialchars($busca); ?>">
                </div>
                
                <div class="filter-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <?php if (!empty($categoria) || !empty($busca) || $ordem !== 'recentes'): ?>
                        <a href="artigos.php" class="btn btn-outline" style="margin-left: 10px;">Limpar filtros</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <?php if (count($artigos) > 0): ?>
            <!-- Lista de Artigos -->
            <div class="articles">
                <?php foreach ($artigos as $artigo): ?>
                    <article class="article-card fade-in">
                        <a href="artigo.php?id=<?php echo $artigo['id']; ?>" class="article-link">
                            <div class="image-container">
                                <?php if (!empty($artigo['imagem_principal'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo $artigo['imagem_principal']; ?>" alt="<?php echo htmlspecialchars($artigo['titulo']); ?>" class="article-image">
                                <?php else: ?>
                                    <img src="../assets/images/artigo-default.jpg" alt="Imagem padrão" class="article-image">
                                <?php endif; ?>
                            </div>
                            <div class="article-content">
                                <h2><?php echo htmlspecialchars($artigo['titulo']); ?></h2>
                                <p class="article-excerpt"><?php echo htmlspecialchars(substr($artigo['resumo'], 0, 100)) . '...'; ?></p>
                                <div class="article-meta">
                                    <span class="article-author">Por <?php echo htmlspecialchars($artigo['autor_nome']); ?></span>
                                    <span class="article-date"><?php echo date('d/m/Y', strtotime($artigo['data_criacao'])); ?></span>
                                </div>
                                <div class="article-stats">
                                    <span class="article-views"><i class="fas fa-eye"></i> <?php echo $artigo['visualizacoes']; ?></span>
                                    <span class="article-comments"><i class="fas fa-comment"></i> <?php echo $artigo['total_comentarios']; ?></span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
                <div class="pagination">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=1<?php echo !empty($categoria) ? '&categoria=' . urlencode($categoria) : ''; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?><?php echo '&ordem=' . urlencode($ordem); ?>">&laquo; Primeira</a>
                        <a href="?pagina=<?php echo $pagina - 1; ?><?php echo !empty($categoria) ? '&categoria=' . urlencode($categoria) : ''; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?><?php echo '&ordem=' . urlencode($ordem); ?>">&lsaquo; Anterior</a>
                    <?php else: ?>
                        <span class="disabled">&laquo; Primeira</span>
                        <span class="disabled">&lsaquo; Anterior</span>
                    <?php endif; ?>
                    
                    <?php
                    // Determinar quais páginas mostrar
                    $inicio_pag = max(1, $pagina - 2);
                    $fim_pag = min($total_paginas, $pagina + 2);
                    
                    // Garantir que mostramos pelo menos 5 botões de página se possível
                    if ($fim_pag - $inicio_pag < 4) {
                        if ($inicio_pag == 1) {
                            $fim_pag = min($total_paginas, 5);
                        } elseif ($fim_pag == $total_paginas) {
                            $inicio_pag = max(1, $total_paginas - 4);
                        }
                    }
                    
                    // Mostrar as páginas
                    for ($i = $inicio_pag; $i <= $fim_pag; $i++) {
                        if ($i == $pagina) {
                            echo '<span class="active">' . $i . '</span>';
                        } else {
                            echo '<a href="?pagina=' . $i . (!empty($categoria) ? '&categoria=' . urlencode($categoria) : '') . (!empty($busca) ? '&busca=' . urlencode($busca) : '') . '&ordem=' . urlencode($ordem) . '">' . $i . '</a>';
                        }
                    }
                    ?>
                    
                    <?php if ($pagina < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina + 1; ?><?php echo !empty($categoria) ? '&categoria=' . urlencode($categoria) : ''; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?><?php echo '&ordem=' . urlencode($ordem); ?>">Próxima &rsaquo;</a>
                        <a href="?pagina=<?php echo $total_paginas; ?><?php echo !empty($categoria) ? '&categoria=' . urlencode($categoria) : ''; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?><?php echo '&ordem=' . urlencode($ordem); ?>">Última &raquo;</a>
                    <?php else: ?>
                        <span class="disabled">Próxima &rsaquo;</span>
                        <span class="disabled">Última &raquo;</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Mensagem quando não há resultados -->
            <div class="no-results">
                <h2>Nenhum artigo encontrado</h2>
                <p>Não encontramos artigos que correspondam aos seus critérios de busca.</p>
                <p>Tente ajustar os filtros ou <a href="artigos.php">veja todos os artigos</a>.</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar componentes
            const filterForm = document.querySelector('.filter-form');
            
            // Função para auto-submit do formulário quando os selects mudam
            const categoria = document.getElementById('categoria');
            const ordem = document.getElementById('ordem');
            
            if (categoria && ordem) {
                [categoria, ordem].forEach(element => {
                    element.addEventListener('change', function() {
                        filterForm.submit();
                    });
                });
            }
        });
    </script>
</body>
</html>
