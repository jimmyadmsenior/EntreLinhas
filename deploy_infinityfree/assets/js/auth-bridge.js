/**
 * Este script verifica o estado de login do usuário armazenado no localStorage
 * e atualiza a interface do usuário adequadamente.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se o usuário está logado verificando o localStorage
    const userLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
    const userName = localStorage.getItem('userName');
    const userEmail = localStorage.getItem('userEmail');
    const userType = localStorage.getItem('userType');
    
    // Elementos do menu a serem atualizados
    const navButtons = document.querySelector('.nav-buttons');
    
    if (navButtons && userLoggedIn && userName) {
        // Usuário está logado, mostrar menu dropdown
        navButtons.innerHTML = `
            <div class="user-menu">
                <div class="user-name">
                    <span class="avatar-container">
                        <i class="fas fa-user"></i>
                    </span>
                    ${userName} <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu" id="user-dropdown-menu">
                    <a href="perfil.php" class="dropdown-link"><i class="fas fa-id-card"></i> Meu Perfil</a>
                    <a href="meus-artigos.php" class="dropdown-link"><i class="fas fa-newspaper"></i> Meus Artigos</a>
                    <a href="enviar-artigo.php" class="dropdown-link"><i class="fas fa-edit"></i> Enviar Artigo</a>
                    ${userType === 'admin' ? `<a href="admin_dashboard.php" class="dropdown-link"><i class="fas fa-cogs"></i> Painel de Admin</a>` : ''}
                    <a href="logout.php" class="dropdown-link"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
            <button id="theme-toggle" class="theme-toggle" aria-label="Alternar modo escuro">
                <i class="fas fa-moon"></i>
            </button>
            <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        `;
        
        // Configurar o menu dropdown
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
    }
});
