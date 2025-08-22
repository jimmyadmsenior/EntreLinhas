<?php
// Arquivo de cabeçalho comum para todas as páginas que exigem login
// Verificar se o usuário está logado e obter dados do perfil

// Se a sessão ainda não foi iniciada, inicie-a
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Se estiver logado, obter a foto de perfil
$foto_perfil = null;
if ($usuario_logado && isset($conn)) {
    // Carregar helper se ainda não estiver carregado
    if (!function_exists('obter_foto_perfil')) {
        require_once dirname(__FILE__) . "/usuario_helper.php";
    }
    
    // Obter foto de perfil
    $foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);
}

// Determinar qual página está ativa para destacar no menu
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>

<!-- Header -->
<header>
    <nav class="navbar">
        <div class="logo">
            <a href="index.html">EntreLinhas</a>
        </div>
        
        <ul class="nav-links">
            <li><a href="index.html" <?php if($pagina_atual == 'index.html' || $pagina_atual == 'index.php') echo 'class="active"'; ?>>Início</a></li>
            <li><a href="artigos.html" <?php if($pagina_atual == 'artigos.html' || $pagina_atual == 'artigos.php') echo 'class="active"'; ?>>Artigos</a></li>
            <li><a href="sobre.html" <?php if($pagina_atual == 'sobre.html') echo 'class="active"'; ?>>Sobre</a></li>
            <li><a href="escola.html" <?php if($pagina_atual == 'escola.html') echo 'class="active"'; ?>>A Escola</a></li>
            <li><a href="contato.html" <?php if($pagina_atual == 'contato.html') echo 'class="active"'; ?>>Contato</a></li>
        </ul>
        
        <div class="nav-buttons">
            <?php if ($usuario_logado): ?>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" style="display: flex; align-items: center; gap: 8px;">
                        <?php if ($foto_perfil): ?>
                            <div style="width: 24px; height: 24px; border-radius: 50%; overflow: hidden;">
                                <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                    </button>
                    <div class="dropdown-menu">
                        <a href="perfil.php" <?php if($pagina_atual == 'perfil.php') echo 'class="active"'; ?>>Meu Perfil</a>
                        <a href="meus-artigos.php" <?php if($pagina_atual == 'meus-artigos.php') echo 'class="active"'; ?>>Meus Artigos</a>
                        <a href="enviar-artigo.php" <?php if($pagina_atual == 'enviar-artigo.php') echo 'class="active"'; ?>>Enviar Artigo</a>
                        <a href="../backend/logout.php">Sair</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">Entrar</a>
                <a href="cadastro.php" class="btn btn-primary">Cadastrar</a>
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
