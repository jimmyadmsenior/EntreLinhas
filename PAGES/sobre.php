<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$page_title = "Sobre - EntreLinhas";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="Conheça a história e a equipe do EntreLinhas, o jornal escolar do 3º Ano 2025.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user-menu.css">
    <link rel="stylesheet" href="../assets/css/alerts.css">
    <style>
        .carousel-container {
            position: relative;
            max-width: 900px;
            margin: 0 auto 32px auto;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: var(--shadow-light);
        }
        .carousel-track { width: 100%; aspect-ratio: 16/9; position: relative; overflow: hidden; }
        .carousel-img { position: absolute; left: 0; top: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.7s ease; }
        .carousel-img.active { opacity: 1; z-index: 2; }
        .carousel-progress-bar { width: 100%; height: 3px; background-color: var(--border-light); border-radius: 2px; margin-top: 10px; overflow: hidden; }
        .carousel-progress { height: 100%; background-color: var(--primary); width: 0%; border-radius: 2px; transition: width 0.5s ease; }
        .carousel-caption { width: 100%; text-align: center; font-size: 1.1em; color: var(--primary); font-weight: 500; margin-top: 12px; min-height: 1.5em; }
        .section-text { max-width: 900px; margin: 0 auto 32px auto; font-size: 1.15rem; line-height: 1.7; }
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
                <li><a href="artigos.php">Artigos</a></li>
                <li><a href="sobre.php" class="active">Sobre</a></li>
                <li><a href="escola.php">A Escola</a></li>
                <li><a href="contato.php">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <?php if ($usuario_logado): ?>
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

    <!-- Main Content -->
    <main class="container fade-in">
        <h1>EntreLinhas - Jornal Escolar do 3º Ano 2025</h1>

        <section class="section-text">
            <p>
                O <strong>EntreLinhas</strong> nasceu como um projeto inovador da turma do 3º ano do ensino médio de 2025. Inicialmente, produzido em formato impresso, o jornal se tornou uma ferramenta essencial para que os estudantes compartilhassem notícias, histórias e opiniões sobre o cotidiano escolar. A cada edição, os alunos desenvolvem habilidades de escrita, pesquisa e edição, garantindo que cada página conte uma história autêntica da escola.
            </p>
            <p>
                Com o avanço da tecnologia e a necessidade de alcançar toda a comunidade escolar, o EntreLinhas migrou para o formato digital. Hoje, o jornal está disponível online, permitindo que matérias, entrevistas e reportagens sejam acessadas de qualquer lugar, ampliando o impacto do trabalho dos alunos e valorizando o protagonismo estudantil.
            </p>
        </section>

        <section class="section-text">
            <h2>Por que o EntreLinhas é importante no último ano</h2>
            <p>
                Participar do EntreLinhas no último ano do ensino médio é uma experiência única. O projeto estimula o trabalho em equipe, o senso crítico e a criatividade dos estudantes, permitindo que cada um contribua com ideias, textos e reportagens. Além disso, os alunos aprendem a planejar conteúdos, lidar com prazos e organizar informações de forma clara e envolvente, habilidades fundamentais para o futuro acadêmico e profissional.
            </p>
            <p>
                Mais do que um jornal, o EntreLinhas fortalece a identidade da turma, criando memórias duradouras e consolidando a colaboração entre os colegas. Cada edição é fruto de dedicação e esforço, refletindo a realidade da escola e a diversidade de experiências de seus alunos.
            </p>
        </section>

        <section class="section-text">
            <h2>Conheça a Turma do 3º Ano 2025</h2>
            <p>
                Esta é a turma responsável por tornar o EntreLinhas realidade. Cada estudante contribuiu com criatividade, pesquisa e paixão, fazendo do jornal um registro fiel do cotidiano escolar.
            </p>

            <div class="carousel-container" id="carousel-jornal">
                <div class="carousel-track" tabindex="0">
                    <img src="../assets/images/turma-3ano-2025.jpg" alt="Foto da turma do 3º ano 2025" class="carousel-img active" data-caption="Turma do 3º ano - 2025, produtores do EntreLinhas" />
                </div>
                <div class="carousel-progress-bar">
                    <div class="carousel-progress"></div>
                </div>
                <div class="carousel-caption" id="carousel-caption-jornal"></div>
            </div>
        </section>

        <section class="section-text">
            <h2>O EntreLinhas Hoje</h2>
            <p>
                Atualmente, o EntreLinhas é um portal digital completo, com matérias, entrevistas e conteúdos exclusivos produzidos pelos alunos. O jornal online garante maior interação com a comunidade escolar, tornando o conhecimento acessível a todos. A evolução do projeto mostra como iniciativas colaborativas podem transformar a experiência educacional, preparando os estudantes para desafios futuros.
            </p>
            <p>
                O EntreLinhas é mais do que um projeto escolar: é um registro histórico da vida acadêmica, da cultura da escola e da criatividade dos alunos, reforçando a importância de desenvolver projetos colaborativos no último ano do ensino médio.
            </p>
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
    <script src="../assets/js/user-menu.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar o conteúdo da legenda
            const carousel = document.getElementById('carousel-jornal');
            if (carousel) {
                const img = carousel.querySelector('.carousel-img');
                const caption = document.getElementById('carousel-caption-jornal');
                if (img && caption) {
                    caption.textContent = img.getAttribute('data-caption') || img.alt || '';
                }
            }
            
            // Configurar o menu dropdown do usuário
            const userMenu = document.querySelector('.user-menu');
            if (userMenu) {
                const dropdownMenu = document.getElementById('user-dropdown-menu');
                userMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });
                
                document.addEventListener('click', function() {
                    dropdownMenu.classList.remove('show');
                });
            }
        });
    </script>
    
    <!-- JavaScript -->
    <script src="../assets/js/auth-cookies.js"></script>
    <script src="../assets/js/verificar-sincronizar-login.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/user-menu.js"></script>
    <script src="../assets/js/dropdown-menu.js"></script>
    <script src="../assets/js/header-nav.js"></script>
</body>
</html>
