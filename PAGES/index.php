<?php
// Iniciar a sessão para poder acessar as variáveis de sessão
session_start();

// Definir caminho base do projeto
define('BASE_PATH', dirname(dirname(__FILE__)) . '/');

// Incluir arquivo de configuração para conexão com o banco de dados
require_once BASE_PATH . "backend/config.php";
?>
<?php
// Incluir arquivo com funções do cabeçalho
require_once __DIR__ . '/includes/cabecalho_helper.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EntreLinhas - Jornal Digital</title>
    <meta name="description" content="EntreLinhas - Jornal digital colaborativo com notícias, artigos e textos da comunidade.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/PAGES') . '/assets/images/jornal.png'; ?>">
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
    <script src="../assets/js/user-menu.js" defer></script>
    <script src="../assets/js/theme.js" defer></script>
</head>
<body>

    <?php 
    // Gerar o cabeçalho com o menu
    echo gerar_cabecalho($conn, 'index.php');
    ?>

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
        <!-- Seção de Tirinhas -->
        <section class="tirinhas mb-5">
            <h2 class="mb-3">Tirinhas</h2>
            <div class="articles">
                <!-- Tirinha 1 -->
                <article class="article-card fade-in">
                    <div class="image-container">
                        <img src="../assets/images/tirinhas/TirinhaAfrodite.png" alt="Tirinha Afrodite" class="article-image" title="Clique para ampliar">
                    </div>
                    <div class="article-content">
                        <h3>Afrodite</h3>
                    </div>
                </article>
                
                <!-- Tirinha 2 -->
                <article class="article-card fade-in">
                    <div class="image-container">
                        <img src="../assets/images/tirinhas/TirinhaHeroiPorRoteiro 6ºB.png" alt="Tirinha Herói por Roteiro" class="article-image" title="Clique para ampliar">
                    </div>
                    <div class="article-content">
                        <h3>Herói por Roteiro - 6ºB</h3>
                    </div>
                </article>
                
                <!-- Tirinha 3 -->
                <article class="article-card fade-in">
                    <div class="image-container">
                        <img src="../assets/images/tirinhas/TirinhaImagemGabriel6ºA.png" alt="Tirinha Gabriel" class="article-image" title="Clique para ampliar">
                    </div>
                    <div class="article-content">
                        <h3>Gabriel - 6ºA</h3>
                    </div>
                </article>
            </div>
        </section>
        
        <!-- Artigo Principal sobre a Feira de Profissões -->
        <section class="featured-article mb-5">
            <h2 class="mb-3">Artigo em Destaque</h2>
            <article class="article-full fade-in">
                <div class="article-header">
                    <h3>SESI SALTO PROMOVE FEIRA DE PROFISSÕES 2025</h3>
                    <div class="article-meta">
                        <span>Educação</span>
                        <span>28/08/2025</span>
                    </div>
                </div>
                <div class="article-image-container">
                    <img src="../assets/images/artigos/f623caca-1049-4588-babc-48f8e30bb31f_page-0001.jpg" alt="Feira de Profissões SESI" class="article-image-full" title="Clique para ampliar">
                </div>
                <div class="article-body">
                    <p><em>Idealizada pela professora Nilceia Ragazzi, o evento promete oferecer aos educandos e educandas "um olhar para o amanhã".</em></p>
                    <p><strong>Por Ana Carolina Gatti, professora de Língua Portuguesa do SESI.</strong></p>
                    <p>Nos dias 4 e 5 de setembro, o Centro Educacional SESI da cidade de Salto realizará a 7ª Feira de Profissões da unidade. O evento, que ocorrerá das 8h às 12h, é organizado anualmente com o objetivo de apoiar os estudantes na escolha de seu futuro profissional, um momento decisivo no projeto de vida.</p>
                    <p>Nilceia Ragazzi, docente da área de Geografia, em colaboração com os demais professores, é responsável por esse projeto que se tornou uma tradição no calendário institucional. Aguardada pela comunidade escolar, a feira promove orientação vocacional e de carreira, além de proporcionar a interação dos estudantes com instituições de ensino da região e profissionais das áreas de interesse. Neste ano, o evento ocorrerá em dois dias e contará com a participação de faculdades públicas e privadas, que apresentarão projetos, cursos e possibilidades de carreira.</p>
                    <p>Além dessas iniciativas, os alunos e alunas do SESI Salto poderão participar de atividades práticas, dinâmicas interativas e palestras inspiradoras. Em 2025, a feira contará com a participação de egressas da unidade, profissionais da Faculdade SENAI e palestras de encerramento: na quinta-feira, Elaine Fidêncio (RH e Gestão de Pessoas) apresentará "Você no protagonismo: construa o seu caminho"; e, na sexta-feira, Francisco Petros discorrerá sobre "Os desafios da política brasileira frente ao mercado financeiro mundial".</p>
                    <p>A Feira é um momento de notável importância para os jovens, por oferecer a oportunidade de conhecerem diferentes áreas de atuação, sanar dúvidas com especialistas e fomentar a autopercepção. Visando a promoção da reflexão sobre interesses e talentos individuais, o convite está aberto aos estudantes, e a participação de todos é fundamental para tornar o evento significativo na trajetória profissional e acadêmica de cada um deles.</p>
                    <p>As inscrições estão abertas até o dia 29/08, no link abaixo: <a href="https://forms.gle/QabJ6Q3SFUNy5LQ4A" target="_blank">https://forms.gle/QabJ6Q3SFUNy5LQ4A</a></p>
                </div>
            </article>
        </section>
        

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

        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/image-viewer.js"></script>

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

</body>
</html>
