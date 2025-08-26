<?php
/**
 * notificar_status.php
 * 
 * Script simplificado para notificar autores sobre status de artigos
 * com templates predefinidos para cada tipo de status.
 */

// Incluir os arquivos necessários
require_once __DIR__ . '/backend/config.php';

// Verificar parâmetros da linha de comando
if ($argc < 3) {
    echo "Uso: php notificar_status.php [id_artigo] [status] [comentario_opcional]\n";
    echo "\nStatus disponíveis:\n";
    echo "  aprovado    - Notifica que o artigo foi aprovado\n";
    echo "  recusado    - Notifica que o artigo foi recusado\n";
    echo "  revisao     - Notifica que o artigo está em revisão\n";
    echo "  correcoes   - Notifica que o artigo precisa de correções\n";
    echo "  publicado   - Notifica que o artigo foi publicado\n";
    echo "\nExemplos:\n";
    echo "  php notificar_status.php 3 aprovado\n";
    echo "  php notificar_status.php 3 recusado \"O artigo não atende aos critérios editoriais.\"\n";
    exit(1);
}

$artigo_id = intval($argv[1]);
$status = $argv[2];
$comentario = isset($argv[3]) ? $argv[3] : '';

// Templates predefinidos de notificações
$templates = [
    'aprovado' => [
        'status' => 'aprovado',
        'assunto' => 'Boas notícias! Seu artigo foi aprovado',
        'mensagem' => 'Seu artigo foi revisado e aprovado pela equipe editorial. Parabéns! Em breve ele será publicado no site.',
        'comentario_default' => 'Parabéns! Seu artigo foi aprovado e logo será publicado no site.'
    ],
    'recusado' => [
        'status' => 'recusado',
        'assunto' => 'Atualização sobre seu artigo submetido',
        'mensagem' => 'Infelizmente, após análise cuidadosa, seu artigo não foi aprovado para publicação no momento.',
        'comentario_default' => 'Agradecemos sua submissão, mas o artigo não atende aos critérios editoriais atuais.'
    ],
    'revisao' => [
        'status' => 'revisao',
        'assunto' => 'Seu artigo está em revisão',
        'mensagem' => 'Informamos que seu artigo foi recebido e está atualmente sob análise da equipe editorial.',
        'comentario_default' => 'Seu artigo está sendo revisado pela nossa equipe editorial. Retornaremos em breve com o resultado.'
    ],
    'correcoes' => [
        'status' => 'correcoes',
        'assunto' => 'Seu artigo precisa de ajustes',
        'mensagem' => 'Seu artigo foi revisado e identificamos alguns pontos que precisam ser corrigidos antes da publicação.',
        'comentario_default' => 'Por favor, revise os pontos indicados e reenvie o artigo para nova avaliação.'
    ],
    'publicado' => [
        'status' => 'publicado',
        'assunto' => 'Seu artigo foi publicado!',
        'mensagem' => 'Temos o prazer de informar que seu artigo foi publicado e já está disponível em nosso site.',
        'comentario_default' => 'Seu artigo já está disponível para leitura no site EntreLinhas. Agradecemos sua contribuição!'
    ]
];

// Verificar se o status é válido
if (!isset($templates[$status])) {
    echo "Status inválido: {$status}\n";
    echo "Status disponíveis: " . implode(', ', array_keys($templates)) . "\n";
    exit(1);
}

// Se não foi fornecido um comentário, usar o comentário padrão
if (empty($comentario)) {
    $comentario = $templates[$status]['comentario_default'];
}

// Executar o script de notificação
$cmd = "php " . __DIR__ . "/notificar_artigo.php {$artigo_id} {$templates[$status]['status']} " . escapeshellarg($comentario);
echo "Executando: {$cmd}\n";
passthru($cmd);
?>
