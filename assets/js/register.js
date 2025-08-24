/**
 * Script para processamento do formulário de cadastro usando localStorage
 */

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    const alertContainer = document.getElementById('alert-container');
    
    if (!registerForm) return;
    
    // Função para mostrar mensagens de alerta
    function showAlert(message, type) {
        alertContainer.textContent = message;
        alertContainer.className = `alert ${type}`;
        alertContainer.style.display = 'block';
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            alertContainer.style.display = 'none';
        }, 5000);
    }

    // Limpar erros do formulário
    function clearFormErrors() {
        const formGroups = registerForm.querySelectorAll('.form-group');
        formGroups.forEach(group => group.classList.remove('has-error'));
        const errorMessages = registerForm.querySelectorAll('.error-message');
        errorMessages.forEach(message => { message.textContent = ''; });
    }

    // Mostrar erro em um campo específico
    function displayFieldError(fieldId, errorMessage) {
        const field = document.getElementById(fieldId);
        const errorElement = document.getElementById(`${fieldId}-error`);
        if (field && errorElement) {
            field.parentElement.classList.add('has-error');
            errorElement.textContent = errorMessage;
            field.focus();
        }
    }
    
    // Validar formato de email
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    // Processar o envio do formulário
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulário de cadastro enviado');
        
        clearFormErrors();
        
        // Obter valores do formulário
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const terms = document.getElementById('terms').checked;
        
        // Validação básica dos campos
        let isValid = true;
        
        if (!name) {
            displayFieldError('name', 'O nome é obrigatório');
            isValid = false;
        }
        
        if (!email) {
            displayFieldError('email', 'O email é obrigatório');
            isValid = false;
        } else if (!isValidEmail(email)) {
            displayFieldError('email', 'Por favor, insira um email válido');
            isValid = false;
        }
        
        if (!password) {
            displayFieldError('password', 'A senha é obrigatória');
            isValid = false;
        } else if (password.length < 6) {
            displayFieldError('password', 'A senha deve ter pelo menos 6 caracteres');
            isValid = false;
        }
        
        if (password !== confirmPassword) {
            displayFieldError('confirm-password', 'As senhas não coincidem');
            isValid = false;
        }
        
        if (!terms) {
            showAlert('Você precisa aceitar os termos para continuar', 'error');
            isValid = false;
        }
        
        if (!isValid) {
            return;
        }
        
        // Mostrar estado de carregamento
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Cadastrando...';
        submitBtn.disabled = true;
        
        // Simular tempo de processamento (remover em produção)
        setTimeout(() => {
            // Criar usuário demo no localStorage
            const newUser = {
                id: Date.now().toString(), // ID único baseado no timestamp
                nome: name,
                email: email,
                senha: password, // Nota: em um sistema real, nunca armazenar senhas em texto plano
                tipo: 'usuario',
                dataCadastro: new Date().toISOString()
            };
            
            // Salvar usuário em localStorage
            // Em um sistema real, isso seria feito no servidor
            const userList = JSON.parse(localStorage.getItem('users') || '[]');
            
            // Verificar se o email já existe
            const emailExists = userList.some(user => user.email.toLowerCase() === email.toLowerCase());
            if (emailExists) {
                showAlert('Este email já está cadastrado. Por favor, use outro email.', 'error');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                return;
            }
            
            userList.push(newUser);
            localStorage.setItem('users', JSON.stringify(userList));
            
            // Exibir mensagem de sucesso
            showAlert('Cadastro realizado com sucesso!', 'success');
            
            // Fazer login automaticamente
            localStorage.setItem('userLoggedIn', 'true');
            localStorage.setItem('userName', newUser.nome);
            localStorage.setItem('userEmail', newUser.email);
            localStorage.setItem('userType', newUser.tipo);
            localStorage.setItem('userId', newUser.id);
            
            // Redirecionar após um pequeno delay
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
            
        }, 1000);
    });
});
