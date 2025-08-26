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
                            </div>
                        </article>';
                    }
                } else {
                    echo '<div class="no-articles"><p>Nenhum artigo publicado ainda.</p></div>';
                }
                ?>
            </div>
        </section>
        
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
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <a href="enviar-artigo.php" class="btn btn-primary">Enviar um Artigo</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">Entrar</a>
                <a href="registro.php" class="btn btn-primary">Cadastre-se</a>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
