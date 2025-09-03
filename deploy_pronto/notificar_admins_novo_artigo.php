<?php
/**
 * Notifica administradores sobre um novo artigo
 * 
 * Este script envia e-mails para todos os administradores quando um novo artigo é submetido
 * 
 * Uso: php notificar_admins_novo_artigo.php <id_artigo>
 */

// Verificar argumentos
if ($argc < 2) {
    echo "Uso: php notificar_admins_novo_artigo.php <id_artigo>\n";
    exit(1);
}

$artigo_id = intval($argv[1]);

// Incluir arquivos necessários
require_once 'backend/config.php';
require_once 'backend/sendgrid_api_helper.php';

// Criar conexão com o banco de dados
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Buscar informações do artigo
$sql = "SELECT a.*, u.nome, u.email
        FROM artigos a
        JOIN usuarios u ON a.id_usuario = u.id
        WHERE a.id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artigo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Artigo com ID $artigo_id não encontrado.\n";
    exit(1);
}

$artigo = $result->fetch_assoc();
echo "Artigo encontrado: " . $artigo['titulo'] . "\n";
echo "Autor: " . $artigo['nome'] . " (" . $artigo['email'] . ")\n";

// Buscar todos os administradores
$sql_admins = "SELECT email FROM usuarios WHERE tipo = 'admin'";
$result_admins = $conn->query($sql_admins);

if ($result_admins->num_rows === 0) {
    echo "Nenhum administrador encontrado no sistema.\n";
    exit(1);
}

$admins_emails = [];
while ($row = $result_admins->fetch_assoc()) {
    $admins_emails[] = $row['email'];
}

echo "Administradores encontrados: " . count($admins_emails) . "\n";

// Preparar conteúdo do e-mail
$assunto = "[EntreLinhas] Novo Artigo Submetido: " . $artigo['titulo'];

$mensagem = "
<h2>Novo Artigo Submetido na Plataforma EntreLinhas</h2>
<p>Um novo artigo foi submetido e aguarda revisão.</p>

<h3>Detalhes do Artigo:</h3>
<ul>
    <li><strong>Título:</strong> {$artigo['titulo']}</li>
    <li><strong>Autor:</strong> {$artigo['nome']} ({$artigo['email']})</li>
    <li><strong>Data de Submissão:</strong> {$artigo['data_criacao']}</li>
</ul>

<h3>Resumo:</h3>
<p>{$artigo['resumo']}</p>

<p>Por favor, acesse o painel administrativo para revisar este artigo.</p>

<p><a href='http://entrelinhas.com/PAGES/admin.php'>Acessar Painel Administrativo</a></p>

<hr>
<p><small>Esta é uma mensagem automática do sistema EntreLinhas. Por favor, não responda a este e-mail.</small></p>
";

// Enviar e-mail para cada administrador
$sucessos = 0;
$falhas = 0;

foreach ($admins_emails as $email) {
    echo "Enviando e-mail para $email...\n";
    
    $enviado = sendEmail(
        $email,
        $assunto,
        $mensagem
    );
    
    if ($enviado) {
        echo "✅ E-mail enviado com sucesso para $email!\n";
        $sucessos++;
        
        // Registrar o envio no log
        $stmt_log = $conn->prepare("INSERT INTO email_log 
                                (artigo_id, destinatario, assunto, status_envio, metodo_envio, data_envio) 
                                VALUES (?, ?, ?, 'enviado', 'sendgrid', NOW())");
        $stmt_log->bind_param("iss", $artigo_id, $email, $assunto);
        $stmt_log->execute();
    } else {
        echo "❌ Falha ao enviar e-mail para $email.\n";
        $falhas++;
        
        // Registrar a falha no log
        $stmt_log = $conn->prepare("INSERT INTO email_log 
                                (artigo_id, destinatario, assunto, status_envio, metodo_envio, data_envio) 
                                VALUES (?, ?, ?, 'falha', 'sendgrid', NOW())");
        $stmt_log->bind_param("iss", $artigo_id, $email, $assunto);
        $stmt_log->execute();
    }
}

// Resumo da operação
echo "\nResumo da operação:\n";
echo "E-mails enviados com sucesso: $sucessos\n";
echo "Falhas no envio: $falhas\n";
echo "Total de administradores notificados: " . count($admins_emails) . "\n";

$conn->close();
echo "Operação concluída.\n";
?>
