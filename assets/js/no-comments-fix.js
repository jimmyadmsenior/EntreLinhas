/**
 * Script específico para garantir que a mensagem "Nenhum comentário ainda" seja visível no modo escuro
 */
document.addEventListener('DOMContentLoaded', function() {
    // Função para aplicar estilos ao elemento .no-comments
    function fixNoCommentsVisibility() {
        // Verificar se o elemento existe
        const noComments = document.querySelector('.no-comments');
        if (!noComments) return;
        
        // Verificar se o modo escuro está ativo
        const isDarkMode = document.body.classList.contains('dark-mode');
        
        if (isDarkMode) {
            // Aplicar estilos diretos com !important para garantir visibilidade
            noComments.setAttribute('style', 'background-color: #2d2d2d !important; color: #ffffff !important; border: 1px solid #444 !important');
            
            // Para cada elemento filho, garantir que seja visível
            const allChildNodes = noComments.querySelectorAll('*');
            allChildNodes.forEach(function(node) {
                node.setAttribute('style', 'color: #ffffff !important');
            });
        }
    }
    
    // Executar a função imediatamente
    fixNoCommentsVisibility();
    
    // Executar novamente após um curto atraso
    setTimeout(fixNoCommentsVisibility, 100);
    
    // E também após um atraso maior para garantir que o DOM está completamente carregado
    setTimeout(fixNoCommentsVisibility, 500);
    
    // Observar mudanças no modo do tema
    const observer = new MutationObserver(function() {
        fixNoCommentsVisibility();
    });
    
    // Observar mudanças na classe do body para detectar alterações no tema
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
});
