/**
 * EntreLinhas - auth.js
 * Gerencia o estado de autenticação do usuário em todas as páginas
 */

// Função para verificar se o usuário está logado
function checkUserAuth() {
    const userLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
    const userName = localStorage.getItem('userName');
    const userEmail = localStorage.getItem('userEmail');
    const userType = localStorage.getItem('userType');
    
    return {
        isLoggedIn: userLoggedIn,
        userName: userName,
        userEmail: userEmail,
        userType: userType,
        isAdmin: userType === 'admin'
    };
}

// Função para atualizar a navegação com base no estado de autenticação
function updateNavigation() {
    const navButtons = document.querySelector('.nav-buttons');
    if (!navButtons) return;
    
    const auth = checkUserAuth();
    
    // Preservar os botões de tema e menu móvel
    const themeToggle = document.getElementById('theme-toggle');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    
    if (auth.isLoggedIn) {
        // Limpar conteúdo existente exceto theme toggle e mobile menu
        Array.from(navButtons.children).forEach(child => {
            if (child !== themeToggle && child !== mobileMenuBtn) {
                navButtons.removeChild(child);
            }
        });
        
        // Criar o dropdown do usuário
        const dropdown = document.createElement('div');
        dropdown.className = 'dropdown';
        
        dropdown.innerHTML = `
            <button class="btn btn-secondary dropdown-toggle">
                <i class="fas fa-user"></i> ${auth.userName}
            </button>
            <div class="dropdown-menu">
                <a href="perfil.php">Meu Perfil</a>
                <a href="meus-artigos.php">Meus Artigos</a>
                <a href="enviar-artigo.php">Enviar Artigo</a>
                ${auth.isAdmin ? '<a href="admin.php">Painel de Admin</a>' : ''}
                <a href="../backend/logout.php">Sair</a>
            </div>
        `;
        
        // Adicionar o dropdown antes dos botões de tema e menu
        if (themeToggle) {
            navButtons.insertBefore(dropdown, themeToggle);
        } else {
            navButtons.prepend(dropdown);
        }
        
    } else {
        // Limpar conteúdo existente exceto theme toggle e mobile menu
        Array.from(navButtons.children).forEach(child => {
            if (child !== themeToggle && child !== mobileMenuBtn) {
                navButtons.removeChild(child);
            }
        });
        
        // Adicionar botões de login e cadastro
        const loginBtn = document.createElement('a');
        loginBtn.href = 'login.php';
        loginBtn.className = 'btn btn-secondary';
        loginBtn.textContent = 'Entrar';
        
        const registerBtn = document.createElement('a');
        registerBtn.href = 'cadastro.html';
        registerBtn.className = 'btn btn-primary';
        registerBtn.textContent = 'Cadastrar';
        
        // Inserir os botões antes do toggle de tema
        if (themeToggle) {
            navButtons.insertBefore(registerBtn, themeToggle);
            navButtons.insertBefore(loginBtn, registerBtn);
        } else {
            navButtons.prepend(registerBtn);
            navButtons.prepend(loginBtn);
        }
    }
}

// Função para salvar dados do usuário após login
function saveUserData(userData) {
    localStorage.setItem('userLoggedIn', 'true');
    localStorage.setItem('userName', userData.nome);
    localStorage.setItem('userEmail', userData.email);
    localStorage.setItem('userType', userData.tipo);
    localStorage.setItem('userId', userData.id);
    
    // Atualizar navegação
    updateNavigation();
}

// Função para limpar dados do usuário após logout
function clearUserData() {
    localStorage.removeItem('userLoggedIn');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userType');
    localStorage.removeItem('userId');
    
    // Atualizar navegação
    updateNavigation();
}

// Inicializar ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    updateNavigation();
    
    // Detectar logout.php e limpar dados do usuário
    if (window.location.pathname.includes('logout.php')) {
        clearUserData();
    }
});
