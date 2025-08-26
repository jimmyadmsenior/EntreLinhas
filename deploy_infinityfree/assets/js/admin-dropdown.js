/**
 * Script específico para o menu dropdown no painel de administração
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin dropdown script loaded');
    
    // Menu dropdown do usuário
    const userMenu = document.querySelector('.user-menu');
    
    if (userMenu) {
        console.log('Menu de usuário encontrado');
        
        // Adicionar ID ao menu para seleção mais fácil
        userMenu.id = 'user-menu';
        
        // Adicionar evento de clique para alternar a classe 'active'
        userMenu.addEventListener('click', function(e) {
            console.log('Menu de usuário clicado');
            this.classList.toggle('active');
            console.log('Estado do menu:', this.classList.contains('active') ? 'ativo' : 'inativo');
        });
        
        // Fechar o menu quando clicar fora dele
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
                console.log('Menu fechado (clique fora)');
            }
        });
    } else {
        console.warn('Menu de usuário não encontrado no DOM');
    }
    
    // Trocar entre abas
    const tabs = document.querySelectorAll('.admin-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remover classe ativa de todas as abas
            tabs.forEach(t => t.classList.remove('active'));
            
            // Adicionar classe ativa à aba clicada
            tab.classList.add('active');
            
            // Esconder todo o conteúdo das abas
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            
            // Mostrar o conteúdo da aba selecionada
            const tabId = tab.getAttribute('data-tab');
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.style.display = 'block';
            } else {
                console.warn(`Conteúdo da aba ${tabId} não encontrado`);
            }
        });
    });
});
