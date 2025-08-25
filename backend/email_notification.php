<?php
/**
 * Funções para enviar notificações por e-mail
 */

/**
 * Envia e-mail para os administradores sobre um novo artigo
 * 
 * @param array $artigo Dados do artigo
 * @param string $autor Nome do autor
 * @return bool Se o e-mail foi enviado com sucesso
 */
function notificar_admins_novo_artigo($artigo, $autor) {
    // Lista de e-mails dos administradores
    $admin_emails = [
        'jimmycastilho555@gmail.com',
        'bianca.blanco@aluno.senai.br',
        'miguel.zacharias@aluno.senai.br'
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
    
    // Cabeçalhos para envio de e-mail HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: EntreLinhas <noreply@entrelinhas.com>\r\n";
    
    // Enviar e-mail para cada administrador
    $sucesso = true;
    foreach ($admin_emails as $email) {
        if (!mail($email, $assunto, $mensagem, $headers)) {
            // Se falhar, marcamos como falha mas continuamos tentando enviar para os outros
            $sucesso = false;
            error_log("Falha ao enviar e-mail de notificação para {$email}");
        }
    }
    
    return $sucesso;
}

/**
 * Envia e-mail para o autor quando seu artigo for aprovado ou rejeitado
 * 
 * @param string $email_autor E-mail do autor
 * @param string $nome_autor Nome do autor
 * @param array $artigo Dados do artigo
 * @param bool $aprovado Se o artigo foi aprovado ou não
 * @param string $comentario_admin Comentário do administrador (opcional)
 * @return bool Se o e-mail foi enviado com sucesso
 */
function notificar_autor_status_artigo($email_autor, $nome_autor, $artigo, $aprovado, $comentario_admin = '') {
    $status = $aprovado ? "aprovado" : "rejeitado";
    $assunto = "EntreLinhas: Seu artigo foi {$status} - {$artigo['titulo']}";
    
    $mensagem = "<html><body>";
    $mensagem .= "<h2>Atualização sobre seu artigo</h2>";
    $mensagem .= "<p>Olá, {$nome_autor}!</p>";
    
    if ($aprovado) {
        $mensagem .= "<p>Temos o prazer de informar que seu artigo <strong>{$artigo['titulo']}</strong> foi aprovado e já está publicado no site.</p>";
        $mensagem .= "<p>Você pode visualizá-lo em <a href='http://seusite.com/PAGES/artigo.php?id={$artigo['id']}'>http://seusite.com/PAGES/artigo.php?id={$artigo['id']}</a>.</p>";
    } else {
        $mensagem .= "<p>Infelizmente, seu artigo <strong>{$artigo['titulo']}</strong> não foi aprovado para publicação neste momento.</p>";
    }
    
    if (!empty($comentario_admin)) {
        $mensagem .= "<p><strong>Comentário do revisor:</strong> {$comentario_admin}</p>";
    }
    
    $mensagem .= "<p>Agradecemos sua contribuição para o EntreLinhas!</p>";
    $mensagem .= "<p>Atenciosamente,<br>Equipe EntreLinhas</p>";
    $mensagem .= "</body></html>";
    
    // Cabeçalhos para envio de e-mail HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: EntreLinhas <noreply@entrelinhas.com>\r\n";
    
    return mail($email_autor, $assunto, $mensagem, $headers);
}
?>
