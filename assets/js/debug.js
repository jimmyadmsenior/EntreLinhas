// Arquivo de depuração para testar o cadastro.html
console.log('Testando console do navegador');

// Verificar tema atual
const currentTheme = localStorage.getItem('theme');
console.log('Tema atual:', currentTheme);

// Verificar se os elementos importantes existem
document.addEventListener('DOMContentLoaded', () => {
  console.log('Formulário de registro:', document.getElementById('register-form'));
  console.log('Botão de tema:', document.getElementById('theme-toggle'));
  console.log('Container de alerta:', document.getElementById('alert-container'));
});
