<?php
// Arquivo de cabeçalho comum para todas as páginas que exigem login
// Verificar se o usuário está logado e obter dados do perfil

// Não iniciar sessão aqui, pois isso já é feito no session.php que é incluído nas páginas principais

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Se estiver logado, obter a foto de perfil
$foto_perfil = null;
if ($usuario_logado && isset($conn)) {
    // Carregar helper se ainda não estiver carregado
    if (!function_exists('obter_foto_perfil')) {
        require_once dirname(__FILE__) . "/../../backend/usuario_helper.php";
    }
    
    // Obter foto de perfil
    if (function_exists('obter_foto_perfil')) {
        $foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);
    }
}

// Determinar qual página está ativa para destacar no menu
$pagina_atual = basename($_SERVER['PHP_SELF']);

// Garantir que o nome do usuário esteja disponível para JavaScript
if ($usuario_logado) {
    // Definir cookies para JavaScript
    setcookie("userLoggedIn", "true", time() + 86400, "/");
    setcookie("userName", $_SESSION["nome"], time() + 86400, "/");
    setcookie("userEmail", $_SESSION["email"], time() + 86400, "/");
    setcookie("userType", $_SESSION["tipo"], time() + 86400, "/");
    setcookie("userId", $_SESSION["id"], time() + 86400, "/");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'EntreLinhas'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $root_path ?? '..'; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $root_path ?? '..'; ?>/assets/css/user-menu.css">
    <link rel="stylesheet" href="<?php echo $root_path ?? '..'; ?>/assets/css/alerts.css">
    <?php if (isset($page_specific_css)): ?>
        <?php foreach($page_specific_css as $css_file): ?>
        <link rel="stylesheet" href="<?php echo $css_file; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Scripts para autenticação e menu do usuário -->
    <?php 
    $pagina_atual = basename($_SERVER['PHP_SELF']);
    $paginas_auth = ['login.php', 'cadastro.php', 'registro.php'];
    if (!in_array($pagina_atual, $paginas_auth)): 
    ?>
    <script src="<?php echo $root_path ?? '..'; ?>/assets/js/auth-cookies.js" defer></script>
    <script src="<?php echo $root_path ?? '..'; ?>/assets/js/verificar-sincronizar-login.js" defer></script>
    <?php endif; ?>
    <script src="<?php echo $root_path ?? '..'; ?>/assets/js/user-menu.js" defer></script>
    <script src="<?php echo $root_path ?? '..'; ?>/assets/js/dropdown-menu.js" defer></script>
    
    <?php if (isset($page_specific_head)): ?>
        <?php echo $page_specific_head; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="../index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="../index.php" <?php if($pagina_atual == 'index.html' || $pagina_atual == 'index.php') echo 'class="active"'; ?>>Início</a></li>
                <li><a href="artigos.php" <?php if($pagina_atual == 'artigos.html' || $pagina_atual == 'artigos.php') echo 'class="active"'; ?>>Artigos</a></li>
                <li><a href="sobre.php" <?php if($pagina_atual == 'sobre.html' || $pagina_atual == 'sobre.php') echo 'class="active"'; ?>>Sobre</a></li>
                <li><a href="escola.php" <?php if($pagina_atual == 'escola.html' || $pagina_atual == 'escola.php') echo 'class="active"'; ?>>A Escola</a></li>
                <li><a href="contato.php" <?php if($pagina_atual == 'contato.html' || $pagina_atual == 'contato.php') echo 'class="active"'; ?>>Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <?php if ($usuario_logado): ?>
                    <!-- Menu dropdown do usuário -->
                    <div class="user-menu">
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
                            <a href="perfil.php" class="dropdown-link"><i class="fas fa-id-card"></i> Meu Perfil</a>
                            <a href="meus-artigos.php" class="dropdown-link"><i class="fas fa-newspaper"></i> Meus Artigos</a>
                            <a href="enviar-artigo.php" class="dropdown-link"><i class="fas fa-edit"></i> Enviar Artigo</a>
                            <?php if (isset($_SESSION["tipo"]) && $_SESSION["tipo"] === 'admin'): ?>
                                <a href="admin_dashboard.php" class="dropdown-link"><i class="fas fa-cogs"></i> Painel de Admin</a>
                            <?php endif; ?>
                            <a href="../backend/logout.php" class="dropdown-link"><i class="fas fa-sign-out-alt"></i> Sair</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Links de login e cadastro -->
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
    
<script>
// O JavaScript para o cabeçalho agora está centralizado nos arquivos:
// - user-menu.js: para o menu dropdown do usuário
// - header-nav.js: para a navegação e botões do cabeçalho
</script>
