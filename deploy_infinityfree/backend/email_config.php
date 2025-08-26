<?php
/**
 * Configurações para envio de e-mail
 * Este arquivo contém informações para o sistema de notificações por email
 */

// Lista de e-mails dos administradores que receberão notificações
$admin_emails = [
    'jimmycastilho555@gmail.com',
    // Adicione aqui os e-mails dos seus amigos administradores
    // 'admin2@example.com',
    // 'admin3@example.com'
];

// Configurações do servidor de e-mail (para uso com bibliotecas como PHPMailer)
$email_settings = [
    'from_email' => 'noreply@entrelinhas.com',
    'from_name' => 'EntreLinhas - Jornal Digital',
    'reply_to' => 'jimmycastilho555@gmail.com',
    
    // Configurações SMTP - ajuste conforme seu servidor de e-mail
    'smtp_host' => 'smtp.gmail.com', // Exemplo: para Gmail
    'smtp_port' => 587,
    'smtp_secure' => 'tls', // tls ou ssl
    'smtp_auth' => true,
    'smtp_username' => 'seu_email@gmail.com', // Seu e-mail completo
    'smtp_password' => 'sua_senha_ou_app_password', // Senha ou senha de app
    
    // Para usar o Gmail, recomenda-se criar uma "App password" específica
    // https://myaccount.google.com/apppasswords
];

// Assuntos padrão para diferentes tipos de notificações
$email_subjects = [
    'novo_artigo' => 'EntreLinhas: Novo Artigo Submetido para Aprovação',
    'aprovacao' => 'EntreLinhas: Seu Artigo Foi Aprovado',
    'rejeicao' => 'EntreLinhas: Atualização Sobre Seu Artigo Submetido',
    'comentario' => 'EntreLinhas: Novo Comentário no Seu Artigo'
];

// Caminho para templates de e-mail
$email_templates_dir = __DIR__ . '/../templates/emails/';
?>
