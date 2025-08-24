<?php
// Este script converte todos os arquivos HTML em redirecionamentos para suas versões PHP
// Executar a partir da linha de comando: php converter_html_para_redirecionamentos.php

$diretorio_pages = __DIR__ . '/PAGES';
$arquivos = glob($diretorio_pages . '/*.html');

$template_redirecionamento = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0; url=NOME_ARQUIVO.php">
    <title>Redirecionando...</title>
    <script>
        window.location.href = "NOME_ARQUIVO.php" + window.location.search;
    </script>
</head>
<body>
    <p>Redirecionando para <a href="NOME_ARQUIVO.php">NOME_ARQUIVO.php</a>...</p>
</body>
</html>
HTML;

$contador = 0;
foreach ($arquivos as $arquivo) {
    $nome_base = basename($arquivo, '.html');
    
    // Verificar se existe uma versão PHP
    $arquivo_php = $diretorio_pages . '/' . $nome_base . '.php';
    if (!file_exists($arquivo_php)) {
        echo "Arquivo PHP não existe: $arquivo_php - Ignorando...\n";
        continue;
    }
    
    $conteudo = str_replace('NOME_ARQUIVO', $nome_base, $template_redirecionamento);
    
    // Fazer backup do arquivo original
    $arquivo_backup = $arquivo . '.bak';
    copy($arquivo, $arquivo_backup);
    
    // Salvar o novo conteúdo
    file_put_contents($arquivo, $conteudo);
    
    echo "Convertido: $arquivo\n";
    $contador++;
}

echo "Concluído! $contador arquivos convertidos.\n";
?>
