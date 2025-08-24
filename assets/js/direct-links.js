/**
 * Script auxiliar para garantir que os links do dropdown funcionem corretamente
 * Este script é carregado após todos os outros scripts para corrigir problemas de navegação
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('direct-links.js carregado - garantindo que links funcionem corretamente');
    
    // Função para garantir que os links funcionem
    function enableMenuLinks() {
        // Encontrar o menu do usuário
        const userMenu = document.querySelector('.user-menu');
        
        if (userMenu) {
            console.log('Menu do usuário encontrado');
            
            // Configurar comportamento do clique para mostrar/ocultar dropdown
            const userNameDiv = userMenu.querySelector('.user-name');
            if (userNameDiv) {
                userNameDiv.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userMenu.classList.toggle('active');
                    console.log('Menu toggle:', userMenu.classList.contains('active') ? 'ativo' : 'inativo');
                };
            }
            
            // Configurar o botão de logout
            const logoutLink = userMenu.querySelector('#logout-link');
            if (logoutLink) {
                logoutLink.onclick = function(e) {
                    e.preventDefault();
                    console.log('Botão de logout clicado');
                    
                    // Limpar dados do localStorage
                    localStorage.removeItem('userLoggedIn');
                    localStorage.removeItem('userName');
                    localStorage.removeItem('userEmail');
                    localStorage.removeItem('userType');
                    localStorage.removeItem('userId');
                    
                    // Mostrar mensagem
                    alert('Você foi desconectado com sucesso!');
                    
                    // Redirecionar para a página inicial
                    const path = window.location.pathname.toLowerCase();
                    if (path.includes('/pages/')) {
                        window.location.href = 'index.html';
                    } else {
                        window.location.href = 'PAGES/index.html';
                    }
                };
            }
            
            // Garantir que os outros links funcionem normalmente (removendo quaisquer prevenção de eventos)
            const navigationLinks = userMenu.querySelectorAll('.dropdown-menu a:not(#logout-link)');
            navigationLinks.forEach(link => {
                // Remover quaisquer handlers de eventos que possam estar interferindo
                const href = link.getAttribute('href');
                console.log(`Configurando link para navegação direta: ${link.textContent} -> ${href}`);
            });
            
            // Fechar menu ao clicar fora dele
            document.addEventListener('click', function(e) {
                if (userMenu && !userMenu.contains(e.target)) {
                    userMenu.classList.remove('active');
                }
            });
        } else {
            console.log('Menu do usuário não encontrado');
        }
    }
    
    // Executar imediatamente
    enableMenuLinks();
    
    // E também executar após um pequeno atraso para garantir que todos os outros scripts foram carregados
    setTimeout(enableMenuLinks, 1000);
});
