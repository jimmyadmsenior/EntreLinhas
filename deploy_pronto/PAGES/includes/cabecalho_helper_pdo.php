<?php
// Função para gerar o menu de navegação consistente em todas as páginas
// Esta função pode ser chamada em qualquer página para gerar o menu apropriado
// Versão PDO

function gerar_cabecalho($pdo = null, $pagina_atual = '') {
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
    if ($usuario_logado && isset($pdo)) {
        // Carregar helper se ainda não estiver carregado
        if (!function_exists('obter_foto_perfil_pdo')) {
            $helper_path = dirname(__FILE__) . "/../../backend/usuario_helper_pdo.php";
            if (file_exists($helper_path)) {
                require_once $helper_path;
            }
        }
        
        // Se a função existir, usá-la para obter a foto de perfil
        if (function_exists('obter_foto_perfil_pdo')) {
            $foto_perfil = obter_foto_perfil_pdo($pdo, $_SESSION["id"]);
        }
    }
    
    // Construir os links do menu
    $menu_items = array(
        'index.php' => array('texto' => 'Início', 'icon' => 'fas fa-home'),
        'categorias.php' => array('texto' => 'Categorias', 'icon' => 'fas fa-th-large'),
        'ultimas_noticias.php' => array('texto' => 'Últimas Notícias', 'icon' => 'fas fa-newspaper'),
        'sobre.php' => array('texto' => 'Sobre', 'icon' => 'fas fa-info-circle'),
        'contato.php' => array('texto' => 'Contato', 'icon' => 'fas fa-envelope')
    );
    
    // Links que só aparecem para usuários logados
    $menu_usuario_logado = array(
        'enviar-artigo.php' => array('texto' => 'Enviar Artigo', 'icon' => 'fas fa-edit'),
        'meus-artigos.php' => array('texto' => 'Meus Artigos', 'icon' => 'fas fa-folder')
    );
    
    // Links que só aparecem para administradores
    $menu_admin = array(
        'admin_dashboard.php' => array('texto' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'),
        'admin_artigos.php' => array('texto' => 'Gerenciar Artigos', 'icon' => 'fas fa-file-alt'),
        'admin_usuarios.php' => array('texto' => 'Gerenciar Usuários', 'icon' => 'fas fa-users')
    );
    
    // Começar a montar o HTML do cabeçalho
    $output = '<header class="main-header">';
    $output .= '<div class="container">';
    $output .= '<div class="header-content">';
    
    // Logo
    $output .= '<div class="logo">';
    $output .= '<a href="index.php" class="logo-link">';
    $output .= '<img src="../assets/images/jornal.png" alt="EntreLinhas Logo" class="logo-img">';
    $output .= '<span class="logo-text">EntreLinhas</span>';
    $output .= '</a>';
    $output .= '</div>';
    
    // Mobile menu toggle
    $output .= '<button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Abrir menu">';
    $output .= '<span class="hamburger-icon"></span>';
    $output .= '</button>';
    
    // Navigation
    $output .= '<nav class="main-nav" id="main-nav">';
    $output .= '<ul class="nav-list">';
    
    // Regular menu items
    foreach ($menu_items as $link => $item) {
        $active = ($pagina_atual == $link) ? ' active' : '';
        $output .= '<li class="nav-item' . $active . '">';
        $output .= '<a href="' . $link . '" class="nav-link">';
        $output .= '<i class="' . $item['icon'] . '"></i> ' . $item['texto'];
        $output .= '</a></li>';
    }
    
    // Items for logged in users
    if ($usuario_logado) {
        foreach ($menu_usuario_logado as $link => $item) {
            $active = ($pagina_atual == $link) ? ' active' : '';
            $output .= '<li class="nav-item' . $active . '">';
            $output .= '<a href="' . $link . '" class="nav-link">';
            $output .= '<i class="' . $item['icon'] . '"></i> ' . $item['texto'];
            $output .= '</a></li>';
        }
        
        // Admin menu items - only show if the user is an admin
        if (isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "admin") {
            foreach ($menu_admin as $link => $item) {
                $active = ($pagina_atual == $link) ? ' active' : '';
                $output .= '<li class="nav-item' . $active . '">';
                $output .= '<a href="' . $link . '" class="nav-link">';
                $output .= '<i class="' . $item['icon'] . '"></i> ' . $item['texto'];
                $output .= '</a></li>';
            }
        }
    }
    
    $output .= '</ul>';
    $output .= '</nav>';
    
    // User menu section
    $output .= '<div class="user-menu">';
    
    if ($usuario_logado) {
        // Profile picture or default avatar
        $avatar_url = $foto_perfil ? 'data:image/jpeg;base64,' . $foto_perfil : '../assets/images/default-avatar.png';
        
        $output .= '<div class="user-dropdown">';
        $output .= '<div class="user-dropdown-toggle" id="user-dropdown-toggle">';
        $output .= '<img src="' . $avatar_url . '" alt="Foto de perfil" class="user-avatar">';
        $output .= '<span class="user-name">' . htmlspecialchars($_SESSION["nome"]) . '</span>';
        $output .= '<i class="fas fa-chevron-down"></i>';
        $output .= '</div>';
        
        // Dropdown menu
        $output .= '<div class="user-dropdown-menu" id="user-dropdown-menu">';
        $output .= '<ul class="dropdown-menu-list">';
        $output .= '<li><a href="perfil.php"><i class="fas fa-user"></i> Meu Perfil</a></li>';
        
        if (isset($_SESSION["tipo"]) && $_SESSION["tipo"] === "admin") {
            $output .= '<li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</a></li>';
        }
        
        $output .= '<li><a href="meus-artigos.php"><i class="fas fa-folder"></i> Meus Artigos</a></li>';
        $output .= '<li><a href="javascript:void(0)" id="logout-link"><i class="fas fa-sign-out-alt"></i> Sair</a></li>';
        $output .= '</ul>';
        $output .= '</div>'; // Fim do dropdown-menu
        $output .= '</div>'; // Fim do user-dropdown
    } else {
        // Login/Register buttons for non-logged in users
        $output .= '<div class="auth-buttons">';
        $output .= '<a href="login.php" class="btn btn-sm btn-outline">Entrar</a>';
        $output .= '<a href="register.php" class="btn btn-sm btn-primary">Cadastrar</a>';
        $output .= '</div>';
    }
    
    $output .= '</div>'; // Fim do user-menu
    $output .= '</div>'; // Fim do header-content
    $output .= '</div>'; // Fim do container
    $output .= '</header>';
    
    return $output;
}

// Função para gerar elementos dinâmicos do cabeçalho como meta tags, títulos, etc.
function gerar_meta_tags($titulo = 'EntreLinhas - Jornal Digital', $descricao = 'EntreLinhas - Jornal digital colaborativo com notícias, artigos e textos da comunidade.') {
    $output = "<title>" . htmlspecialchars($titulo) . "</title>\n";
    $output .= '<meta name="description" content="' . htmlspecialchars($descricao) . '">' . "\n";
    return $output;
}
?>
