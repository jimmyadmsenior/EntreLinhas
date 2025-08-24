/**
 * Script para redirecionar automaticamente de páginas HTML para PHP
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar a URL atual
    const currentPath = window.location.pathname;
    
    // Se a URL termina com .html, redirecionar para a versão .php
    if (currentPath.toLowerCase().endsWith('.html')) {
        const newPath = currentPath.replace(/\.html$/i, '.php');
        window.location.href = newPath;
    }
});
