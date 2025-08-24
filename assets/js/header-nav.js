/**
 * EntreLinhas - header-nav.js
 * Centraliza a lógica para atualizar o cabeçalho e navegação em todas as páginas
 */

// Função simplificada para detectar o caminho base
function getBasePath() {
    // Solução direta que funciona para a maioria dos casos:
    // Se a URL atual tem /PAGES/ ou /pages/, estamos dentro da pasta PAGES
    const path = window.location.pathname.toLowerCase();
    console.log('URL atual:', path);
    
    if (path.includes('/pages/')) {
        console.log('Estamos na pasta PAGES');
        return '';
    } else {
        console.log('Estamos fora da pasta PAGES');
        return 'PAGES/';
    }
}

// Função para ler um cookie pelo nome
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Função para verificar o estado atual da página e atualizar o menu
function atualizarHeaderMenu() {
    console.log('Atualizando menu de navegação...');
    
    // Verificar se o usuário está logado - verificar tanto localStorage quanto cookies
    let userLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
    let userName = localStorage.getItem('userName');
    let userType = localStorage.getItem('userType');
    
    // Verificar os cookies também (prioridade sobre localStorage)
    const cookieLoggedIn = getCookie('userLoggedIn');
    const cookieName = getCookie('userName');
    const cookieType = getCookie('userType');
    
    // Se temos os dados nos cookies, usá-los e atualizar o localStorage
    if (cookieLoggedIn === 'true') {
        userLoggedIn = true;
        userName = cookieName || userName;
        userType = cookieType || userType;
        
        // Decodificar o nome se estiver codificado em URL
        if (userName && userName.includes('%')) {
            try {
                userName = decodeURIComponent(userName);
                console.log('Nome decodificado:', userName);
            } catch (e) {
                console.error('Erro ao decodificar o nome:', e);
            }
        }
        
        // Sincronizar com localStorage
        localStorage.setItem('userLoggedIn', 'true');
        if (userName) localStorage.setItem('userName', userName);
        if (cookieType) localStorage.setItem('userType', cookieType);
    }
    
    console.log('Estado de login:', userLoggedIn ? 'Logado' : 'Não logado');
    console.log('Nome do usuário:', userName || 'N/A');
    console.log('Tipo de usuário:', userType || 'N/A');
    
    // Obter a div de botões de navegação
    const navButtons = document.querySelector('.nav-buttons');
    if (!navButtons) {
        console.error('Elemento .nav-buttons não encontrado!');
        return;
    }
    
    // Verificar se já existe um menu dropdown ativo e removê-lo para evitar duplicação
    const existingUserMenu = document.querySelector('.user-menu');
    if (existingUserMenu) {
        existingUserMenu.remove();
    }
    
    // Preservar os botões existentes que devem permanecer
    const themeToggle = document.getElementById('theme-toggle');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    
    // Remover todos os elementos exceto os botões preservados
    const elementsToRemove = [];
    for (let i = 0; i < navButtons.children.length; i++) {
        const child = navButtons.children[i];
        if (child !== themeToggle && child !== mobileMenuBtn) {
            elementsToRemove.push(child);
        }
    }
    
    // Remover os elementos (em array separado para evitar problemas ao iterar sobre uma coleção em modificação)
    elementsToRemove.forEach(element => element.remove());
    
    if (userLoggedIn && userName) {
        // Usuário está logado, criar o menu dropdown
        const userMenu = document.createElement('div');
        userMenu.className = 'user-menu';
        
        const isAdmin = userType === 'admin';
        
        const basePath = getBasePath();
        
        // Usar o script de ponte de autenticação para páginas PHP
        userMenu.innerHTML = `
            <div class="user-name">
                <i class="fas fa-user"></i> ${userName} <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu" id="user-dropdown-menu">
                <a href="${basePath}auth-bridge.php?to=perfil.php" class="dropdown-link"><i class="fas fa-id-card"></i> Meu Perfil</a>
                <a href="${basePath}auth-bridge.php?to=meus-artigos.php" class="dropdown-link"><i class="fas fa-newspaper"></i> Meus Artigos</a>
                <a href="${basePath}enviar-artigo.html" class="dropdown-link"><i class="fas fa-edit"></i> Enviar Artigo</a>
                ${isAdmin ? `<a href="${basePath}auth-bridge.php?to=admin_dashboard.php" class="dropdown-link"><i class="fas fa-cogs"></i> Painel de Admin</a>` : ''}
                <a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        `;
        
        // Adicionar o menu antes dos botões preservados
        if (themeToggle) {
            navButtons.insertBefore(userMenu, themeToggle);
        } else {
            navButtons.prepend(userMenu);
        }
        
        // Configurar eventos do menu de usuário
        const userNameDiv = userMenu.querySelector('.user-name');
        userNameDiv.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userMenu.classList.toggle('active');
            console.log('Menu de usuário clicado, estado:', userMenu.classList.contains('active') ? 'ativo' : 'inativo');
        });
        
        // Configurar apenas o evento de logout - usar o logout.php do PHP
        const logoutLink = userMenu.querySelector('#logout-link');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                // Usar o logout.php para garantir que a sessão PHP seja destruída
                const basePath = getBasePath();
                window.location.href = `${basePath}logout.php`;
            });
        }
        
        // Garantir que os links funcionem diretamente sem interceptação
        const navigationLinks = userMenu.querySelectorAll('.dropdown-menu a:not(#logout-link)');
        navigationLinks.forEach(link => {
            console.log(`Link configurado: ${link.textContent} -> ${link.href}`);
            
            // Garantir que o link funcione normalmente
            link.onclick = function(e) {
                // Não prevenir o comportamento padrão, apenas registrar
                console.log('Link clicado:', this.href);
                // Navegar explicitamente 
                window.location.href = this.href;
            };
        });
        
        // Debug: mostrar os links configurados
        const dropdownMenu = userMenu.querySelector('.dropdown-menu');
        const allLinks = dropdownMenu.querySelectorAll('a');
        console.log('Total de links no dropdown:', allLinks.length);
        allLinks.forEach((link, index) => {
            console.log(`Link ${index + 1}:`, link.href, link.textContent);
        });
        
        // Fechar menu ao clicar fora
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
        
    } else {
        // Usuário não está logado, mostrar botões de login e cadastro
        const basePath = getBasePath();
        
        const loginBtn = document.createElement('a');
        loginBtn.href = basePath + 'login.html';
        loginBtn.className = 'btn btn-secondary';
        loginBtn.textContent = 'Entrar';
        
        const registerBtn = document.createElement('a');
        registerBtn.href = basePath + 'cadastro.html';
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
    
    console.log('Menu de navegação atualizado!');
}

