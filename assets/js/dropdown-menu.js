/**
 * Este script garante que o menu dropdown do usuário funcione corretamente
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configurar o menu dropdown do usuário
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
        const userNameElement = userMenu.querySelector('.user-name');
        const dropdownMenu = document.getElementById('user-dropdown-menu');
        
        if (userNameElement && dropdownMenu) {
            // Adicionar evento de clique ao nome do usuário
            userNameElement.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });
            
            // Fechar o dropdown quando clicar fora dele
            document.addEventListener('click', function(e) {
                if (!userMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
            
            console.log('Menu de usuário inicializado');
        }
    }
});
