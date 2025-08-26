<?php
/**
 * notificar_artigo.php
 * 
 * Script de linha de comando para enviar notificações sobre artigos
 * usando a API do SendGrid
 */

// Incluir os arquivos necessários
require_once __DIR__ . '/sendgrid_api_helper.php';
require_once __DIR__ . '/backend/config.php';

// Verificar parâmetros da linha de comando
if ($argc < 3) {
    echo "Uso: php notificar_artigo.php [id_artigo] [status] [comentario]\n";
    echo "  id_artigo: ID do artigo no banco de dados\n";
    echo "  status: pendente, revisao, aprovado, publicado, recusado, correcoes\n";
    echo "  comentario: (Opcional) Comentário sobre a mudança de status\n";
    exit(1);
}

$artigo_id = intval($argv[1]);
$status = $argv[2];
$comentario = isset($argv[3]) ? $argv[3] : '';

// Validar status
$status_validos = ['pendente', 'revisao', 'aprovado', 'publicado', 'recusado', 'correcoes'];
if (!in_array($status, $status_validos)) {
    echo "Status inválido: {$status}\n";
    echo "Status válidos: " . implode(', ', $status_validos) . "\n";
    exit(1);
}

// Mapear status para descrições mais amigáveis
$status_descricao = [
    'pendente' => 'Pendente de Revisão',
    'revisao' => 'Em Revisão',
    'aprovado' => 'Aprovado',
    'publicado' => 'Publicado',
    'recusado' => 'Não Aprovado',
    'correcoes' => 'Aguardando Correções'
];
$status_texto = $status_descricao[$status];

echo "Notificando sobre artigo #{$artigo_id} - Status: {$status_texto}\n";
if (!empty($comentario)) {
    echo "Comentário: {$comentario}\n";
}

// Verificar conexão com o banco de dados
if (!isset($conn) || $conn->connect_error) {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
}

if ($conn->connect_error) {
    echo "ERRO: Falha na conexão com o banco de dados: {$conn->connect_error}\n";
    exit(1);
}

// Buscar dados do artigo
$stmt = $conn->prepare("SELECT a.titulo, u.email, u.nome FROM artigos a 
                        JOIN usuarios u ON a.id_usuario = u.id 
                        WHERE a.id = ?");
if (!$stmt) {
    echo "ERRO: Falha na preparação da consulta: {$conn->error}\n";
    exit(1);
}

$stmt->bind_param("i", $artigo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "ERRO: Artigo não encontrado: {$artigo_id}\n";
    exit(1);
}

$artigo = $result->fetch_assoc();
$stmt->close();

echo "Artigo: {$artigo['titulo']}\n";
echo "Autor: {$artigo['nome']} ({$artigo['email']})\n";

// Criar o assunto do e-mail
$subject = "Atualização de Status do Artigo: {$artigo['titulo']}";

// Criar o corpo do e-mail em HTML
$message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Atualização de Status</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { font-size: 12px; text-align: center; margin-top: 30px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EntreLinhas</h1>
        </div>
        <div class="content">
            <h2>Atualização de Status do Artigo</h2>
            <p>Olá ' . htmlspecialchars($artigo['nome']) . ',</p>
            <p>O status do seu artigo <strong>"' . htmlspecialchars($artigo['titulo']) . '"</strong> foi atualizado para: </p>
            <p style="font-size: 18px; font-weight: bold; color: #3498db; padding: 10px; background: #e8f4fc; text-align: center; border-radius: 5px;">' 
                . htmlspecialchars($status_texto) . '</p>';

// Adicionar comentário, se existir
if (!empty($comentario)) {
    $message .= '
            <h3>Comentário:</h3>
            <div style="background: #fff; padding: 15px; border-left: 4px solid #3498db; margin: 10px 0;">
                ' . nl2br(htmlspecialchars($comentario)) . '
            </div>';
}

$message .= '
            <p>Você pode acessar seu artigo em nossa plataforma para verificar mais detalhes.</p>
            <p>Atenciosamente,<br>Equipe EntreLinhas</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' EntreLinhas - Todos os direitos reservados</p>
        </div>
    </div>
</body>
</html>';

// Enviar o e-mail
echo "Enviando e-mail para {$artigo['email']}...\n";
$result = sendgrid_send_email($artigo['email'], $subject, $message);

// Verificar resultado
if ($result['success']) {
    echo "✅ E-mail enviado com sucesso!\n";
    
    // Registrar o envio no log
    $log_query = "INSERT INTO email_log (artigo_id, destinatario, assunto, status_envio, metodo_envio, data_envio) 
                 VALUES (?, ?, ?, 'enviado', 'sendgrid', NOW())";
                 
    $stmt = $conn->prepare($log_query);
    if ($stmt) {
        $stmt->bind_param("iss", $artigo_id, $artigo['email'], $subject);
        $stmt->execute();
        $stmt->close();
        echo "Log registrado no banco de dados.\n";
    }
} else {
    echo "❌ Falha ao enviar e-mail: {$result['message']}\n";
    
    // Registrar a falha no log
    $log_query = "INSERT INTO email_log (artigo_id, destinatario, assunto, status_envio, metodo_envio, data_envio) 
                 VALUES (?, ?, ?, 'falha', 'sendgrid', NOW())";
                 
    $stmt = $conn->prepare($log_query);
    if ($stmt) {
        $stmt->bind_param("iss", $artigo_id, $artigo['email'], $subject);
        $stmt->execute();
        $stmt->close();
        echo "Falha registrada no banco de dados.\n";
    }
}

$conn->close();
echo "Operação concluída.\n";
?>
