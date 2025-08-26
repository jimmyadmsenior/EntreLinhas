/**
 * Script para verificar e sincronizar o estado de login
 * Este script deve ser incluído em todas as páginas HTML
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos na página de login ou cadastro e interromper completamente se for o caso
    if (window.location.pathname.toLowerCase().includes('login.php') || 
        window.location.pathname.toLowerCase().includes('cadastro.php') ||
        window.location.pathname.toLowerCase().includes('registro.php')) {
        console.log('Página de autenticação detectada, desativando verificação de sincronização de login');
        return; // Sai completamente da função, não executa nada abaixo
    }
    
    // Verificar se estamos em uma página HTML
    const isPHPPage = window.location.pathname.endsWith('.php');
    const isHTMLPage = window.location.pathname.endsWith('.html');
    
    // Se não for uma página PHP ou HTML, não fazer nada
    if (!isPHPPage && !isHTMLPage) return;
    
    console.log('Verificando estado de login...');
    
    // Obter o caminho base correto
    // Lógica mais robusta para determinar o caminho base
    let basePath = '';
    if (window.location.pathname.toLowerCase().includes('/pages/')) {
        // Estamos em uma página dentro da pasta PAGES
        basePath = '';
    } else if (window.location.pathname.toLowerCase().includes('/entrelinhas/pages/')) {
        // Estamos em uma página dentro da pasta PAGES com o nome do projeto na URL
        basePath = '';
    } else {
        // Estamos na raiz do projeto
        basePath = 'PAGES/';
    }
    console.log('Caminho base calculado:', basePath);
    
    // Função para verificar o estado de login do servidor
    function verificarLoginServidor() {
        // Evitar verificação na página de login para prevenir loops
        if (window.location.pathname.toLowerCase().includes('login.php')) {
            console.log('Página de login detectada, pulando verificação de login');
            return;
        }
        
        // Construir URL relativa para o arquivo verificar_login.php
        const baseURL = window.location.origin + '/' + window.location.pathname.split('/')[1]; // obter a parte EntreLinhas
        fetch(baseURL + '/PAGES/verificar_login.php')
            .then(response => response.json())
            .then(data => {
                console.log('Resposta do servidor:', data);
                
                // Se o servidor diz que o usuário está logado
                if (data.logado) {
                    console.log('Servidor: usuário logado');
                    
                    // Atualizar localStorage
                    localStorage.setItem('userLoggedIn', 'true');
                    localStorage.setItem('userName', data.dados.nome);
                    localStorage.setItem('userEmail', data.dados.email);
                    localStorage.setItem('userType', data.dados.tipo);
                    localStorage.setItem('userId', data.dados.id);
                    
                    console.log('localStorage atualizado com dados da sessão PHP');
                    
                    // Se estamos em uma página HTML, redirecionar para a versão PHP
                    if (isHTMLPage) {
                        console.log('Redirecionando para versão PHP da página atual');
                        window.location.href = window.location.pathname.replace('.html', '.php');
                    }
                } 
                // Se o servidor diz que o usuário não está logado
                else {
                    console.log('Servidor: usuário não logado');
                    
                    // Verificar se o localStorage indica que o usuário está logado
                    const lsLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
                    
                    if (lsLoggedIn) {
                        console.log('Inconsistência detectada: localStorage indica login mas servidor não');
                        
                        // Limpar localStorage para corrigir inconsistência
                        localStorage.removeItem('userLoggedIn');
                        localStorage.removeItem('userName');
                        localStorage.removeItem('userEmail');
                        localStorage.removeItem('userType');
                        localStorage.removeItem('userId');
                        
                        console.log('localStorage limpo para corrigir inconsistência');
                        
                        // Somente recarregar se não estivermos na página de login
                        // para evitar loop infinito
                        if (!window.location.pathname.includes('login.php')) {
                            window.location.reload();
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao verificar login:', error);
            });
    }
    
    // Verificar login
    verificarLoginServidor();
});
