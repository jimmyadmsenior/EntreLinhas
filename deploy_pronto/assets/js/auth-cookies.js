/**
 * Script para gerenciar autenticação com localStorage
 * Este script é importante para manter o estado de autenticação consistente
 */
document.addEventListener('DOMContentLoaded', function() {
    // Interromper se estiver na página de login ou cadastro
    if (window.location.pathname.toLowerCase().includes('login.php') || 
        window.location.pathname.toLowerCase().includes('cadastro.php') ||
        window.location.pathname.toLowerCase().includes('registro.php')) {
        console.log('Página de autenticação detectada, desativando sincronização de autenticação');
        return; // Sai completamente da função
    }
    
    // Verificar se os dados de login estão no localStorage
    const userLoggedIn = localStorage.getItem('userLoggedIn');
    const userName = localStorage.getItem('userName');
    const userEmail = localStorage.getItem('userEmail');
    const userType = localStorage.getItem('userType');
    const userId = localStorage.getItem('userId');
    
    console.log('Verificando cookies de autenticação:');
    console.log('- userLoggedIn:', userLoggedIn);
    console.log('- userName:', userName);
    
    // Se os cookies indicarem que o usuário está logado
    if (userLoggedIn === 'true' && userName) {
        console.log('Cookies indicam que o usuário está logado. Sincronizando com localStorage...');
        
        // Atualizar localStorage
        // Os dados já estão no localStorage a partir do header.php
        console.log('Usando dados do localStorage para autenticação.');
    } 
    // Se o localStorage não tem dados de login mas a sessão PHP indica que há um login,
    // o localStorage pode ter sido limpo pelo usuário
    else {
        const lsLoggedIn = localStorage.getItem('userLoggedIn');
        const lsUserName = localStorage.getItem('userName');
        
        console.log('Verificando localStorage:');
        console.log('- userLoggedIn:', lsLoggedIn);
        console.log('- userName:', lsUserName);
        
        if (lsLoggedIn !== 'true' && userLoggedIn === 'true') {
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