// Função para fazer logout - AGORA REDIRECIONANDO PARA logout.php
// Esta função não é mais usada diretamente, mantida apenas por compatibilidade
function logout() {
    console.log('Esta função não é mais usada diretamente');
    
    // Usar o logout.php do PHP para garantir consistência na autenticação
    const basePath = getBasePath();
    window.location.href = `${basePath}logout.php`;
    
    // O código abaixo não é mais executado, pois o redirecionamento acontece acima
    /*
    // Limpar todos os dados do usuário do localStorage
    localStorage.removeItem('userLoggedIn');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userType');
    localStorage.removeItem('userId');
    
    // Obter o caminho atual da página
    const currentPath = window.location.pathname.toLowerCase();
    console.log('Caminho atual:', currentPath);
    
    // Determinar página de redirecionamento após logout
    let redirectTo = '';
    */
    
    if (currentPath.includes('/pages/')) {
        redirectTo = 'index.html'; // Relativo à pasta PAGES
    } else {
        redirectTo = 'PAGES/index.html'; // Relativo à raiz
    }
    
    console.log('Redirecionando para:', redirectTo);
    window.location.href = redirectTo;
}

// Registrar a função de logout globalmente
window.logout = logout;

// Inicializar quando o documento estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    console.log('header-nav.js carregado');
    atualizarHeaderMenu();
    
    // Log simples para debug
    console.log('Menu header-nav inicializado');
    console.log('URL atual:', window.location.href);
    console.log('BasePath calculado:', getBasePath());
    console.log('Estado de login:', localStorage.getItem('userLoggedIn') === 'true' ? 'Logado' : 'Não logado');
    
    // Verificar alterações no localStorage
    window.addEventListener('storage', function(e) {
        if (e.key === 'userLoggedIn' || e.key === 'userName' || e.key === 'userType') {
            atualizarHeaderMenu();
        }
    });
});
