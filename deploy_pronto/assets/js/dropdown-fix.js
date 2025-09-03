/**
 * Correção para o menu dropdown
 * Este script é incluído em último lugar para garantir que os comportamentos corretos sejam aplicados
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Correções do menu dropdown carregadas');
    
    function fixDropdownBehavior() {
        // Função para garantir que o menu dropdown funcione
        const userMenu = document.querySelector('.user-menu');
        if (!userMenu) {
            console.log('Menu do usuário não encontrado');
            return;
        }
        
        console.log('Configurando comportamento do menu dropdown');
        
        // 1. Configurar toggle no clique do nome do usuário
        const userNameDiv = userMenu.querySelector('.user-name');
        if (userNameDiv) {
            userNameDiv.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                userMenu.classList.toggle('active');
                console.log('Menu toggled:', userMenu.classList.contains('active') ? 'ativo' : 'inativo');
            };
        }
        
        // 2. Fechar dropdown quando clicar fora
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
        
        // 3. Garantir que os links funcionem sem problemas
        const menuLinks = userMenu.querySelectorAll('.dropdown-menu a:not(#logout-link)');
        menuLinks.forEach(link => {
            // Remover qualquer handler que possa estar interferindo
            const href = link.getAttribute('href');
            console.log(`Corrigindo link: ${link.textContent} -> ${href}`);
            
            // Garantir que o link funcione como um link normal
            link.addEventListener('click', function(e) {
                // Não prevenir padrão - permitir navegação normal
                userMenu.classList.remove('active'); // Fechar o dropdown ao clicar
            });
        });
        
        // 4. Configurar link de logout separadamente
        const logoutLink = userMenu.querySelector('#logout-link');
        if (logoutLink) {
            logoutLink.onclick = function(e) {
                e.preventDefault();
                
                // Limpar localStorage
                localStorage.removeItem('userLoggedIn');
                localStorage.removeItem('userName');
                localStorage.removeItem('userEmail');
                localStorage.removeItem('userType');
                localStorage.removeItem('userId');
                
                alert('Você foi desconectado com sucesso!');
                
                // Redirecionar para a página inicial
                const path = window.location.pathname.toLowerCase();
                const redirectTo = path.includes('/pages/') ? 'index.html' : 'PAGES/index.html';
                window.location.href = redirectTo;
            };
        }
    }
    
    // Executar imediatamente
    fixDropdownBehavior();
    
    // E novamente após um breve atraso para garantir que todo o DOM foi carregado
    setTimeout(fixDropdownBehavior, 1000);
});
