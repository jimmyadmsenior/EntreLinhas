<?php
// Este arquivo ajudará a identificar problemas com o acesso aos arquivos no projeto

// Função para verificar se um arquivo existe e pode ser lido
function verificar_arquivo($caminho, $nome) {
    echo "<div style='margin-bottom: 10px;'>";
    echo "<strong>Arquivo:</strong> $nome<br>";
    echo "<strong>Caminho:</strong> $caminho<br>";
    
    if (file_exists($caminho)) {
        echo "<span style='color:green'>✅ Arquivo existe</span><br>";
        
        if (is_readable($caminho)) {
            echo "<span style='color:green'>✅ Arquivo pode ser lido</span>";
        } else {
            echo "<span style='color:red'>❌ Arquivo não pode ser lido</span>";
        }
    } else {
        echo "<span style='color:red'>❌ Arquivo não existe</span>";
    }
    echo "</div><hr>";
}

// Definir constantes de caminho base para testar diferentes abordagens
define('BASE_ABS_PATH', dirname(dirname(__FILE__)) . '/');
define('BASE_REL_PATH', '../');
define('DOC_ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/EntreLinhas/');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Caminhos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #333; }
        hr { margin: 20px 0; border: 0; border-top: 1px solid #eee; }
        .info { background: #f9f9f9; padding: 15px; margin-bottom: 20px; border-left: 4px solid #5bc0de; }
    </style>
</head>
<body>
    <h1>Diagnóstico de Caminhos do Projeto EntreLinhas</h1>
    
    <div class="info">
        <h2>Informações do Servidor</h2>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        <p><strong>Script Filename:</strong> <?php echo $_SERVER['SCRIPT_FILENAME']; ?></p>
        <p><strong>PHP_SELF:</strong> <?php echo $_SERVER['PHP_SELF']; ?></p>
        <p><strong>REQUEST_URI:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
        <p><strong>SERVER_NAME:</strong> <?php echo $_SERVER['SERVER_NAME']; ?></p>
        <p><strong>HTTP_HOST:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
    </div>
    
    <h2>Verificação de Caminhos Base</h2>
    <p><strong>BASE_ABS_PATH:</strong> <?php echo BASE_ABS_PATH; ?></p>
    <p><strong>BASE_REL_PATH:</strong> <?php echo BASE_REL_PATH; ?></p>
    <p><strong>DOC_ROOT_PATH:</strong> <?php echo DOC_ROOT_PATH; ?></p>
    
    <h2>Verificação de Arquivos Críticos</h2>
    
    <h3>Usando caminho absoluto baseado no __FILE__</h3>
    <?php
    verificar_arquivo(BASE_ABS_PATH . 'backend/config.php', 'config.php');
    verificar_arquivo(BASE_ABS_PATH . 'backend/db_connection_fix.php', 'db_connection_fix.php');
    verificar_arquivo(BASE_ABS_PATH . 'PAGES/includes/cabecalho_helper.php', 'cabecalho_helper.php');
    ?>
    
    <h3>Usando caminho relativo (../)</h3>
    <?php
    verificar_arquivo(BASE_REL_PATH . 'backend/config.php', 'config.php');
    verificar_arquivo(BASE_REL_PATH . 'backend/db_connection_fix.php', 'db_connection_fix.php');
    verificar_arquivo('includes/cabecalho_helper.php', 'cabecalho_helper.php');
    ?>
    
    <h3>Usando DOCUMENT_ROOT</h3>
    <?php
    verificar_arquivo(DOC_ROOT_PATH . 'backend/config.php', 'config.php');
    verificar_arquivo(DOC_ROOT_PATH . 'backend/db_connection_fix.php', 'db_connection_fix.php');
    verificar_arquivo(DOC_ROOT_PATH . 'PAGES/includes/cabecalho_helper.php', 'cabecalho_helper.php');
    ?>
    
    <h2>Solução Recomendada</h2>
    <p>Com base nos resultados acima, você deve usar o tipo de caminho que mostrou "Arquivo existe" e "Arquivo pode ser lido" para todos os arquivos.</p>
</body>
</html>
