/**
 * Script para melhorar a visibilidade dos artigos no modo escuro
 * Este script adiciona classes específicas para elementos dentro do conteúdo do artigo
 * quando o modo escuro está ativado.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Verificar se estamos em uma página de artigo
    const articleContent = document.querySelector('.article-content');
    if (!articleContent) return;
    
    // Função para aplicar estilos ao conteúdo do artigo quando no modo escuro
    function applyDarkModeStyles() {
        const isDarkMode = document.body.classList.contains('dark-mode');
        
        // Aplicar estilo diretamente ao container do artigo para garantir herança de cores
        if (isDarkMode) {
            // Aplicar uma regra geral para todos os elementos dentro do conteúdo
            articleContent.style.color = '#ffffff';
            
            // Adicionar estilo ao contêiner principal para garantir que o conteúdo seja visível
            articleContent.style.backgroundColor = '#1e1e1e';
            
            // Adicionar uma folha de estilo diretamente no documento
            // Isso nos permite definir regras mais específicas sem precisar selecionar cada elemento
            if (!document.getElementById('dark-mode-article-styles')) {
                const styleEl = document.createElement('style');
                styleEl.id = 'dark-mode-article-styles';
                styleEl.textContent = `
                    .dark-mode .article-content * {
                        color: #ffffff !important;
                    }
                    .dark-mode .article-content a {
                        color: #add8e6 !important;
                    }
                    .dark-mode .article-content h1, 
                    .dark-mode .article-content h2, 
                    .dark-mode .article-content h3, 
                    .dark-mode .article-content h4, 
                    .dark-mode .article-content h5, 
                    .dark-mode .article-content h6 {
                        color: #ffffff !important;
                    }
                    .dark-mode .article-content table {
                        border-color: #444 !important;
                    }
                    .dark-mode .article-content td,
                    .dark-mode .article-content th {
                        border-color: #444 !important;
                    }
                    .dark-mode .article-content code,
                    .dark-mode .article-content pre {
                        background-color: #2d2d2d !important;
                        color: #f8f8f8 !important;
                    }
                `;
                document.head.appendChild(styleEl);
            }
        } else {
            // Restaurar estilos para modo claro
            articleContent.style.color = '';
            articleContent.style.backgroundColor = '';
            
            // Remover a folha de estilo se existir
            const darkStyleSheet = document.getElementById('dark-mode-article-styles');
            if (darkStyleSheet) {
                darkStyleSheet.remove();
            }
        }
    }
    
    // Aplicar estilos inicialmente
    applyDarkModeStyles();
    
    // Observar mudanças no tema
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                applyDarkModeStyles();
            }
        });
    });
    
    // Observar mudanças na classe do body para detectar alterações de tema
    observer.observe(document.body, { attributes: true });
    
    // Também verificar quando o localStorage do tema mudar
    window.addEventListener('storage', (event) => {
        if (event.key === 'theme') {
            applyDarkModeStyles();
        }
    });
});
