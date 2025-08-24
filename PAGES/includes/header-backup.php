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

<!-- Header -->
<header>
    <nav class="navbar">
        <div class="logo">
            <a href="../index.html">EntreLinhas</a>
        </div>
        
        <ul class="nav-links">
            <li><a href="../index.html" <?php if($pagina_atual == 'index.html' || $pagina_atual == 'index.php') echo 'class="active"'; ?>>Início</a></li>
            <li><a href="artigos.php" <?php if($pagina_atual == 'artigos.html' || $pagina_atual == 'artigos.php') echo 'class="active"'; ?>>Artigos</a></li>
            <li><a href="sobre.html" <?php if($pagina_atual == 'sobre.html') echo 'class="active"'; ?>>Sobre</a></li>
            <li><a href="escola.php" <?php if($pagina_atual == 'escola.html' || $pagina_atual == 'escola.php') echo 'class="active"'; ?>>A Escola</a></li>
            <li><a href="contato.html" <?php if($pagina_atual == 'contato.html') echo 'class="active"'; ?>>Contato</a></li>
        </ul>
        
        <div class="nav-buttons">
            <?php if ($usuario_logado): ?>
                <!-- Menu dropdown do usuário -->
                <div class="user-menu">
                    <div class="user-name">
                        <?php if ($foto_perfil): ?>
                            <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" class="user-avatar">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
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

<!-- Adicionar script para funcionalidade do menu dropdown -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidade de dropdown
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
        const userNameDiv = userMenu.querySelector('.user-name');
        userNameDiv.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userMenu.classList.toggle('active');
            console.log('Menu de usuário clicado');
        });
        
        // Fechar ao clicar fora
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
    }
    
    // Funcionalidade de menu móvel
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }
    
    // Funcionalidade de tema
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const icon = themeToggle.querySelector('i');
            if (icon.classList.contains('fa-moon')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
            
            // Salvar preferência
            const isDarkMode = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode ? 'true' : 'false');
        });
        
        // Verificar tema preferido
        const savedDarkMode = localStorage.getItem('darkMode') === 'true';
        if (savedDarkMode) {
            document.body.classList.add('dark-mode');
            const icon = themeToggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        }
    }
});
</script>
