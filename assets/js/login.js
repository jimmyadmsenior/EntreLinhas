/**
 * Script para processamento do formulário de login usando localStorage
 */
document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM carregado - login.js executando");
    const loginForm = document.getElementById("login-form");
    
    if (!loginForm) {
        console.error("Formulário de login não encontrado!");
        return;
    }
    
    console.log("Formulário de login encontrado:", loginForm);
    
    // Verificar se há parâmetro de forçar logout na URL
    const urlParams = new URLSearchParams(window.location.search);
    const forceLogout = urlParams.get("logout");
    
    if (forceLogout === "true") {
        // Limpar dados de login
        localStorage.removeItem("userLoggedIn");
        localStorage.removeItem("userName");
        localStorage.removeItem("userEmail");
        localStorage.removeItem("userType");
        localStorage.removeItem("userId");
        console.log("Logout forçado realizado");
    }
    
    // Verificar se já existe um usuário logado
    const isLoggedIn = localStorage.getItem("userLoggedIn") === "true";
    if (isLoggedIn && forceLogout !== "true") {
        // Redirecionar para a página inicial se já estiver logado
        console.log("Usuário já logado, redirecionando para index.php");
        window.location.href = "../index.php";
        return;
    }
    
    // Definindo usuários de teste para demonstração
    const demoUsers = [
        { 
            email: "admin@exemplo.com", 
            senha: "admin123", 
            nome: "Administrador",
            tipo: "admin",
            id: "1"
        },
        { 
            email: "usuario@exemplo.com", 
            senha: "usuario123", 
            nome: "Usuário Teste",
            tipo: "usuario",
            id: "2"
        }
    ];
    
    // Buscar usuários registrados no localStorage
    const registeredUsers = JSON.parse(localStorage.getItem("users") || "[]");
    console.log("Usuários registrados:", registeredUsers);
    
    // Combinar com os usuários de demonstração
    const allUsers = [...demoUsers, ...registeredUsers];
    
    loginForm.addEventListener("submit", function(e) {
        console.log("Formulário de login enviado!");
        e.preventDefault();
        
        // Preparar dados do formulário
        const email = document.getElementById("email").value.trim().toLowerCase();
        const password = document.getElementById("password").value;
        const remember = document.getElementById("remember")?.checked || false;
        
        console.log("Email fornecido:", email);
        console.log("Lembrar de mim:", remember);
        
        // Mostrar estado de carregamento
        const submitBtn = loginForm.querySelector("button[type='submit']");
        const originalText = submitBtn.textContent;
        submitBtn.textContent = "Entrando...";
        submitBtn.disabled = true;
        
        // Preparar dados para enviar ao backend
        const formData = new FormData();
        formData.append('email', email);
        formData.append('senha', password);
        
        // Enviar solicitação de login para o backend PHP
        fetch('../backend/process_login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("Resposta do servidor:", data);
            
            if (data.success) {
                // Login bem-sucedido
                let successDiv = document.getElementById("login-success");
                if (!successDiv) {
                    successDiv = document.createElement("div");
                    successDiv.id = "login-success";
                    successDiv.className = "alert alert-success";
                    loginForm.prepend(successDiv);
                }
                successDiv.textContent = data.message || "Login bem-sucedido! Redirecionando...";
                
                try {
                    // Salvar dados do usuário no localStorage como backup
                    localStorage.setItem("userLoggedIn", "true");
                    
                    // Os cookies já foram definidos pelo PHP, mas mantemos no localStorage também
                    // para compatibilidade com o sistema existente
                    if (data.user_name) localStorage.setItem("userName", data.user_name);
                    if (data.user_email) localStorage.setItem("userEmail", data.user_email);
                    if (data.user_type) localStorage.setItem("userType", data.user_type);
                    if (data.user_id) localStorage.setItem("userId", data.user_id);
                    
                    console.log("Dados do usuário salvos no localStorage");
                } catch (error) {
                    console.error("Erro ao salvar dados do usuário:", error);
                }
                
                // Redirecionar após um pequeno delay
                setTimeout(() => {
                    console.log(`Redirecionando para ${data.redirect || 'index.html'}`);
                    window.location.href = data.redirect || "index.html";
                }, 1000);
                
            } else {
                // Login falhou
                let errorDiv = document.getElementById("login-error");
                if (!errorDiv) {
                    errorDiv = document.createElement("div");
                    errorDiv.id = "login-error";
                    errorDiv.className = "alert alert-danger";
                    loginForm.prepend(errorDiv);
                }
                
                errorDiv.textContent = data.message || "Email ou senha incorretos. Por favor, tente novamente.";
                
                // Restaurar botão
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error("Erro na solicitação:", error);
            
            // Mostrar erro
            let errorDiv = document.getElementById("login-error");
            if (!errorDiv) {
                errorDiv = document.createElement("div");
                errorDiv.id = "login-error";
                errorDiv.className = "alert alert-danger";
                loginForm.prepend(errorDiv);
            }
            errorDiv.textContent = "Erro ao conectar com o servidor. Por favor, tente novamente.";
            
            // Restaurar botão
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
});