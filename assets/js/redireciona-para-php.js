/**
 * Este script redireciona automaticamente todas as páginas HTML para suas versões PHP
 * Adicionar no topo de cada arquivo HTML com:
 * <script src="../assets/js/redireciona-para-php.js"></script>
 */
(function() {
    // Se estamos em uma página HTML
    if (window.location.pathname.toLowerCase().endsWith('.html')) {
        // Criar o caminho da versão PHP
        const phpPath = window.location.pathname.replace(/\.html$/i, '.php');
        
        // Parâmetros da URL atual
        const urlParams = window.location.search;
        
        // Redirecionar imediatamente
        window.location.replace(phpPath + urlParams);
    }
})();
