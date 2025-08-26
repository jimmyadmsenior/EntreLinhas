<?php
// Script de teste para o envio de e-mails via SendGrid

// Habilitar exibição de erros para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir cabeçalhos para saída
header('Content-Type: text/html; charset=utf-8');

echo '<h1>Teste de Envio de E-mail via SendGrid</h1>';

// Verificar se podemos acessar o arquivo
if (!file_exists('backend/sendgrid_email.php')) {
    echo '<p style="color: red; font-weight: bold;">ERRO: O arquivo backend/sendgrid_email.php não foi encontrado!</p>';
    exit;
}

// Incluir o arquivo com as funções de envio
try {
    require_once 'backend/sendgrid_email.php';
    echo '<p style="color: green;">Arquivo sendgrid_email.php carregado com sucesso.</p>';
} catch (Exception $e) {
    echo '<p style="color: red; font-weight: bold;">ERRO ao carregar o arquivo sendgrid_email.php: ' . $e->getMessage() . '</p>';
    exit;
}

// Verificar se a API Key foi configurada
if (!defined('SENDGRID_API_KEY') || SENDGRID_API_KEY == 'SUA_API_KEY_AQUI') {
    echo '<p style="color: red; font-weight: bold;">ERRO: Você precisa configurar sua API Key do SendGrid no arquivo backend/sendgrid_email.php</p>';
    echo '<p>Abra o arquivo <code>backend/sendgrid_email.php</code> e substitua "SUA_API_KEY_AQUI" pela sua API Key do SendGrid.</p>';
    echo '<p>Para obter uma API Key do SendGrid:</p>';
    echo '<ol>';
    echo '<li>Crie uma conta no <a href="https://signup.sendgrid.com/" target="_blank">SendGrid</a> (há um plano gratuito com 100 e-mails por dia)</li>';
    echo '<li>Acesse o <a href="https://app.sendgrid.com/" target="_blank">Painel do SendGrid</a></li>';
    echo '<li>Vá para Settings > API Keys > Create API Key</li>';
    echo '<li>Dê um nome como "EntreLinhas" e escolha "Full Access" ou "Restricted Access" com permissão para "Mail Send"</li>';
    echo '<li>Copie a API Key gerada e coloque no arquivo sendgrid_email.php</li>';
    echo '</ol>';
    exit;
}

// Verificar se a extensão cURL está disponível
if (!function_exists('curl_init')) {
    echo '<p style="color: red; font-weight: bold;">ERRO: A extensão cURL do PHP não está instalada ou habilitada!</p>';
    echo '<p>O SendGrid API requer a extensão cURL para funcionar.</p>';
    echo '<p>Para instalar no Windows com XAMPP: ative a extensão no php.ini e reinicie o Apache.</p>';
    exit;
}

// Se temos um formulário enviado, tentar enviar o e-mail
if (isset($_POST['enviar'])) {
    $destinatario = isset($_POST['destinatario']) ? $_POST['destinatario'] : '';
    $assunto = isset($_POST['assunto']) ? $_POST['assunto'] : 'Teste de E-mail EntreLinhas';
    $mensagem = isset($_POST['mensagem']) ? $_POST['mensagem'] : '<p>Este é um e-mail de teste enviado pelo sistema EntreLinhas.</p>';
    
    if (empty($destinatario)) {
        echo '<p style="color: red;">Por favor, informe um e-mail de destinatário válido.</p>';
    } else {
        // Tentar enviar o e-mail
        $resultado = sendEmail($destinatario, $assunto, $mensagem);
        
        if ($resultado) {
            echo '<p style="color: green; font-weight: bold;">E-mail enviado com sucesso para ' . htmlspecialchars($destinatario) . '!</p>';
            echo '<p>Verifique sua caixa de entrada (e também a pasta de spam).</p>';
        } else {
            echo '<p style="color: red; font-weight: bold;">Falha ao enviar e-mail. Verifique os logs para mais detalhes.</p>';
            echo '<p>Veja os logs de erro do PHP em sua instalação para mais informações.</p>';
        }
    }
}

// Se temos um formulário para testar notificação de artigo
if (isset($_POST['enviar_notificacao'])) {
    $artigo = [
        'id' => 999,
        'titulo' => $_POST['titulo_artigo'],
        'conteudo' => $_POST['conteudo_artigo']
    ];
    $autor = $_POST['nome_autor'];
    
    // Tentar enviar a notificação
    $resultado = notificar_admins_artigo($artigo, $autor);
    
    if ($resultado) {
        echo '<p style="color: green; font-weight: bold;">Notificação enviada com sucesso para os administradores!</p>';
    } else {
        echo '<p style="color: red; font-weight: bold;">Falha ao enviar notificação. Verifique os logs para mais detalhes.</p>';
    }
}

