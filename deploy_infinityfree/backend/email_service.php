<?php
/**
 * Implementação de e-mail para o sistema EntreLinhas
 * 
 * Este arquivo contém funções para envio de e-mail usando funções nativas do PHP.
 * Para usar PHPMailer no futuro, siga os passos:
 * 
 * 1. Instale o Composer (https://getcomposer.org/)
 * 2. Execute: composer require phpmailer/phpmailer
 * 3. Modifique este arquivo para implementar PHPMailer
 */

// Por enquanto, usaremos somente as funções nativas do PHP
$usePHPMailer = false;

/**
 * Envia um e-mail usando a função mail() nativa do PHP
 * 
 * @param string $para E-mail do destinatário
 * @param string $assunto Assunto do e-mail
 * @param string $mensagem Corpo do e-mail (pode ser HTML)
 * @param string $headers Cabeçalhos adicionais
 * @param bool $isHTML Se a mensagem está em formato HTML
 * @return bool Se o e-mail foi enviado com sucesso
 */
function enviarEmail($para, $assunto, $mensagem, $headers = '', $isHTML = true) {
    // Criar diretório de log se não existir
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0777, true);
    }
    
    // Configurar log
    error_log("[MAIL] Tentando enviar e-mail para: $para, Assunto: $assunto", 3, __DIR__ . '/../logs/email.log');
    
    // Preparar cabeçalhos
    if ($isHTML && strpos($headers, 'Content-type: text/html') === false) {
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    }
    
    // Adicionar cabeçalho From se não estiver presente
    if (strpos($headers, 'From:') === false) {
        $headers .= "From: EntreLinhas <noreply@entrelinhas.com>\r\n";
    }
    
    // Tentar enviar o e-mail
    $result = mail($para, $assunto, $mensagem, $headers);
    
    // Registrar resultado
    if ($result) {
        error_log("[MAIL] E-mail enviado com sucesso para: $para", 3, __DIR__ . '/../logs/email.log');
    } else {
        $error = error_get_last();
        error_log("[MAIL] Erro ao enviar e-mail para: $para. Detalhes: " . ($error ? json_encode($error) : "Desconhecido"), 3, __DIR__ . '/../logs/email.log');
    }
    
    return $result;
}

/**
 * Envia notificação para administradores sobre um novo artigo
 * 
 * @param array $artigo Dados do artigo
 * @param string $autor Nome do autor
 * @return bool Se pelo menos um e-mail foi enviado com sucesso
 */
function notificarAdminsNovoArtigo($artigo, $autor) {
    // Lista de e-mails dos administradores
    $admin_emails = [
        'jimmycastilho555@gmail.com',
        // Adicione aqui outros e-mails de administradores
    ];
    
    $assunto = "EntreLinhas: Novo artigo para aprovação - {$artigo['titulo']}";
    
    $mensagem = "<html><body>";
    $mensagem .= "<h2>Um novo artigo foi enviado para aprovação</h2>";
    $mensagem .= "<p><strong>Título:</strong> {$artigo['titulo']}</p>";
    $mensagem .= "<p><strong>Autor:</strong> {$autor}</p>";
    $mensagem .= "<p><strong>Data de envio:</strong> " . date("d/m/Y H:i:s") . "</p>";
    $mensagem .= "<p><strong>Resumo:</strong> " . substr(strip_tags($artigo['conteudo']), 0, 200) . "...</p>";
    $mensagem .= "<p>Para revisar e aprovar este artigo, acesse o <a href='http://localhost:8000/PAGES/admin_dashboard.php'>Painel de Administração</a>.</p>";
    $mensagem .= "</body></html>";
    
    // Enviar e-mail para cada administrador
    $sucessos = 0;
    
    foreach ($admin_emails as $email) {
        if (enviarEmail($email, $assunto, $mensagem)) {
            $sucessos++;
        }
    }
    
    return $sucessos > 0;
}

/**
 * Notifica o autor sobre a mudança de status de seu artigo
 * 
 * @param array $artigo Dados do artigo
 * @param string $status_anterior Status anterior
 * @param string $status_novo Novo status
 * @param string $email_autor E-mail do autor
 * @param string $nome_autor Nome do autor
 * @return bool Se o e-mail foi enviado com sucesso
 */
function notificarAutorMudancaStatus($artigo, $status_anterior, $status_novo, $email_autor, $nome_autor) {
    $assunto = "EntreLinhas: Atualização sobre seu artigo - {$artigo['titulo']}";
    
    $status_texto = [
        'pendente' => 'em análise',
        'aprovado' => 'aprovado',
        'rejeitado' => 'não aprovado'
    ];
    
    $mensagem = "<html><body>";
    $mensagem .= "<h2>Olá, {$nome_autor}!</h2>";
    $mensagem .= "<p>Temos uma atualização sobre seu artigo <strong>\"{$artigo['titulo']}\"</strong>.</p>";
    
    if ($status_novo == 'aprovado') {
        $mensagem .= "<p>Temos o prazer de informar que seu artigo foi <strong style='color: green;'>aprovado</strong> e já está disponível no EntreLinhas.</p>";
        $mensagem .= "<p>Você pode visualizar seu artigo publicado <a href='http://localhost:8000/PAGES/artigo.php?id={$artigo['id']}'>clicando aqui</a>.</p>";
    } elseif ($status_novo == 'rejeitado') {
        $mensagem .= "<p>Infelizmente, seu artigo foi <strong style='color: red;'>rejeitado</strong> pela equipe editorial.</p>";
        $mensagem .= "<p>Você pode revisar e reenviar seu artigo com as melhorias necessárias através da sua <a href='http://localhost:8000/PAGES/meus-artigos.php'>área de usuário</a>.</p>";
    } else {
        $mensagem .= "<p>Seu artigo está atualmente <strong>{$status_texto[$status_novo]}</strong> pela nossa equipe editorial.</p>";
    }
    
    $mensagem .= "<p>Agradecemos sua contribuição para a comunidade EntreLinhas!</p>";
    $mensagem .= "</body></html>";
    
    return enviarEmail($email_autor, $assunto, $mensagem);
}

// Função para enviar e-mail de boas-vindas
function enviarEmailBoasVindas($nome, $email) {
    $assunto = "Bem-vindo(a) ao EntreLinhas!";
    
    $mensagem = "<html><body>";
    $mensagem .= "<h2>Olá, {$nome}!</h2>";
    $mensagem .= "<p>Seja bem-vindo(a) ao EntreLinhas, seu novo espaço para compartilhar conhecimento e experiências!</p>";
    $mensagem .= "<p>Com sua conta, você pode:</p>";
    $mensagem .= "<ul>";
    $mensagem .= "<li>Enviar artigos para publicação</li>";
    $mensagem .= "<li>Gerenciar seus artigos publicados</li>";
    $mensagem .= "<li>Comentar em artigos de outros autores</li>";
    $mensagem .= "</ul>";
    $mensagem .= "<p>Para começar, acesse <a href='http://localhost:8000/PAGES/enviar-artigo.php'>enviar artigo</a> e compartilhe sua primeira publicação!</p>";
    $mensagem .= "<p>Estamos felizes em ter você como parte da nossa comunidade!</p>";
    $mensagem .= "</body></html>";
    
    return enviarEmail($email, $assunto, $mensagem);
}
?>
