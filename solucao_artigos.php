<?php
// Solução para problema de envio de artigos
// Este script verifica e corrige os problemas comuns no processo de envio de artigos

// Exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Estilos para melhor visualização
echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solução EntreLinhas - Envio de Artigos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        .section { margin-bottom: 30px; padding: 20px; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 3px; }
        .action-btn { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Solução para Problemas de Envio de Artigos</h1>
';

echo '<div class="section info">
    <h2>1. Diagnóstico Inicial</h2>';

// Verificar sessão
session_start();
echo "<p>Verificando status da sessão...</p>";
if (isset($_SESSION['id'])) {
    echo '<div class="alert alert-success">Usuário logado: ID ' . $_SESSION['id'] . ', Nome: ' . $_SESSION['nome'] . '</div>';
} else {
    echo '<div class="alert alert-warning">Usuário não está logado. Alguns testes não poderão ser executados.</div>';
}

// Verificar conexão com o banco de dados
echo "<p>Verificando conexão com o banco de dados...</p>";
require_once 'backend/config.php';

if (isset($conn) && $conn instanceof mysqli) {
    echo '<div class="alert alert-success">Conexão com o banco de dados estabelecida com sucesso.</div>';
    
    // Verificar tabela de artigos
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'artigos'");
    if (mysqli_num_rows($result) > 0) {
        echo '<div class="alert alert-success">Tabela de artigos encontrada.</div>';
    } else {
        echo '<div class="alert alert-danger">Tabela de artigos não encontrada!</div>';
    }
    
    // Verificar tabela de imagens_artigos
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'imagens_artigos'");
    if (mysqli_num_rows($result) > 0) {
        echo '<div class="alert alert-success">Tabela de imagens de artigos encontrada.</div>';
    } else {
        echo '<div class="alert alert-danger">Tabela de imagens de artigos não encontrada!</div>';
    }
} else {
    echo '<div class="alert alert-danger">Falha na conexão com o banco de dados!</div>';
}

echo '</div>'; // Fim da seção de diagnóstico

// Verificar extensões PHP necessárias
echo '<div class="section info">
    <h2>2. Verificação de Extensões PHP</h2>
    <p>Verificando extensões necessárias...</p>';

$required_extensions = ['mysqli', 'curl', 'fileinfo', 'gd'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo '<div class="alert alert-success">Extensão ' . $ext . ' está carregada.</div>';
    } else {
        echo '<div class="alert alert-danger">Extensão ' . $ext . ' NÃO está carregada!</div>';
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo '<div class="alert alert-warning">
        <strong>Atenção:</strong> Algumas extensões necessárias estão faltando. 
        Adicione as seguintes linhas ao seu php.ini:<br>
        <pre>';
    foreach ($missing_extensions as $ext) {
        echo 'extension=' . $ext . "\n";
    }
    echo '</pre>
    </div>';
}

echo '</div>'; // Fim da seção de verificação de extensões

// Verificar diretórios de upload
echo '<div class="section info">
    <h2>3. Verificação de Diretórios</h2>';

$required_dirs = [
    '../uploads/',
    '../uploads/artigos/',
    '../logs/'
];

foreach ($required_dirs as $dir) {
    $full_path = realpath(dirname(__FILE__) . '/' . $dir);
    echo "<p>Verificando diretório: {$dir}</p>";
    
    if (file_exists($full_path)) {
        echo '<div class="alert alert-success">Diretório existe: ' . $full_path . '</div>';
        
        // Verificar permissões
        if (is_writable($full_path)) {
            echo '<div class="alert alert-success">Permissões de escrita OK.</div>';
        } else {
            echo '<div class="alert alert-danger">Sem permissão de escrita! Corrija com: chmod 755 ' . $full_path . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Diretório não existe: ' . $dir . '</div>';
        echo '<button class="btn btn-warning action-btn criar-diretorio" data-dir="' . $dir . '">Criar Diretório</button>';
    }
}

echo '</div>'; // Fim da seção de verificação de diretórios

// Verificar arquivos necessários
echo '<div class="section info">
    <h2>4. Verificação de Arquivos Essenciais</h2>';

$required_files = [
    '../backend/processar_artigo.php',
    '../backend/artigos.php',
    '../backend/imagens_artigos.php',
    '../backend/email_notification.php',
    '../backend/sendgrid_email.php',
    '../PAGES/enviar-artigo.php'
];

foreach ($required_files as $file) {
    $full_path = realpath(dirname(__FILE__) . '/' . $file);
    
    echo "<p>Verificando arquivo: {$file}</p>";
    
    if (file_exists($full_path)) {
        echo '<div class="alert alert-success">Arquivo existe: ' . basename($file) . '</div>';
        
        // Verificar conteúdo básico do arquivo para enviar-artigo.php
        if (basename($file) == 'enviar-artigo.php') {
            $content = file_get_contents($full_path);
            if (strlen($content) < 10) {
                echo '<div class="alert alert-danger">Arquivo enviar-artigo.php está vazio ou corrompido!</div>';
                
                // Verificar se existe um backup
                $backup_file = str_replace('.php', '.php.bak', $full_path);
                if (file_exists($backup_file)) {
                    echo '<div class="alert alert-warning">Backup encontrado: ' . basename($backup_file) . '</div>';
                    echo '<button class="btn btn-warning action-btn restaurar-backup" data-file="' . basename($file) . '">Restaurar do Backup</button>';
                }
                
                // Verificar template
                $template_file = realpath(dirname(__FILE__) . '/../PAGES/enviar-artigo-temp.php');
                if (file_exists($template_file)) {
                    echo '<div class="alert alert-info">Template encontrado: enviar-artigo-temp.php</div>';
                    echo '<button class="btn btn-info action-btn usar-template" data-file="' . basename($file) . '">Usar Template</button>';
                }
            }
        }
    } else {
        echo '<div class="alert alert-danger">Arquivo não existe: ' . basename($file) . '</div>';
    }
}

echo '</div>'; // Fim da seção de verificação de arquivos

// Solução para problemas de email
echo '<div class="section info">
    <h2>5. Correção de Problemas de E-mail</h2>';

// Verificar configuração de email
$email_log_file = realpath(dirname(__FILE__) . '/../logs/email_debug.log');
if (file_exists($email_log_file)) {
    $email_log = file_get_contents($email_log_file);
    
    if (strpos($email_log, 'Failed to connect to mailserver') !== false) {
        echo '<div class="alert alert-danger">Problema detectado: Erro de conexão com servidor de email.</div>';
        echo '<div class="alert alert-info">
            <p><strong>Solução:</strong> O sistema está tentando usar a função mail() do PHP, mas não há servidor SMTP configurado.</p>
            <p>Existem duas opções para resolver:</p>
            <ol>
                <li>Configurar um servidor SMTP local (como Sendmail)</li>
                <li>Modificar o sistema para ignorar envios de email em ambiente de desenvolvimento</li>
            </ol>
        </div>';
        
        echo '<button class="btn btn-primary action-btn criar-solucao-email">Criar Solução para E-mail</button>';
    }
}

// Criar função para corrigir problema de email
echo '<div id="solucao-email" style="display:none;">
    <h3>Solução para o Problema de E-mail</h3>
    <p>Crie um arquivo chamado <code>email_fix.php</code> na pasta <code>backend</code> com o seguinte conteúdo:</p>
    <pre>
&lt;?php
/**
 * Solução temporária para problemas de e-mail
 */

// Verificar se estamos em ambiente de desenvolvimento
function is_development_env() {
    $server_name = $_SERVER[\'SERVER_NAME\'] ?? \'\';
    return $server_name == \'localhost\' || 
           strpos($server_name, \'127.0.0.1\') !== false || 
           strpos($server_name, \'192.168.\') === 0;
}

// Sobrescrever função de notificação para ambiente de desenvolvimento
function notificar_admins_novo_artigo($artigo, $autor) {
    if (is_development_env()) {
        // Em ambiente de desenvolvimento, apenas simular o envio
        error_log(\'[EMAIL SIMULADO] Notificação sobre novo artigo: \' . $artigo[\'titulo\'] . \' por \' . $autor);
        return true;
    }
    
    // Em produção, usar a função original
    return notificar_admins_artigo_original($artigo, $autor);
}

// Renomear a função original (você precisa fazer isso manualmente)
// 1. Abra o arquivo backend/email_notification.php
// 2. Renomeie a função notificar_admins_novo_artigo para notificar_admins_artigo_original
// 3. Inclua este arquivo no início do email_notification.php
</pre>
    <p>Depois, modifique o arquivo <code>backend/email_notification.php</code> da seguinte forma:</p>
    <ol>
        <li>Renomeie a função existente <code>notificar_admins_novo_artigo</code> para <code>notificar_admins_artigo_original</code></li>
        <li>Adicione no início do arquivo (após os comentários): <code>require_once __DIR__ . \'/email_fix.php\';</code></li>
    </ol>
    <p>Isso fará com que o sistema simule o envio de e-mails em ambiente de desenvolvimento, evitando os erros.</p>
</div>';

echo '</div>'; // Fim da seção de email

// Formulário de teste simplificado
echo '<div class="section info">
    <h2>6. Teste Simplificado de Envio de Artigo</h2>';

if (isset($_SESSION['id'])) {
    echo '<p>Você pode usar este formulário simplificado para testar o envio de artigos sem imagens:</p>
    <form action="../backend/debug_processar_artigo.php" method="post" class="border p-3">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título do Artigo</label>
            <input type="text" class="form-control" id="titulo" name="titulo" value="Artigo de Teste" required>
        </div>
        
        <div class="mb-3">
            <label for="categoria" class="form-label">Categoria</label>
            <select class="form-select" id="categoria" name="categoria" required>
                <option value="noticia" selected>Notícia</option>
                <option value="opiniao">Opinião</option>
                <option value="entrevista">Entrevista</option>
                <option value="cultural">Cultural</option>
                <option value="educacao">Educação</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="conteudo" class="form-label">Conteúdo do Artigo</label>
            <textarea class="form-control" id="conteudo" name="conteudo" rows="5" required>Este é um artigo de teste para diagnóstico do sistema.</textarea>
        </div>
        
        <input type="hidden" name="acao" value="enviar">
        <button type="submit" class="btn btn-primary">Testar Envio</button>
    </form>';
} else {
    echo '<div class="alert alert-warning">Você precisa estar logado para testar o envio de artigos.</div>';
}

echo '</div>'; // Fim da seção de teste

// Link para página de diagnóstico completo
echo '<div class="mt-4">
    <a href="backend/debug_processar_artigo.php" class="btn btn-primary">Ver Script de Diagnóstico Completo</a>
    <a href="PAGES/teste_envio_artigo.php" class="btn btn-success">Ir para Formulário de Diagnóstico</a>
</div>';

// Scripts JavaScript
echo '
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Botão para criar solução de email
    $(".criar-solucao-email").click(function() {
        $("#solucao-email").show();
    });
    
    // Botão para criar diretório
    $(".criar-diretorio").click(function() {
        const dir = $(this).data("dir");
        alert("Função para criar o diretório: " + dir + "\nEsta funcionalidade precisa ser implementada pelo administrador.");
    });
    
    // Botão para restaurar backup
    $(".restaurar-backup").click(function() {
        const file = $(this).data("file");
        alert("Função para restaurar o arquivo " + file + " a partir do backup.\nEsta funcionalidade precisa ser implementada pelo administrador.");
    });
    
    // Botão para usar template
    $(".usar-template").click(function() {
        const file = $(this).data("file");
        alert("Função para restaurar o arquivo " + file + " a partir do template.\nEsta funcionalidade precisa ser implementada pelo administrador.");
    });
});
</script>
';

echo '
    </div>
</body>
</html>';
?>
