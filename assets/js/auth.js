/**
 * EntreLinhas - auth.js
 * Gerencia o estado de autenticação do usuário em todas as páginas
 */

// Função para fazer logout
function logout() {
    localStorage.removeItem('userLoggedIn');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userType');
    
    // Redirecionar para a página inicial
    window.location.href = 'index.html';
}

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
        dropdown.className = 'user-menu';
        
        dropdown.innerHTML = `
            <div class="user-name">
                <i class="fas fa-user"></i> ${auth.userName} <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu">
<<<<<<< Updated upstream
                <a href="perfil.php"><i class="fas fa-id-card"></i> Meu Perfil</a>
                <a href="meus-artigos.php"><i class="fas fa-newspaper"></i> Meus Artigos</a>
                <a href="enviar-artigo.php"><i class="fas fa-edit"></i> Enviar Artigo</a>
                ${auth.isAdmin ? '<a href="admin_dashboard.php"><i class="fas fa-cogs"></i> Painel de Admin</a>' : ''}
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
=======
                <a href="perfil.html">Meu Perfil</a>
                <a href="meus-artigos.html">Meus Artigos</a>
                <a href="enviar-artigo.html">Enviar Artigo</a>
                ${auth.isAdmin ? '<a href="admin.html">Painel de Admin</a>' : ''}
                <a href="javascript:void(0)" onclick="logout()">Sair</a>
>>>>>>> Stashed changes
            </div>
        `;
        
        // Adicionar o dropdown antes dos botões de tema e menu
        if (themeToggle) {
            navButtons.insertBefore(dropdown, themeToggle);
        } else {
            navButtons.prepend(dropdown);
        }
        
        // O setupUserMenuEvents será chamado do script user-menu.js
        
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
        registerBtn.href = 'cadastro.php';
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

// A função setupUserMenuEvents foi movida para user-menu.js

// Inicializar ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    console.log("auth.js carregado - atualizando navegação");
    updateNavigation();
    
    // setupUserMenuEvents agora é chamado do user-menu.js
    
    // Função para fazer logout
    window.logout = function() {
        console.log('Executando logout');
        // Usar a função de limpeza de dados para evitar duplicação de código
        clearUserData();
        console.log('Dados do usuário removidos');
        alert('Você foi desconectado com sucesso!');
        window.location.href = 'index.php';
    }
});
