<?php
/**
 * teste_email_universal_integration.php
 * 
 * Script de teste para o sistema de integração de e-mail universal
 */

// Incluir arquivos necessários
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/email_universal.php';
require_once __DIR__ . '/backend/email_universal_integration.php';

// Configurar relatório de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Integração do Email Universal</h1>";

// Função para exibir resultados de forma legível
function exibir_resultado($resultado) {
    echo "<pre>";
    print_r($resultado);
    echo "</pre>";
    echo "<hr>";
}

// Teste 1: Envio simples de e-mail
echo "<h2>Teste 1: Envio simples de e-mail</h2>";
$resultado1 = send_email_universal(
    ADMIN_EMAIL,
    "Teste de Email Universal Simples",
    "<h1>Teste de Email</h1><p>Este é um teste do sistema de email universal.</p>"
);
exibir_resultado($resultado1);

// Verificar se temos pelo menos um artigo e um usuário no banco de dados
$artigo_id = null;
$sql = "SELECT a.id FROM artigos a JOIN usuarios u ON a.usuario_id = u.id LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $artigo_id = $row['id'];
    
    // Teste 2: Notificação de status de artigo
    echo "<h2>Teste 2: Notificação de status de artigo</h2>";
    if ($artigo_id) {
        $resultado2 = notificar_status_artigo_universal(
            $artigo_id,
            'aprovado',
            'Parabéns! Seu artigo foi aprovado.'
        );
        exibir_resultado($resultado2);
        
        // Teste 3: Notificação de novo artigo para administradores
        echo "<h2>Teste 3: Notificação de novo artigo para administradores</h2>";
        $resultado3 = notificar_novo_artigo_universal($artigo_id);
        exibir_resultado($resultado3);
    } else {
        echo "<p>Nenhum artigo encontrado para testar as notificações.</p>";
    }
} else {
    echo "<p>Nenhum artigo encontrado para testar as notificações.</p>";
}

// Verificar tabela de log de e-mails
echo "<h2>Verificação da tabela de log de e-mails</h2>";
$verificacao = verificar_tabela_email_log();
echo "<p>Tabela email_log " . ($verificacao ? "existe ou foi criada com sucesso" : "não pôde ser criada") . ".</p>";

// Exibir últimos logs de e-mail
echo "<h2>Últimos logs de e-mail</h2>";
$logs_query = "SELECT * FROM email_log ORDER BY data_envio DESC LIMIT 10";
$logs_result = $conn->query($logs_query);

if ($logs_result && $logs_result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Artigo ID</th><th>Destinatário</th><th>Assunto</th><th>Status</th><th>Método</th><th>Data</th></tr>";
    
    while ($log = $logs_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$log['id']}</td>";
        echo "<td>{$log['artigo_id']}</td>";
        echo "<td>{$log['destinatario']}</td>";
        echo "<td>{$log['assunto']}</td>";
        echo "<td>{$log['status_envio']}</td>";
        echo "<td>{$log['metodo_envio']}</td>";
        echo "<td>{$log['data_envio']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Nenhum log de e-mail encontrado.</p>";
}

// Criar link para voltar
echo "<p><a href='index_email_teste.php'>Voltar para a página de testes de e-mail</a></p>";
?>
