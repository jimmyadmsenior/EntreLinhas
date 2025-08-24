<?php
// Iniciar a sessão para poder acessar as variáveis de sessão
session_start();

// Incluir arquivo de configuração para conexão com o banco de dados
require_once "../backend/config.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A Escola - EntreLinhas</title>
    <meta name="description" content="Conheça a Escola SESI, onde o Jornal EntreLinhas é produzido.">
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
</head>
<body>
<<<<<<< Updated upstream
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="../index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="../index.php">Início</a></li>
                <li><a href="artigos.php">Artigos</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="escola.php" class="active">A Escola</a></li>
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
=======
    <?php include 'includes/header.php'; ?>
>>>>>>> Stashed changes
    
    <main>
        <section class="school-section">
            <div class="container">
                <div class="section-header">
                    <h1>A Escola SESI</h1>
                    <p>Conhecendo a nossa instituição</p>
                </div>
                
                <div class="school-intro">
                    <img src="../assets/images/Sesi-Vista.png" alt="Vista da Escola SESI" class="school-main-image">
                    <div class="school-intro-content">
                        <h2>Formando o futuro do Brasil</h2>
                        <p>A Escola SESI é uma instituição de ensino que oferece educação de qualidade, com foco no desenvolvimento integral dos alunos, preparando-os para os desafios do século XXI.</p>
                        <p>Com uma metodologia inovadora e professores altamente qualificados, a Escola SESI proporciona um ambiente de aprendizado estimulante, que combina teoria e prática, incentivando a criatividade, o pensamento crítico e a capacidade de resolução de problemas.</p>
                        <p>Nossa missão é formar cidadãos éticos, responsáveis e preparados para o mercado de trabalho, contribuindo para o desenvolvimento da sociedade brasileira.</p>
                    </div>
                </div>
                
                <div class="school-features">
                    <div class="feature-card">
                        <img src="../assets/images/Sesi-Biblioteca.png" alt="Biblioteca">
                        <h3>Biblioteca</h3>
                        <p>Nossa biblioteca possui um vasto acervo de livros, revistas e recursos digitais, proporcionando um ambiente propício para estudos e pesquisas.</p>
                    </div>
                    
                    <div class="feature-card">
                        <img src="../assets/images/Senai-Bloco.png" alt="Laboratórios">
                        <h3>Laboratórios</h3>
                        <p>Contamos com laboratórios modernos e bem equipados para as aulas de ciências, física, química, biologia e informática.</p>
                    </div>
                    
                    <div class="feature-card">
                        <img src="../assets/images/Sesi-Refeitório.png" alt="Refeitório">
                        <h3>Refeitório</h3>
                        <p>O refeitório da escola oferece alimentação balanceada e nutritiva, preparada por profissionais qualificados.</p>
                    </div>
                </div>
                
                <div class="school-info">
                    <div class="info-card">
                        <i class="fas fa-users"></i>
                        <h3>Corpo Docente</h3>
                        <p>Nosso corpo docente é composto por professores com formação acadêmica sólida e experiência de mercado, comprometidos com a educação de qualidade e o desenvolvimento integral dos alunos.</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-book"></i>
                        <h3>Metodologia</h3>
                        <p>Nossa metodologia de ensino combina teoria e prática, com foco no desenvolvimento de competências e habilidades essenciais para o século XXI, como criatividade, comunicação, colaboração e pensamento crítico.</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>Resultados</h3>
                        <p>Nossos alunos alcançam excelentes resultados em exames e vestibulares, além de se destacarem em olimpíadas do conhecimento e competições nacionais e internacionais.</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-handshake"></i>
                        <h3>Parcerias</h3>
                        <p>Mantemos parcerias com empresas e instituições de ensino superior, proporcionando aos nossos alunos oportunidades de estágio, visitas técnicas e projetos de extensão.</p>
                    </div>
                </div>
                
                <div class="school-cta">
                    <h2>Faça parte da nossa comunidade</h2>
                    <p>Venha conhecer a Escola SESI e descubra por que somos referência em educação de qualidade.</p>
                    <a href="contato.php" class="btn btn-primary">Entre em contato</a>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- JavaScript -->
<<<<<<< Updated upstream
    <script src="../assets/js/auth-cookies.js"></script>
    <script src="../assets/js/verificar-sincronizar-login.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/user-menu.js"></script>
    <script src="../assets/js/dropdown-menu.js"></script>
    <script src="../assets/js/header-nav.js"></script>
=======
    <script src="../assets/js/main.js"></script>
>>>>>>> Stashed changes
</body>
</html>
