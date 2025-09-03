/**
 * Este script sincroniza os dados entre cookies e localStorage
 * para garantir que o estado de login seja consistente em todas as páginas.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Função para obter valor de cookie por nome
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    // Verificar se os cookies estão definidos e atualizar localStorage
    const userLoggedInCookie = getCookie('userLoggedIn');
    const userNameCookie = getCookie('userName');
    const userEmailCookie = getCookie('userEmail');
    const userTypeCookie = getCookie('userType');
    const userIdCookie = getCookie('userId');

    if (userLoggedInCookie === 'true' && userNameCookie) {
        // Atualizar localStorage com dados dos cookies
        localStorage.setItem('userLoggedIn', 'true');
        localStorage.setItem('userName', userNameCookie);
        localStorage.setItem('userEmail', userEmailCookie || '');
        localStorage.setItem('userType', userTypeCookie || '');
        localStorage.setItem('userId', userIdCookie || '');
    } else {
        // Verificar se localStorage tem informações que os cookies não têm
        const userLoggedInLS = localStorage.getItem('userLoggedIn');
        const userNameLS = localStorage.getItem('userName');
        
        if (userLoggedInLS === 'true' && userNameLS && !userLoggedInCookie) {
            // Se o localStorage indicar que o usuário está logado, mas os cookies não,
            // redirecionar para a página de login para sincronizar o estado
            window.location.href = 'PAGES/login.php?redirect=' + encodeURIComponent(window.location.href);
        }
    }
});
