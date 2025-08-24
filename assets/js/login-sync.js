// Este script verifica e sincroniza o estado de login entre JavaScript e PHP
// É projetado para ser incluído em todas as páginas HTML e PHP

document.addEventListener('DOMContentLoaded', function() {
    console.log('Verificando sincronização de login...');
    
    // Função para obter o valor de um cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    
    // Verificar se temos um estado de login no localStorage
    const userLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
    const phpAuth = getCookie('php_auth') === 'true';
    const userName = localStorage.getItem('userName');
    const userId = localStorage.getItem('userId');
    
    console.log('Estado de login JS:', userLoggedIn);
    console.log('Estado de login PHP:', phpAuth);
    
    // Se o usuário está logado no JavaScript mas não no PHP
    if (userLoggedIn && !phpAuth && userId) {
        console.log('Sincronizando login: JS → PHP');
        // Redirecionar para o bridge de autenticação com os dados do usuário
        window.location.href = '/EntreLinhas/PAGES/auth-bridge.php?userId=' + userId + '&to=' + window.location.pathname.split('/').pop();
    }
    // Se o usuário está logado no PHP mas não no JavaScript
    else if (phpAuth && !userLoggedIn) {
        console.log('Sincronizando login: PHP → JS');
        // Obter dados dos cookies PHP
        const phpUserName = getCookie('userName');
        const phpUserEmail = getCookie('userEmail');
        const phpUserId = getCookie('userId');
        const phpUserType = getCookie('userType');
        
        if (phpUserId) {
            // Armazenar dados do usuário no localStorage
            localStorage.setItem('userLoggedIn', 'true');
            localStorage.setItem('userName', phpUserName || 'Usuário');
            localStorage.setItem('userEmail', phpUserEmail || '');
            localStorage.setItem('userId', phpUserId);
            localStorage.setItem('userType', phpUserType || 'usuario');
            console.log('Login sincronizado de PHP para JS');
            
            // Atualizar a página para refletir o estado de login
            window.location.reload();
        }
    }
    
    // Verificar se os menus de login/usuário estão exibidos corretamente
    setTimeout(function() {
        updateMenuVisibility();
    }, 100);
});

// Função para atualizar a visibilidade dos menus de acordo com o estado de login
function updateMenuVisibility() {
    const userLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
    console.log('Atualizando visibilidade dos menus. Logado:', userLoggedIn);
    
    const loginButtons = document.querySelectorAll('.login-buttons');
    const userMenus = document.querySelectorAll('.user-menu');
    
    if (userLoggedIn) {
        // Esconder botões de login e mostrar menu do usuário
        loginButtons.forEach(el => el.style.display = 'none');
        userMenus.forEach(el => el.style.display = 'flex');
        
        // Atualizar nome do usuário nos menus
        const userName = localStorage.getItem('userName');
        document.querySelectorAll('.user-name-display').forEach(el => {
            el.textContent = userName || 'Usuário';
        });
    } else {
        // Mostrar botões de login e esconder menu do usuário
        loginButtons.forEach(el => el.style.display = 'flex');
        userMenus.forEach(el => el.style.display = 'none');
    }
}
