/**
 * Script para gerenciar o menu dropdown do usuário
 */

document.addEventListener('DOMContentLoaded', function() {
    setupUserMenuEvents();
});

function setupUserMenuEvents() {
    // Buscar pelo menu de usuário
    const userMenus = document.querySelectorAll('.user-menu');
    
    if (userMenus.length > 0) {
        console.log('Menus de usuário encontrados:', userMenus.length);
        
        userMenus.forEach(userMenu => {
            // Remover quaisquer listeners antigos (para evitar duplicação)
            userMenu.removeEventListener('click', toggleUserMenu);
            
            // Adicionar novo listener de clique apenas para o cabeçalho do menu, não para os links
            const userNameElement = userMenu.querySelector('.user-name');
            if (userNameElement) {
                userNameElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userMenu.classList.toggle('active');
                    console.log('Menu ativo:', userMenu.classList.contains('active'));
                });
            }
            
            // Garantir que os links do dropdown funcionem normalmente
            const dropdownLinks = userMenu.querySelectorAll('.dropdown-link');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Não prevenimos o comportamento padrão para os links
                    console.log('Link clicado:', link.href);
                });
            });
        });
        
        // Fechar o dropdown quando clicar fora dele
        document.addEventListener('click', function(e) {
            userMenus.forEach(userMenu => {
                if (!userMenu.contains(e.target)) {
                    userMenu.classList.remove('active');
                }
            });
        });
    } else {
        console.log('Nenhum menu de usuário encontrado');
    }
}

// A função toggleUserMenu foi integrada diretamente no setupUserMenuEvents
