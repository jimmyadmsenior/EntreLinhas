/**
 * Script para sincronizar cookies com localStorage
 * Este script é importante para manter o estado de autenticação consistente
 */
document.addEventListener('DOMContentLoaded', function() {
    // Interromper se estiver na página de login ou cadastro
    if (window.location.pathname.toLowerCase().includes('login.php') || 
        window.location.pathname.toLowerCase().includes('cadastro.php') ||
        window.location.pathname.toLowerCase().includes('registro.php')) {
        console.log('Página de autenticação detectada, desativando sincronização de cookies');
        return; // Sai completamente da função
    }
    
    // Função para obter valor de cookie por nome
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
        return null;
    }
    
    // Verificar se os cookies de login estão definidos
    const userLoggedIn = getCookie('userLoggedIn');
    const userName = getCookie('userName');
    const userEmail = getCookie('userEmail');
    const userType = getCookie('userType');
    const userId = getCookie('userId');
    
    console.log('Verificando cookies de autenticação:');
    console.log('- userLoggedIn:', userLoggedIn);
    console.log('- userName:', userName);
    
    // Se os cookies indicarem que o usuário está logado
    if (userLoggedIn === 'true' && userName) {
        console.log('Cookies indicam que o usuário está logado. Sincronizando com localStorage...');
        
        // Atualizar localStorage
        localStorage.setItem('userLoggedIn', 'true');
        localStorage.setItem('userName', userName);
        localStorage.setItem('userEmail', userEmail || '');
        localStorage.setItem('userType', userType || '');
        localStorage.setItem('userId', userId || '');
        
        console.log('localStorage atualizado com dados dos cookies.');
    } 
    // Se os cookies não estão definidos, mas localStorage indica login
    else {
        const lsLoggedIn = localStorage.getItem('userLoggedIn');
        const lsUserName = localStorage.getItem('userName');
        
        console.log('Verificando localStorage:');
        console.log('- userLoggedIn:', lsLoggedIn);
        console.log('- userName:', lsUserName);
        
        if (lsLoggedIn === 'true' && lsUserName && !userLoggedIn) {
            console.log('localStorage indica login mas cookies não. Possível problema de sessão.');
            
            // Se estamos em uma página .html, redirecionar para login, 
            // mas não se já estivermos em login.php para evitar loops
            if (window.location.pathname.toLowerCase().endsWith('.html') && 
                !window.location.pathname.toLowerCase().includes('login')) {
                console.log('Redirecionando para login para sincronizar sessão...');
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            } else {
                // Se não estivermos em uma página HTML ou estivermos na página de login,
                // apenas limpar o localStorage para evitar inconsistências
                localStorage.removeItem('userLoggedIn');
                localStorage.removeItem('userName');
                localStorage.removeItem('userEmail');
                localStorage.removeItem('userType');
                localStorage.removeItem('userId');
                console.log('localStorage limpo para corrigir inconsistência');
            }
        }
    }
});
