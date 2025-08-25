/**
 * Este script corrige problemas de visibilidade no modo escuro
 * Ele adiciona uma classe CSS específica para tornar o texto visível no modo escuro
 */
document.addEventListener('DOMContentLoaded', function() {
    // Função para aplicar a correção
    function fixDarkModeVisibility() {
        // Verificar se o modo escuro está ativo
        if (document.body.classList.contains('dark-mode')) {
            // Selecionar todos os elementos que podem conter texto no conteúdo do artigo
            const allElements = document.querySelectorAll('.article-content *');
            
            // Aplicar a cor branca a todos os elementos dentro do conteúdo do artigo
            allElements.forEach(function(element) {
                // Ignorar elementos de imagem, que não precisam de texto branco
                if (element.tagName !== 'IMG') {
                    element.style.setProperty('color', '#ffffff', 'important');
                }
            });
            
            // Aplicar estilos específicos para links
            const links = document.querySelectorAll('.article-content a');
            links.forEach(function(link) {
                link.style.setProperty('color', '#add8e6', 'important');
            });
            
            // Garantir que o fundo do artigo esteja escuro
            const articleContent = document.querySelector('.article-content');
            if (articleContent) {
                articleContent.style.setProperty('background-color', '#1e1e1e', 'important');
            }
            
            // Garantir que o cabeçalho do artigo também esteja visível
            const articleHeader = document.querySelector('.article-header');
            if (articleHeader) {
                articleHeader.style.setProperty('background-color', '#1e1e1e', 'important');
                articleHeader.querySelectorAll('*').forEach(function(element) {
                    if (element.tagName !== 'IMG') {
                        element.style.setProperty('color', '#ffffff', 'important');
                    }
                });
            }
            
            // Garantir que a seção de comentários também esteja visível
            const commentsSection = document.querySelector('.comments-section');
            if (commentsSection) {
                commentsSection.style.setProperty('background-color', '#1e1e1e', 'important');
                
                // Aplicar estilo no elemento de comentários e todos seus descendentes
                commentsSection.querySelectorAll('*:not(textarea):not(button)').forEach(function(element) {
                    element.style.setProperty('color', '#ffffff', 'important');
                });
                
                // Especificamente para o texto "Nenhum comentário ainda"
                const noComments = document.querySelector('.no-comments');
                if (noComments) {
                    noComments.style.setProperty('background-color', '#2d2d2d', 'important');
                    noComments.style.setProperty('color', '#ffffff', 'important');
                    
                    // Garantir que todos os elementos dentro de no-comments também tenham cor branca
                    noComments.querySelectorAll('*').forEach(function(el) {
                        el.style.setProperty('color', '#ffffff', 'important');
                    });
                }
                
                // Campo de texto para comentário
                const commentTextarea = commentsSection.querySelector('textarea');
                if (commentTextarea) {
                    commentTextarea.style.setProperty('background-color', '#2d2d2d', 'important');
                    commentTextarea.style.setProperty('color', '#ffffff', 'important');
                    commentTextarea.style.setProperty('border-color', '#444', 'important');
                }
            }
        }
    }
    
    // Aplicar correção imediatamente após o carregamento da página
    fixDarkModeVisibility();
    
    // Observar alterações na classe do body para detectar mudanças no tema
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                fixDarkModeVisibility();
            }
        });
    });
    
    // Configurar o observer para monitorar alterações na classe do body
    observer.observe(document.body, { attributes: true });
    
    // Também aplicar a correção quando o localStorage do tema mudar
    window.addEventListener('storage', function(event) {
        if (event.key === 'theme') {
            fixDarkModeVisibility();
        }
    });
});
