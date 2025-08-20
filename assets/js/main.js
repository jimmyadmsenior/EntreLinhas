document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    
    // Check for saved theme preference or use preferred color scheme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        updateThemeIcon(true);
    } else if (savedTheme === 'light') {
        body.classList.remove('dark-mode');
        updateThemeIcon(false);
    } else {
        // Check if user prefers dark mode at OS level
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            body.classList.add('dark-mode');
            updateThemeIcon(true);
        }
    }
    
    themeToggle.addEventListener('click', () => {
        // Toggle dark mode class
        body.classList.toggle('dark-mode');
        
        // Update the theme icon
        const isDarkMode = body.classList.contains('dark-mode');
        updateThemeIcon(isDarkMode);
        
        // Save preference to localStorage
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
    });
    
    function updateThemeIcon(isDarkMode) {
        themeToggle.innerHTML = isDarkMode ? 
            '<i class="fas fa-sun"></i>' : 
            '<i class="fas fa-moon"></i>';
        themeToggle.setAttribute('aria-label', isDarkMode ? 
            'Mudar para modo claro' : 
            'Mudar para modo escuro');
    }
    
    // Mobile menu functionality
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('nav-active');
            mobileMenuBtn.innerHTML = navLinks.classList.contains('nav-active') ?
                '<i class="fas fa-times"></i>' :
                '<i class="fas fa-bars"></i>';
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (navLinks.classList.contains('nav-active') && 
                !e.target.closest('.nav-links') && 
                e.target !== mobileMenuBtn) {
                navLinks.classList.remove('nav-active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    }
});

// Validation functions for forms
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    
    // Remove any existing error messages
    const existingErrors = form.querySelectorAll('.error-message');
    existingErrors.forEach(error => error.remove());
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            displayError(input, 'Este campo é obrigatório');
        } else if (input.type === 'email' && !validateEmail(input.value)) {
            isValid = false;
            displayError(input, 'Por favor, insira um e-mail válido');
        } else if (input.id === 'password' && input.value.length < 6) {
            isValid = false;
            displayError(input, 'A senha deve ter pelo menos 6 caracteres');
        }
        
        // Remove error styling when typing
        input.addEventListener('input', () => {
            input.classList.remove('error');
            const errorMsg = input.parentNode.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        });
    });
    
    return isValid;
}

function displayError(input, message) {
    input.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = 'var(--error)';
    errorDiv.style.fontSize = '0.85rem';
    errorDiv.style.marginTop = '0.3rem';
    input.parentNode.appendChild(errorDiv);
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

// Function to submit article
async function submitArticle(event) {
    event.preventDefault();
    
    const form = event.target;
    if (!validateForm(form)) return;
    
    // Get form data
    const title = form.querySelector('#title').value.trim();
    const content = form.querySelector('#content').value.trim();
    const category = form.querySelector('#category').value;
    
    // Create object to send
    const articleData = {
        title,
        content,
        category,
        author: getCurrentUser(), // Function to get current logged in user
        date: new Date().toISOString()
    };
    
    try {
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Enviando...';
        submitBtn.disabled = true;
        
        // In a real app, you'd send this data to your backend
        // For now, we'll simulate sending an email
        await simulateSubmitArticle(articleData);
        
        // Show success message
        const formContainer = form.closest('.form-container');
        formContainer.innerHTML = `
            <div class="text-center">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success);"></i>
                <h2 class="mt-3">Artigo Enviado com Sucesso!</h2>
                <p>Seu artigo foi enviado para revisão. Você receberá uma notificação quando for aprovado.</p>
                <a href="index.html" class="btn btn-primary mt-4">Voltar para a página inicial</a>
            </div>
        `;
        
    } catch (error) {
        // Show error message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-error';
        alertDiv.textContent = 'Ocorreu um erro ao enviar o artigo. Por favor, tente novamente.';
        
        const formTitle = form.querySelector('.form-title');
        form.insertBefore(alertDiv, formTitle.nextSibling);
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        console.error('Error submitting article:', error);
    }
}

// Function to simulate sending an article submission
function simulateSubmitArticle(articleData) {
    return new Promise((resolve) => {
        // Simulate network request
        setTimeout(() => {
            console.log('Article submitted:', articleData);
            
            // In a real implementation, this would send an email to the admin
            // and store the article in a database
            
            resolve({ success: true });
        }, 1500);
    });
}

// Function to get current logged in user (to be implemented with authentication)
function getCurrentUser() {
    // This would get the user from session/local storage after authentication
    // For now, return a placeholder
    return {
        id: 'user123',
        name: 'Usuário Logado',
        email: 'usuario@exemplo.com'
    };
}

// Add specific page initializations here
function initHomePage() {
    // Home page specific functionality
}

function initArticlePage() {
    // Article page specific functionality
}

function initSubmitPage() {
    const submitForm = document.getElementById('submit-article-form');
    if (submitForm) {
        submitForm.addEventListener('submit', submitArticle);
    }
}