// Se temos um formulário para testar notificação de status
if (isset($_POST['enviar_status'])) {
    $artigo = [
        'id' => 999,
        'titulo' => $_POST['titulo_artigo_status']
    ];
    $email_autor = $_POST['email_autor'];
    $nome_autor = $_POST['nome_autor_status'];
    $status_novo = $_POST['status_novo'];
    
    // Tentar enviar a notificação de status
    $resultado = notificar_autor_status($artigo, $email_autor, $nome_autor, $status_novo);
    
    if ($resultado) {
        echo '<p style="color: green; font-weight: bold;">Notificação de status enviada com sucesso para o autor!</p>';
    } else {
        echo '<p style="color: red; font-weight: bold;">Falha ao enviar notificação de status. Verifique os logs para mais detalhes.</p>';
    }
}

// Formulário para teste de envio
?>

// Verificar se a API Key foi configurada
if (!defined('SENDGRID_API_KEY') || SENDGRID_API_KEY == 'SUA_API_KEY_AQUI') {
    echo '<p style="color: red; font-weight: bold;">ERRO: Você precisa configurar sua API Key do SendGrid no arquivo backend/sendgrid_email.php</p>';
    echo '<p>Abra o arquivo <code>backend/sendgrid_email.php</code> e substitua "SUA_API_KEY_AQUI" pela sua API Key do SendGrid.</p>';
    exit;
}

// Verificar se a extensão cURL está disponível
if (!function_exists('curl_init')) {
    echo '<p style="color: red; font-weight: bold;">ERRO: A extensão cURL do PHP não está instalada ou habilitada!</p>';
    echo '<p>O SendGrid API requer a extensão cURL para funcionar.</p>';
    exit;
}

// Se temos um formulário enviado, tentar enviar o e-mail
if (isset($_POST['enviar'])) {
    $destinatario = isset($_POST['destinatario']) ? $_POST['destinatario'] : '';
    $assunto = isset($_POST['assunto']) ? $_POST['assunto'] : 'Teste de E-mail EntreLinhas';
    $mensagem = isset($_POST['mensagem']) ? $_POST['mensagem'] : '<p>Este é um e-mail de teste enviado pelo sistema EntreLinhas.</p>';
    
    if (empty($destinatario)) {
        echo '<p style="color: red;">Por favor, informe um e-mail de destinatário válido.</p>';
    } else {
        // Tentar enviar o e-mail
        $resultado = sendEmail($destinatario, $assunto, $mensagem);
        
        if ($resultado) {
            echo '<p style="color: green; font-weight: bold;">E-mail enviado com sucesso para ' . htmlspecialchars($destinatario) . '!</p>';
        } else {
            echo '<p style="color: red; font-weight: bold;">Falha ao enviar e-mail. Verifique os logs para mais detalhes.</p>';
        }
    }
}

