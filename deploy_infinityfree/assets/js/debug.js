/**
 * Debug.js - Utilitário para debug do localStorage e autenticação
 */

// Namespace para evitar conflitos
const EntreLinhasDebug = {
    // Verificar se o localStorage está disponível
    checkLocalStorage: function() {
        try {
            const test = 'test';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            console.log('✓ localStorage está disponível e funcionando');
            return true;
        } catch (e) {
            console.error('✗ localStorage não está disponível:', e);
            return false;
        }
    },
    
    // Exibir todo o conteúdo do localStorage
    dumpLocalStorage: function() {
        console.group('Conteúdo do localStorage:');
        
        if (localStorage.length === 0) {
            console.log('(vazio)');
        } else {
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                const value = localStorage.getItem(key);
                console.log(`${key}: ${value}`);
            }
        }
        
        console.groupEnd();
    },
    
    // Testar o login com o localStorage
    testLogin: function(email, password) {
        console.group('Teste de login com localStorage');
        
        // Usuários de demo para teste
        const users = [
            { email: 'admin@exemplo.com', senha: 'admin123', nome: 'Administrador', tipo: 'admin', id: '1' },
            { email: 'usuario@exemplo.com', senha: 'usuario123', nome: 'Usuário Teste', tipo: 'usuario', id: '2' }
        ];
        
        // Encontrar usuário
        const user = users.find(u => u.email === email && u.senha === password);
        
        if (user) {
            console.log(`✓ Usuário encontrado: ${user.nome}`);
            
            try {
                // Salvar dados do usuário
                localStorage.setItem('userLoggedIn', 'true');
                localStorage.setItem('userName', user.nome);
                localStorage.setItem('userEmail', user.email);
                localStorage.setItem('userType', user.tipo);
                localStorage.setItem('userId', user.id);
                console.log('✓ Dados do usuário salvos no localStorage');
                
                // Verificar se os dados foram realmente salvos
                const check = localStorage.getItem('userLoggedIn') === 'true';
                if (check) {
                    console.log('✓ Verificação bem-sucedida: dados persistidos');
                } else {
                    console.error('✗ Verificação falhou: os dados não foram persistidos');
                }
                
                this.dumpLocalStorage();
                return true;
            } catch (e) {
                console.error('✗ Erro ao salvar dados no localStorage:', e);
                return false;
            }
        } else {
            console.log(`✗ Usuário não encontrado para ${email}`);
            return false;
        }
        
        console.groupEnd();
    },
    
    // Limpar dados de login
    clearLogin: function() {
        try {
            localStorage.removeItem('userLoggedIn');
            localStorage.removeItem('userName');
            localStorage.removeItem('userEmail');
            localStorage.removeItem('userType');
            localStorage.removeItem('userId');
            console.log('✓ Dados de login removidos do localStorage');
            return true;
        } catch (e) {
            console.error('✗ Erro ao remover dados de login:', e);
            return false;
        }
    },
    
    // Inicializar
    init: function() {
        console.log('EntreLinhasDebug inicializado');
        this.checkLocalStorage();
        this.dumpLocalStorage();
        
        // Tornar funções disponíveis globalmente
        window.dumpLocalStorage = this.dumpLocalStorage.bind(this);
        window.testLogin = this.testLogin.bind(this);
        window.clearLogin = this.clearLogin.bind(this);
        
        console.log('Funções de debug disponíveis: dumpLocalStorage(), testLogin(email, senha), clearLogin()');
    }
};

// Auto-inicializar
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado - debug.js executando');
    EntreLinhasDebug.init();
    
    // Verificar tema atual
    const currentTheme = localStorage.getItem('theme');
    console.log('Tema atual:', currentTheme);
    
    // Verificar elementos importantes
    console.log('Formulário de login:', document.getElementById('login-form'));
    console.log('Botão de tema:', document.getElementById('theme-toggle'));
});
