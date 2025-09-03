<?php
/**
 * Funções para enviar notificações por e-mail
 */

// Incluir o arquivo com as funções do SendGrid
require_once __DIR__ . '/sendgrid_email.php';

// Incluir solução para problemas de email em ambiente de desenvolvimento
require_once __DIR__ . '/email_fix.php';

/**
 * Envia e-mail para os administradores sobre um novo artigo
 * 
 * @param array $artigo Dados do artigo
 * @param string $autor Nome do autor
 * @return bool Se o e-mail foi enviado com sucesso
 */
function notificar_admins_artigo_original($artigo, $autor) {
    // Lista de e-mails dos administradores
    $admin_emails = [
        'jimmycastilho555@gmail.com',
        // Adicione aqui os emails dos seus amigos administradores
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
    $mensagem .= "<p>Para revisar e aprovar este artigo, acesse o <a href='http://entrelinhas.infinityfreeapp.com/PAGES/admin_dashboard.php'>Painel de Administração</a>.</p>";
    $mensagem .= "</body></html>";
    
    // Cabeçalhos para envio de e-mail HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: EntreLinhas <noreply@entrelinhas.com>\r\n";
    
    // Enviar e-mail para cada administrador
    $sucesso = true;
    $data_atual = date("Y-m-d H:i:s");
    
    // Verificar se temos as funções do SendGrid disponíveis
    if (function_exists('notificar_admins_artigo')) {
        // Usar SendGrid para enviar a notificação
        error_log("[{$data_atual}] Usando SendGrid para enviar e-mails de notificação aos admins");
        
        $resultado = notificar_admins_artigo($artigo, $autor);
        
        if ($resultado) {
            error_log("[{$data_atual}] SUCESSO: E-mails enviados para os administradores via SendGrid");
        } else {
            error_log("[{$data_atual}] ERRO: Falha ao enviar e-mails via SendGrid");
            $sucesso = false;
        }
    } else {
        // Método antigo com mail() como fallback
        error_log("[{$data_atual}] SendGrid não disponível, usando mail() para enviar");
        
        foreach ($admin_emails as $email) {
            error_log("[{$data_atual}] Tentando enviar e-mail de notificação para {$email} sobre artigo: {$artigo['titulo']}");
            
            if (!mail($email, $assunto, $mensagem, $headers)) {
                // Se falhar, marcamos como falha mas continuamos tentando enviar para os outros
                $sucesso = false;
                $error = error_get_last();
                error_log("[{$data_atual}] ERRO: Falha ao enviar e-mail de notificação para {$email}. Erro: " . ($error ? json_encode($error) : "Desconhecido"));
            } else {
                error_log("[{$data_atual}] SUCESSO: E-mail de notificação enviado para {$email}");
            }
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
    
    // Registrar tentativa de envio no log
    $data_atual = date("Y-m-d H:i:s");
    error_log("[{$data_atual}] Tentando enviar e-mail para o autor {$nome_autor} <{$email_autor}> sobre o status do artigo: {$artigo['titulo']}");
    
    // Verificar se temos as funções do SendGrid disponíveis
    if (function_exists('notificar_autor_status')) {
        // Usar SendGrid para enviar a notificação
        $artigo_data = [
            'id' => $artigo['id'],
            'titulo' => $artigo['titulo']
        ];
        $status = $aprovado ? 'aprovado' : 'rejeitado';
        
        $resultado = notificar_autor_status($artigo_data, $email_autor, $nome_autor, $status);
        
        if ($resultado) {
            error_log("[{$data_atual}] SUCESSO: E-mail enviado para o autor {$nome_autor} <{$email_autor}> via SendGrid");
        } else {
            error_log("[{$data_atual}] ERRO: Falha ao enviar e-mail via SendGrid para {$nome_autor} <{$email_autor}>");
        }
    } else {
        // Método antigo com mail() como fallback
        $resultado = mail($email_autor, $assunto, $mensagem, $headers);
        
        if ($resultado) {
            error_log("[{$data_atual}] SUCESSO: E-mail enviado para o autor {$nome_autor} <{$email_autor}> via mail()");
        } else {
            error_log("[{$data_atual}] ERRO: Falha ao enviar e-mail via mail() para {$nome_autor} <{$email_autor}>");
        }
    }
    
    return $resultado;
}
?>

