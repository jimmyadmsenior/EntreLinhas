<?php
// Função para verificar se o arquivo PHP correspondente existe
function verificaExistenciaPHP($caminhoHTML) {
    $caminhoPHP = str_replace('.html', '.php', $caminhoHTML);
    return file_exists($caminhoPHP);
}

// Capturar a URL atual
$url_atual = $_SERVER['REQUEST_URI'];

// Se é uma página HTML
if (strpos($url_atual, '.html') !== false) {
    // Verificar se existe uma versão PHP
    $caminho_local = __DIR__ . $url_atual;
    $caminho_php = str_replace('.html', '.php', $caminho_local);
    
    // Criar o URL de redirecionamento
    $url_redirecionamento = str_replace('.html', '.php', $url_atual);
    
    // Redirecionar para a versão PHP
    header("Location: $url_redirecionamento");
    exit;
}
?>
