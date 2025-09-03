<?php
/**
 * Script para notificar o autor quando seu artigo é rejeitado
 * 
 * Este script envia um e-mail para o autor informando que seu artigo foi rejeitado
 * e registra o envio na tabela de logs.
 * 
 * Uso: php notificar_rejeicao.php <id_artigo> <motivo>
 */

// Verificar argumentos
if ($argc < 3) {
    echo "Uso: php notificar_rejeicao.php <id_artigo> <motivo>\n";
    echo "Exemplo: php notificar_rejeicao.php 3 \"O artigo não atende às diretrizes editoriais.\"\n";
    exit(1);
}

$artigo_id = intval($argv[1]);
$motivo = $argv[2];

// Incluir arquivos necessários
require_once 'backend/config.php';
require_once 'backend/sendgrid_api_helper.php';

// Criar conexão
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Buscar informações do artigo e do autor
echo "Buscando informações do artigo #$artigo_id...\n";

$sql = "SELECT a.*, u.nome, u.email 
        FROM artigos a
        JOIN usuarios u ON a.id_usuario = u.id
        WHERE a.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artigo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Artigo não encontrado.\n";
    $conn->close();
    exit(1);
}

$artigo = $result->fetch_assoc();
echo "Artigo: {$artigo['titulo']}\n";
echo "Autor: {$artigo['nome']} ({$artigo['email']})\n";

// Preparar o conteúdo do e-mail
$assunto = "[EntreLinhas] Seu artigo não foi aprovado";

$mensagem = "
<h2>Comunicado sobre seu artigo no EntreLinhas</h2>

<p>Prezado(a) {$artigo['nome']},</p>

<p>Agradecemos por submeter seu artigo <strong>\"{$artigo['titulo']}\"</strong> à nossa plataforma.</p>

<p>Após análise cuidadosa pela nossa equipe editorial, lamentamos informar que seu artigo não foi aprovado para publicação.</p>

<h3>Motivo:</h3>
<p>{$motivo}</p>

<p>Encorajamos você a considerar as orientações fornecidas e, se desejar, submeter uma nova versão do seu trabalho.</p>

<p>Em caso de dúvidas, não hesite em entrar em contato com nossa equipe editorial.</p>

<p>Atenciosamente,<br>
Equipe Editorial - EntreLinhas</p>

<hr>
<p><small>Esta é uma mensagem automática do sistema EntreLinhas. Por favor, não responda a este e-mail.</small></p>
";

// Atualizar o status do artigo para 'rejeitado'
echo "Atualizando status do artigo para 'rejeitado'...\n";
$stmt_update = $conn->prepare("UPDATE artigos SET status = 'rejeitado' WHERE id = ?");
$stmt_update->bind_param("i", $artigo_id);
$stmt_update->execute();

// Enviar o e-mail
echo "Enviando e-mail de notificação para {$artigo['email']}...\n";
$enviado = sendEmail(
    $artigo['email'],
    $assunto,
    $mensagem
);

if ($enviado) {
    echo "✅ E-mail enviado com sucesso!\n";
    
    // Registrar o envio no log
    $stmt_log = $conn->prepare("INSERT INTO email_log 
                            (artigo_id, destinatario, assunto, status_envio, metodo_envio, data_envio) 
                            VALUES (?, ?, ?, 'enviado', 'sendgrid', NOW())");
    $stmt_log->bind_param("iss", $artigo_id, $artigo['email'], $assunto);
    $stmt_log->execute();
    
    echo "Log registrado no banco de dados.\n";
} else {
    echo "❌ Falha ao enviar e-mail.\n";
    
    // Registrar a falha no log
    $stmt_log = $conn->prepare("INSERT INTO email_log 
                            (artigo_id, destinatario, assunto, status_envio, metodo_envio, data_envio) 
                            VALUES (?, ?, ?, 'falha', 'sendgrid', NOW())");
    $stmt_log->bind_param("iss", $artigo_id, $artigo['email'], $assunto);
    $stmt_log->execute();
    
    echo "Falha registrada no banco de dados.\n";
}

$conn->close();
echo "Operação concluída.\n";
?>
