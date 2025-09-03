 <?php
/**
 * SendGrid Email Service para EntreLinhas
 * 
 * Esta é uma implementação simples para envio de e-mails via SendGrid API
 * sem dependências externas, usando apenas cURL que já vem com o PHP.
 */

// Carregar variáveis de ambiente se o carregador existir
if (file_exists(__DIR__ . '/env_loader.php')) {
    require_once __DIR__ . '/env_loader.php';
    carregarVariaveisAmbiente();
}

// Configuração da API SendGrid
define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY') ?: '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'); // API Key do SendGrid
define('EMAIL_FROM', getenv('EMAIL_REMETENTE') ?: 'noreply@entrelinhas.com'); // E-mail de envio
define('EMAIL_FROM_NAME', getenv('EMAIL_NOME') ?: 'EntreLinhas'); // Nome de exibição

/**
 * Envia e-mail usando a API SendGrid
 * 
 * @param string $to Email do destinatário
 * @param string $subject Assunto do e-mail
 * @param string $html_content Conteúdo HTML do e-mail
 * @param string $plain_content Conteúdo em texto puro (opcional)
 * @return bool True se o e-mail foi enviado com sucesso, False caso contrário
 */
function sendEmail($to, $subject, $html_content, $plain_content = '') {
    // Definir arquivo de log
    $log_file = dirname(__DIR__) . '/sendgrid_log.txt';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Iniciando tentativa de envio para {$to}\n", FILE_APPEND);
    
    // Se não houver conteúdo de texto puro, gera a partir do HTML
    if (empty($plain_content)) {
        $plain_content = strip_tags($html_content);
    }
    
    // Verifica se a API Key foi configurada
    if (!defined('SENDGRID_API_KEY') || empty(SENDGRID_API_KEY)) {
        $erro = 'SendGrid API Key não configurada!';
        error_log($erro);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERRO: {$erro}\n", FILE_APPEND);
        return false;
    }
    
    // Prepara os dados para a API SendGrid
    $data = [
        'personalizations' => [
            [
                'to' => [['email' => $to]],
                'subject' => $subject
            ]
        ],
        'from' => [
            'email' => defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@entrelinhas.com',
            'name' => defined('EMAIL_FROM_NAME') ? EMAIL_FROM_NAME : 'EntreLinhas'
        ],
        'content' => [
            [
                'type' => 'text/plain',
                'value' => $plain_content
            ],
            [
                'type' => 'text/html',
                'value' => $html_content
            ]
        ]
    ];
    
    // Converte para JSON para envio
    $json_data = json_encode($data);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $erro = "Erro ao converter dados para JSON: " . json_last_error_msg();
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERRO: {$erro}\n", FILE_APPEND);
        return false;
    }
    
    // Registra a tentativa de envio (para diagnóstico)
    $log_msg = "[SendGrid] Enviando e-mail para: {$to}, Assunto: {$subject}";
    error_log($log_msg);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - {$log_msg}\n", FILE_APPEND);
    
    // Verifica se curl está disponível
    if (!function_exists('curl_init')) {
        $erro = "cURL não está disponível! Não é possível enviar e-mail via SendGrid.";
        error_log($erro);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERRO: {$erro}\n", FILE_APPEND);
        return false;
    }
    
    try {
        // Configura a chamada cURL para a API SendGrid
        $ch = curl_init();
        if ($ch === false) {
            throw new Exception("Falha ao inicializar cURL");
        }
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . SENDGRID_API_KEY,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desativar verificação de certificado para testes
        
        // Executa a chamada e verifica o resultado
        $response = curl_exec($ch);
        
        // Verificar se houve erro
        if ($response === false) {
            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            throw new Exception("cURL Error ({$curl_errno}): {$curl_error}");
        }
        
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $success = ($status_code == 202); // SendGrid retorna 202 Accepted para sucesso
        
        // Registra o resultado
        if ($success) {
            $log_msg = "[SendGrid] E-mail enviado com sucesso para: {$to}";
            error_log($log_msg);
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - {$log_msg}\n", FILE_APPEND);
        } else {
            $log_msg = "[SendGrid] Falha ao enviar e-mail para: {$to}, código: {$status_code}, resposta: {$response}";
            error_log($log_msg);
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - {$log_msg}\n", FILE_APPEND);
        }
        
        return $success;
        
    } catch (Exception $e) {
        $erro = "Exceção ao enviar e-mail: " . $e->getMessage();
        error_log($erro);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERRO: {$erro}\n", FILE_APPEND);
        return false;
    }
}

/**
 * Envia e-mail para os administradores sobre um novo artigo
 * 
 * @param array $artigo Dados do artigo
 * @param string $autor Nome do autor
 * @return bool Se o e-mail foi enviado com sucesso
 */
function notificar_admins_artigo($artigo, $autor) {
    // Lista de e-mails dos administradores
    $admin_emails = [
        'jimmycastilho555@gmail.com',
        // Adicione outros e-mails de administradores aqui
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
    
    // Enviar e-mail para cada administrador
    $sucesso = false;
    
    foreach ($admin_emails as $email) {
        if (sendEmail($email, $assunto, $mensagem)) {
            $sucesso = true; // Se pelo menos um envio for bem-sucedido
        }
    }
    
    return $sucesso;
}

/**
 * Notifica o autor sobre a mudança de status do seu artigo
 *
 * @param array $artigo Dados do artigo
 * @param string $email_autor Email do autor
 * @param string $nome_autor Nome do autor
 * @param string $status_novo Novo status do artigo
 * @return bool Se o e-mail foi enviado com sucesso
 */
function notificar_autor_status($artigo, $email_autor, $nome_autor, $status_novo) {
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
        $mensagem .= "<p>Você pode visualizar seu artigo publicado <a href='http://entrelinhas.infinityfreeapp.com/PAGES/artigo.php?id={$artigo['id']}'>clicando aqui</a>.</p>";
    } elseif ($status_novo == 'rejeitado') {
        $mensagem .= "<p>Infelizmente, seu artigo foi <strong style='color: red;'>rejeitado</strong> pela equipe editorial.</p>";
        $mensagem .= "<p>Você pode revisar e reenviar seu artigo com as melhorias necessárias através da sua <a href='http://entrelinhas.infinityfreeapp.com/PAGES/meus-artigos.php'>área de usuário</a>.</p>";
    } else {
        $mensagem .= "<p>Seu artigo está atualmente <strong>{$status_texto[$status_novo]}</strong> pela nossa equipe editorial.</p>";
    }
    
    $mensagem .= "<p>Agradecemos sua contribuição para a comunidade EntreLinhas!</p>";
    $mensagem .= "</body></html>";
    
    return sendEmail($email_autor, $assunto, $mensagem);
}
?>

