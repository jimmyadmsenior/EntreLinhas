<?php
// Iniciar a sessão para poder acessar as variáveis de sessão
session_start();

<<<<<<< Updated upstream
// Incluir apenas o arquivo de configuração, sem outras dependências
=======
// Incluir arquivo de configuração para conexão com o banco de dados
>>>>>>> Stashed changes
require_once "../backend/config.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EntreLinhas - Jornal Digital</title>
    <meta name="description" content="EntreLinhas - Jornal digital colaborativo com notícias, artigos e textos da comunidade.">
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
    <link rel="stylesheet" href="../assets/css/alerts.css">
</head>
<body>
<<<<<<< Updated upstream
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.php" class="active">Início</a></li>
                <li><a href="artigos.php">Artigos</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Bem-vindo ao EntreLinhas</h1>
            <p>Um jornal digital colaborativo onde vozes diversas se encontram para compartilhar conhecimento, histórias e experiências.</p>
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <a href="enviar-artigo.php" class="btn btn-primary">Compartilhe sua história</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Compartilhe sua história</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content container">
        <section class="featured-articles mb-5">
            <h2 class="mb-3">Artigos em Destaque</h2>
            <div class="articles">
                <?php
                // Consultar os 6 artigos mais recentes aprovados
                $sql = "SELECT a.id, a.titulo, LEFT(a.conteudo, 150) as resumo, a.data_criacao as data_publicacao, 
                        IFNULL(a.categoria, 'Geral') as categoria, IFNULL(a.imagem, '') as imagem_capa, u.nome as autor 
                        FROM artigos a 
                        JOIN usuarios u ON a.id_usuario = u.id 
                        WHERE a.status = 'aprovado' 
                        ORDER BY a.data_criacao DESC 
                        LIMIT 3";
=======
    <?php include 'includes/header.php'; ?>

    <!-- Conteúdo Principal -->
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>EntreLinhas</h1>
                <p class="subtitle">Jornal Digital SESI</p>
                <p class="description">Notícias, artigos e textos da nossa comunidade escolar</p>
                <div class="cta-buttons">
                    <a href="artigos.php" class="btn btn-primary">Ver Artigos</a>
                    <a href="conhecimentos.html" class="btn btn-secondary">Sobre o Projeto</a>
                </div>
            </div>
        </section>

        <!-- Artigos em Destaque -->
        <section class="featured-articles">
            <div class="section-header">
                <h2>Artigos em Destaque</h2>
                <a href="artigos.php" class="view-all">Ver todos</a>
            </div>
            <div class="articles-grid">
                <?php
                // Consultar os 6 artigos mais recentes aprovados
                $sql = "SELECT a.id, a.titulo, a.resumo, a.data_criacao as data_publicacao, 
                        'Geral' as categoria, '' as imagem_capa, u.nome as autor 
                        FROM artigos a 
                        JOIN usuarios u ON a.usuario_id = u.id 
                        WHERE a.status = 'aprovado' 
                        ORDER BY a.data_criacao DESC 
                        LIMIT 6";
