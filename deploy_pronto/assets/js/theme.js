/**
 * Script para controlar o tema claro/escuro no site EntreLinhas
 * Este script manipula o tema e salva a preferência do usuário no localStorage
 */
document.addEventListener('DOMContentLoaded', function() {
    // Selecionar o botão de alternar tema
    const themeToggle = document.getElementById('theme-toggle');
    // Selecionar o ícone dentro do botão
    const themeIcon = themeToggle ? themeToggle.querySelector('i') : null;

    // Função para definir o tema
    function setTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
            if (themeIcon) themeIcon.className = 'fas fa-sun';
        } else {
            document.body.classList.remove('dark-mode');
            if (themeIcon) themeIcon.className = 'fas fa-moon';
        }
        // Salvar a preferência no localStorage
        localStorage.setItem('theme', theme);
    }

    // Verificar se o usuário já tem uma preferência de tema
    const savedTheme = localStorage.getItem('theme');
    
    // Aplicar o tema salvo ou o padrão (claro)
    if (savedTheme) {
        setTheme(savedTheme);
    } else {
        // Se preferir detectar automaticamente o tema do sistema:
        const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        setTheme(prefersDarkMode ? 'dark' : 'light');
    }

    // Adicionar evento de clique ao botão para alternar o tema
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // Verificar o tema atual e alternar
            const currentTheme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });
    }

    // Observar alterações nas preferências do sistema
    if (window.matchMedia) {
        const colorSchemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
        if (colorSchemeQuery.addEventListener) {
            colorSchemeQuery.addEventListener('change', (e) => {
                // Somente alterar automaticamente se o usuário não definiu uma preferência
                if (!localStorage.getItem('theme')) {
                    setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }
});
