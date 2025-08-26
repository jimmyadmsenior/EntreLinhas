/**
 * Script personalizado para a página de login
 * Este script substitui os scripts de verificação de autenticação
 * para evitar loops de redirecionamento na página de login.
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de login carregado');
    
    // Limpar o localStorage para evitar conflitos com verificações de sessão
    localStorage.removeItem('userLoggedIn');
    
    // Função para salvar dados do usuário após login bem-sucedido
    window.saveUserData = function(userData) {
        console.log('Salvando dados do usuário', userData);
        localStorage.setItem('userLoggedIn', 'true');
        
        if (userData.nome) localStorage.setItem('userName', userData.nome);
        if (userData.email) localStorage.setItem('userEmail', userData.email);
        if (userData.tipo) localStorage.setItem('userType', userData.tipo);
        if (userData.id) localStorage.setItem('userId', userData.id);
    };
});