>>>>>>> Stashed changes
                $result = mysqli_query($conn, $sql);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($artigo = mysqli_fetch_assoc($result)) {
                        // Formatar a data
                        $data = date("d/m/Y", strtotime($artigo['data_publicacao']));
                        
                        // Imagem padrão se não houver imagem
                        $imagem = !empty($artigo['imagem_capa']) 
                                ? $artigo['imagem_capa'] 
                                : "../assets/images/jornal.png";
                                
                        echo '
<<<<<<< Updated upstream
                        <article class="article-card fade-in">
                            <img src="' . htmlspecialchars($imagem) . '" alt="' . htmlspecialchars($artigo['titulo']) . '" class="article-image">
                            <div class="article-content">
                                <div class="article-meta">
                                    <span>' . htmlspecialchars($artigo['categoria']) . '</span>
                                    <span>' . $data . '</span>
                                </div>
                                <h3>' . htmlspecialchars($artigo['titulo']) . '</h3>
                                <p>' . htmlspecialchars(strip_tags(substr($artigo['resumo'], 0, 100))) . '...</p>
                                <a href="artigo.php?id=' . $artigo['id'] . '" class="read-more">Leia mais</a>
=======
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
                                </p>
                                <p class="excerpt">' . htmlspecialchars($artigo['resumo']) . '</p>
                                <a href="artigo.php?id=' . $artigo['id'] . '" class="read-more">Ler mais</a>
>>>>>>> Stashed changes
                            </div>
                        </article>';
                    }
                } else {
<<<<<<< Updated upstream
                    // Se não houver artigos, exibir artigos de exemplo como no HTML original
                    echo '
                    <!-- Artigo 1 -->
                    <article class="article-card fade-in">
                        <img src="../assets/images/Sesi-Biblioteca.png" alt="Biblioteca da escola" class="article-image">
                        <div class="article-content">
                            <div class="article-meta">
                                <span>Educação</span>
                                <span>15/08/2025</span>
                            </div>
                            <h3>A importância da leitura no desenvolvimento escolar</h3>
                            <p>Descubra como o hábito da leitura pode transformar o aprendizado e abrir novas portas para o conhecimento.</p>
                            <a href="#" class="read-more">Leia mais</a>
                        </div>
                    </article>
                    
                    <!-- Artigo 2 -->
                    <article class="article-card fade-in">
                        <img src="../assets/images/escola-quadra.png" alt="Quadra poliesportiva" class="article-image">
                        <div class="article-content">
                            <div class="article-meta">
                                <span>Esporte</span>
                                <span>10/08/2025</span>
                            </div>
                            <h3>Esportes na escola: muito além da competição</h3>
                            <p>Como as atividades esportivas contribuem para o desenvolvimento físico, social e emocional dos estudantes.</p>
                            <a href="#" class="read-more">Leia mais</a>
                        </div>
                    </article>
                    
                    <!-- Artigo 3 -->
                    <article class="article-card fade-in">
                        <img src="../assets/images/Sesi-Vista.png" alt="Vista panorâmica da escola" class="article-image">
                        <div class="article-content">
                            <div class="article-meta">
                                <span>Comunidade</span>
                                <span>05/08/2025</span>
                            </div>
                            <h3>Nossa escola: um espaço de transformação social</h3>
                            <p>Como projetos educacionais estão impactando positivamente a comunidade ao redor da escola.</p>
                            <a href="#" class="read-more">Leia mais</a>
                        </div>
                    </article>';
=======
                    echo '<div class="no-articles"><p>Nenhum artigo publicado ainda.</p></div>';
>>>>>>> Stashed changes
                }
                ?>
            </div>
        </section>
        
<<<<<<< Updated upstream
        <section class="categories mb-5">
            <h2 class="mb-3">Categorias</h2>
            <div class="flex flex-wrap gap-2">
                <a href="artigos.php?categoria=Educacao" class="btn btn-secondary">Educação</a>
                <a href="artigos.php?categoria=Cultura" class="btn btn-secondary">Cultura</a>
                <a href="artigos.php?categoria=Esporte" class="btn btn-secondary">Esporte</a>
                <a href="artigos.php?categoria=Tecnologia" class="btn btn-secondary">Tecnologia</a>
                <a href="artigos.php?categoria=Comunidade" class="btn btn-secondary">Comunidade</a>
                <a href="artigos.php?categoria=Eventos" class="btn btn-secondary">Eventos</a>
            </div>
        </section>
        
        <section class="cta-section text-center mt-5 mb-5">
            <h2>Tem algo a compartilhar?</h2>
            <p>Faça parte do nosso jornal e compartilhe suas ideias, histórias e conhecimentos com a comunidade.</p>
            <div class="mt-3">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="enviar-artigo.php" class="btn btn-primary">Enviar um Artigo</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Entrar</a>
                    <a href="cadastro.php" class="btn btn-primary">Cadastre-se</a>
                <?php endif; ?>
            </div>
=======
        <!-- Sobre a Escola -->
        <section class="about-section">
            <div class="about-content">
                <h2>SESI Escola</h2>
                <p>A Escola SESI proporciona educação de qualidade com foco no desenvolvimento integral dos alunos, preparando-os para os desafios do século XXI. Nossa metodologia inovadora combina aprendizado teórico com prática, estimulando a criatividade e o pensamento crítico.</p>
                <a href="escola.php" class="btn btn-secondary">Conheça nossa escola</a>
            </div>
            <div class="about-image">
                <img src="../assets/images/escola-quadra.png" alt="Escola SESI">
            </div>
        </section>
        
        <!-- Call to Action -->
        <section class="cta-section">
            <h2>Compartilhe suas ideias</h2>
            <p>Tem algo a dizer? Uma história para contar? Um conhecimento para compartilhar?</p>
            <a href="enviar-artigo.php" class="btn btn-primary">Enviar um Artigo</a>
>>>>>>> Stashed changes
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
<<<<<<< Updated upstream
    <script src="../assets/js/debug.js"></script>
    <script src="../assets/js/user-menu.js"></script>
    <script src="../assets/js/header-nav.js"></script>
    <script src="../assets/js/auth-cookies.js"></script>
    <script>
        // Initialize home page
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof initHomePage === 'function') {
                initHomePage();
            }
            console.log('Página inicial carregada');
        });
    </script>
=======
>>>>>>> Stashed changes
</body>
</html>
