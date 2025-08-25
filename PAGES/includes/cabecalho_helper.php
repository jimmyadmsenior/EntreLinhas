<?php
// Função para gerar o menu de navegação consistente em todas as páginas
// Esta função pode ser chamada em qualquer página para gerar o menu apropriado

function gerar_cabecalho($conn = null, $pagina_atual = '') {
    // Garantir que a sessão está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar se o usuário está logado
    $usuario_logado = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
    
    // Determinar a página atual se não foi especificada
    if (empty($pagina_atual)) {
        $pagina_atual = basename($_SERVER['PHP_SELF']);
    }
    
    // Verificar se é uma página de administração
    $is_admin_page = strpos($pagina_atual, 'admin_') !== false;
    
    // Se estiver logado, obter a foto de perfil
    $foto_perfil = null;
    if ($usuario_logado && isset($conn)) {
        // Carregar helper se ainda não estiver carregado
        if (!function_exists('obter_foto_perfil')) {
            $helper_path = dirname(__FILE__) . "/../../backend/usuario_helper.php";
            if (file_exists($helper_path)) {
                require_once $helper_path;
            }
        }
        
        // Obter foto de perfil
        if (function_exists('obter_foto_perfil')) {
            $foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);
        }
    }
    
    // Função para verificar se um link deve estar ativo
    $isLinkActive = function($page) use ($pagina_atual) {
        if ($pagina_atual === $page) {
            return 'class="active"';
        } elseif ($page === 'index.php' && ($pagina_atual === '' || $pagina_atual === 'index.html')) {
            return 'class="active"';
        } elseif (str_replace('.php', '.html', $page) === $pagina_atual || 
                 str_replace('.html', '.php', $page) === $pagina_atual) {
            return 'class="active"';
        }
        return '';
    };
    
    // Gerar o HTML do cabeçalho
    ob_start();
    ?>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.php" <?php echo $isLinkActive('index.php'); ?>>Início</a></li>
                <li><a href="artigos.php" <?php echo $isLinkActive('artigos.php'); ?>>Artigos</a></li>
                <li><a href="sobre.php" <?php echo $isLinkActive('sobre.php'); ?>>Sobre</a></li>
                <li><a href="escola.php" <?php echo $isLinkActive('escola.php'); ?>>A Escola</a></li>
                <li><a href="contato.php" <?php echo $isLinkActive('contato.php'); ?>>Contato</a></li>
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
    <?php
    return ob_get_clean();
}

// Função para gerar os links CSS necessários para o cabeçalho
function gerar_cabecalho_css() {
    ob_start();
    ?>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- CSS do Menu do Usuário -->
    <link rel="stylesheet" href="../assets/css/user-menu.css">
    <?php
    return ob_get_clean();
}

// Função para gerar os scripts JS necessários para o cabeçalho
function gerar_cabecalho_js() {
    ob_start();
    ?>
    <!-- Scripts para o menu do usuário -->
    <script src="../assets/js/user-menu.js" defer></script>
    <?php
    return ob_get_clean();
}
?>
