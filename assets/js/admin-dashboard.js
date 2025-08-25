/**
 * Script específico para o painel de administração
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar o menu dropdown do usuário
    const userMenu = document.getElementById('user-menu');
    if (userMenu) {
        const userNameElement = userMenu.querySelector('.user-name');
        
        if (userNameElement) {
            userNameElement.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                userMenu.classList.toggle('active');
                console.log('Menu de administração clicado - estado ativo:', userMenu.classList.contains('active'));
            });
        }
        
        // Fechar o dropdown quando clicar fora dele
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
    } else {
        console.error('Menu do usuário não encontrado no painel de administração');
    }
    
    // Tab switching functionality
    const tabs = document.querySelectorAll('.admin-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            
            // Show selected tab content
            const tabId = tab.getAttribute('data-tab');
            document.getElementById(tabId).style.display = 'block';
        });
    });
});
