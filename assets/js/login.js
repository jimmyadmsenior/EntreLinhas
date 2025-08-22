/**
 * Script para processamento do formulário de login
 */
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Validar o formulário
            if (!validateForm(loginForm)) return;
            
            // Prepare form data
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            // Show loading state
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Entrando...';
            submitBtn.disabled = true;
            
            // Create form data object
            const formData = new FormData();
            formData.append('email', email);
            formData.append('senha', password); // Note: backend expects 'senha' not 'password'
            formData.append('remember', remember);
            
            // Send login request to server - Use the new JSON API endpoint
            fetch('../backend/process_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na rede ao tentar fazer login.');
                }
                return response.json(); // Now expecting JSON response
            })
            .then(data => {
                // Check if login was successful
                if (data.success) {
                    // Show success message briefly
                    let successDiv = document.getElementById('login-success');
                    if (!successDiv) {
                        successDiv = document.createElement('div');
                        successDiv.id = 'login-success';
                        successDiv.className = 'alert alert-success';
                        loginForm.prepend(successDiv);
                    }
                    successDiv.textContent = 'Login bem-sucedido! Redirecionando...';
                    
                    // Redirect based on user type
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    // Show error message
                    let errorDiv = document.getElementById('login-error');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.id = 'login-error';
                        errorDiv.className = 'alert alert-danger';
                        loginForm.prepend(errorDiv);
                    }
                    
                    errorDiv.textContent = data.message;
                    
                    // Reset button
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                
                // Create error display
                let errorDiv = document.getElementById('login-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'login-error';
                    errorDiv.className = 'alert alert-danger';
                    loginForm.prepend(errorDiv);
                }
                errorDiv.textContent = 'Erro ao tentar fazer login. Tente novamente mais tarde.';
                
                // Reset button
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
