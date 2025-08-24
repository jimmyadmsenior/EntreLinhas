/**
 * Script para verificar e sincronizar o estado de login
 * Este script deve ser incluído em todas as páginas HTML
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos em uma página HTML
    const isPHPPage = window.location.pathname.endsWith('.php');
    const isHTMLPage = window.location.pathname.endsWith('.html');
    
    // Se não for uma página PHP ou HTML, não fazer nada
    if (!isPHPPage && !isHTMLPage) return;
    
    console.log('Verificando estado de login...');
    
    // Obter o caminho base correto
    const basePath = window.location.pathname.toLowerCase().includes('/pages/') ? '' : 'PAGES/';
    console.log('Caminho base calculado:', basePath);
    
    // Função para verificar o estado de login do servidor
    function verificarLoginServidor() {
        fetch(basePath + 'verificar_login.php')
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
                        
                        // Recarregar a página para atualizar a interface
                        window.location.reload();
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
