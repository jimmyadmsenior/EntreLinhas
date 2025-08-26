<?php
// Script para aplicar correção ao problema de conexão com o banco de dados
// Usado para corrigir o erro "mysqli object is already closed"

// Exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção de Conexão com Banco de Dados - EntreLinhas</title>
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
        <h1>Correção de Conexão com Banco de Dados</h1>
        <div class="alert alert-info">
            <strong>Problema detectado:</strong> Erro "mysqli object is already closed" ao tentar enviar artigos
        </div>
';

// Verificar se o arquivo db_connection_fix.php existe
$fix_file = __DIR__ . '/backend/db_connection_fix.php';
if (!file_exists($fix_file)) {
    echo '<div class="section error">
        <h2>Arquivo de correção não encontrado</h2>
        <p>O arquivo <code>backend/db_connection_fix.php</code> não foi encontrado.</p>
        <p>Por favor, certifique-se de que este arquivo foi criado corretamente.</p>
    </div>';
} else {
    echo '<div class="section success">
        <h2>Arquivo de correção encontrado</h2>
        <p>O arquivo <code>backend/db_connection_fix.php</code> está presente.</p>
    </div>';
}

// Verificar e atualizar o arquivo enviar-artigo.php
$target_file = __DIR__ . '/PAGES/enviar-artigo.php';
if (!file_exists($target_file)) {
    echo '<div class="section error">
        <h2>Arquivo alvo não encontrado</h2>
        <p>O arquivo <code>PAGES/enviar-artigo.php</code> não foi encontrado.</p>
        <p>Por favor, verifique se o arquivo existe.</p>
    </div>';
} else {
    $content = file_get_contents($target_file);
    
    // Verificar se a correção já foi aplicada
    if (strpos($content, "require_once \"../backend/db_connection_fix.php\";") !== false || 
        strpos($content, "require_once '../backend/db_connection_fix.php';") !== false) {
        
        echo '<div class="section warning">
            <h2>Correção já aplicada</h2>
            <p>O arquivo <code>PAGES/enviar-artigo.php</code> já inclui a correção de conexão com o banco de dados.</p>
        </div>';
    } else {
        // Aplicar correção
        $content = str_replace(
            "require_once \"../backend/config.php\";",
            "require_once \"../backend/config.php\";\nrequire_once \"../backend/db_connection_fix.php\"; // Fix para problemas de conexão",
            $content
        );
        
        $content = str_replace(
            "require_once '../backend/config.php';",
            "require_once '../backend/config.php';\nrequire_once '../backend/db_connection_fix.php'; // Fix para problemas de conexão",
            $content
        );
        
        // Salvar alterações
        if (file_put_contents($target_file, $content)) {
            echo '<div class="section success">
                <h2>Correção aplicada com sucesso</h2>
                <p>O arquivo <code>PAGES/enviar-artigo.php</code> foi atualizado com a correção de conexão com o banco de dados.</p>
                <p>Agora você pode tentar enviar artigos novamente.</p>
            </div>';
        } else {
            echo '<div class="section error">
                <h2>Falha ao aplicar correção</h2>
                <p>Não foi possível atualizar o arquivo <code>PAGES/enviar-artigo.php</code>.</p>
                <p>Verifique as permissões de escrita do arquivo.</p>
            </div>';
        }
    }
}

// Verificar se outros arquivos que incluem usuario_helper.php precisam ser corrigidos
$other_files = [
    'PAGES/artigos.php',
    'PAGES/admin_dashboard.php',
    'PAGES/artigo.php'
];

echo '<div class="section info">
    <h2>Verificando outros arquivos</h2>
    <p>Verificando se outros arquivos que usam a função obter_foto_perfil precisam de correção:</p>
    <ul>';

foreach ($other_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $file_content = file_get_contents($full_path);
        if (strpos($file_content, 'obter_foto_perfil') !== false) {
            // Verificar se já contém o fix
            if (strpos($file_content, "require_once \"../backend/db_connection_fix.php\";") !== false || 
                strpos($file_content, "require_once '../backend/db_connection_fix.php';") !== false) {
                echo "<li>{$file}: Já contém a correção</li>";
            } else {
                // Aplicar correção
                $file_content = str_replace(
                    "require_once \"../backend/config.php\";",
                    "require_once \"../backend/config.php\";\nrequire_once \"../backend/db_connection_fix.php\"; // Fix para problemas de conexão",
                    $file_content
                );
                
                $file_content = str_replace(
                    "require_once '../backend/config.php';",
                    "require_once '../backend/config.php';\nrequire_once '../backend/db_connection_fix.php'; // Fix para problemas de conexão",
                    $file_content
                );
                
                // Salvar alterações
                if (file_put_contents($full_path, $file_content)) {
                    echo "<li>{$file}: Correção aplicada com sucesso</li>";
                } else {
                    echo "<li>{$file}: Falha ao aplicar correção</li>";
                }
            }
        } else {
            echo "<li>{$file}: Não usa obter_foto_perfil, não requer correção</li>";
        }
    } else {
        echo "<li>{$file}: Arquivo não encontrado</li>";
    }
}

echo '</ul>
</div>

<div class="mt-4">
    <a href="PAGES/enviar-artigo.php" class="btn btn-primary">Testar Página de Envio</a>
    <a href="solucao_artigos.php" class="btn btn-secondary ms-2">Voltar para Diagnóstico</a>
</div>';

echo '
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
?>