// Formulário para teste de envio
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Envio de E-mail - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        h2 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <h2>1. Enviar E-mail de Teste Simples</h2>
    <div class="card">
        <form method="post" action="">
            <div class="form-group">
                <label for="destinatario">Destinatário:</label>
                <input type="email" id="destinatario" name="destinatario" required 
                       value="<?php echo htmlspecialchars(isset($_POST['destinatario']) ? $_POST['destinatario'] : ''); ?>">
                <small>Endereço de e-mail onde você quer receber o teste</small>
            </div>
            <div class="form-group">
                <label for="assunto">Assunto:</label>
                <input type="text" id="assunto" name="assunto" 
                       value="<?php echo htmlspecialchars(isset($_POST['assunto']) ? $_POST['assunto'] : 'Teste de E-mail EntreLinhas'); ?>">
            </div>
            <div class="form-group">
                <label for="mensagem">Mensagem (HTML):</label>
                <textarea id="mensagem" name="mensagem" rows="5"><?php echo htmlspecialchars(isset($_POST['mensagem']) ? $_POST['mensagem'] : '<p>Este é um e-mail de teste enviado pelo sistema EntreLinhas.</p><p>Se você está recebendo este e-mail, o sistema está funcionando corretamente!</p>'); ?></textarea>
            </div>
            <button type="submit" name="enviar">Enviar E-mail de Teste</button>
        </form>
    </div>
    
    <h2>2. Testar Notificação de Novo Artigo</h2>
    <div class="card">
        <form method="post" action="">
            <div class="form-group">
                <label for="titulo_artigo">Título do Artigo:</label>
                <input type="text" id="titulo_artigo" name="titulo_artigo" 
                       value="<?php echo htmlspecialchars(isset($_POST['titulo_artigo']) ? $_POST['titulo_artigo'] : 'Artigo de Teste para Aprovação'); ?>" required>
            </div>
            <div class="form-group">
                <label for="nome_autor">Nome do Autor:</label>
                <input type="text" id="nome_autor" name="nome_autor" 
                       value="<?php echo htmlspecialchars(isset($_POST['nome_autor']) ? $_POST['nome_autor'] : 'Autor de Teste'); ?>" required>
            </div>
            <div class="form-group">
                <label for="conteudo_artigo">Conteúdo do Artigo:</label>
                <textarea id="conteudo_artigo" name="conteudo_artigo" rows="3"><?php echo htmlspecialchars(isset($_POST['conteudo_artigo']) ? $_POST['conteudo_artigo'] : 'Este é um conteúdo de teste para simular a notificação de um novo artigo enviado para aprovação.'); ?></textarea>
            </div>
            <button type="submit" name="enviar_notificacao">Testar Notificação para Admins</button>
        </form>
    </div>
    
    <h2>3. Testar Notificação de Mudança de Status</h2>
    <div class="card">
        <form method="post" action="">
            <div class="form-group">
                <label for="email_autor">E-mail do Autor:</label>
                <input type="email" id="email_autor" name="email_autor" required
                       value="<?php echo htmlspecialchars(isset($_POST['email_autor']) ? $_POST['email_autor'] : ''); ?>">
            </div>
            <div class="form-group">
                <label for="nome_autor_status">Nome do Autor:</label>
                <input type="text" id="nome_autor_status" name="nome_autor_status" 
                       value="<?php echo htmlspecialchars(isset($_POST['nome_autor_status']) ? $_POST['nome_autor_status'] : 'Autor de Teste'); ?>" required>
            </div>
            <div class="form-group">
                <label for="titulo_artigo_status">Título do Artigo:</label>
                <input type="text" id="titulo_artigo_status" name="titulo_artigo_status" 
                       value="<?php echo htmlspecialchars(isset($_POST['titulo_artigo_status']) ? $_POST['titulo_artigo_status'] : 'Artigo com Status Atualizado'); ?>" required>
            </div>
            <div class="form-group">
                <label for="status_novo">Novo Status:</label>
                <select id="status_novo" name="status_novo" required>
                    <option value="pendente" <?php echo (isset($_POST['status_novo']) && $_POST['status_novo'] == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                    <option value="aprovado" <?php echo (isset($_POST['status_novo']) && $_POST['status_novo'] == 'aprovado') ? 'selected' : ''; ?>>Aprovado</option>
                    <option value="rejeitado" <?php echo (isset($_POST['status_novo']) && $_POST['status_novo'] == 'rejeitado') ? 'selected' : ''; ?>>Rejeitado</option>
                </select>
            </div>
            <button type="submit" name="enviar_status">Testar Notificação de Status</button>
        </form>
    </div>
    
    <h2>Próximos Passos</h2>
    <ol>
        <li>Configure sua API Key do SendGrid no arquivo <code>backend/sendgrid_email.php</code></li>
        <li>Teste o envio de e-mail simples para confirmar que está funcionando</li>
        <li>Teste as notificações de novo artigo e mudança de status</li>
        <li>Verifique os logs de erro do PHP se houver falhas no envio</li>
        <li>Integre com o restante do sistema substituindo as chamadas para <code>mail()</code> por <code>sendEmail()</code></li>
    </ol>
    
    <h2>Integrando com o Sistema</h2>
    <p>Para integrar o envio de e-mail via SendGrid com o sistema existente:</p>
    <ol>
        <li>Em <code>backend/email_notification.php</code>, inclua <code>require_once 'sendgrid_email.php';</code></li>
        <li>Substitua as chamadas para <code>mail()</code> por <code>sendEmail()</code></li>
        <li>Para notificação de novos artigos, use a função <code>notificar_admins_artigo()</code></li>
        <li>Para notificação de mudança de status, use a função <code>notificar_autor_status()</code></li>
    </ol>
</body>
</html>
