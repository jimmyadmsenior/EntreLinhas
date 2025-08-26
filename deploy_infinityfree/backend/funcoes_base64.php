<?php
/**
 * Converte uma imagem para uma string Base64
 * @param string $caminho_imagem O caminho para o arquivo de imagem
 * @return string A imagem codificada em Base64 com o data URI
 */
function converter_imagem_para_base64($caminho_imagem) {
    // Verifica se o arquivo existe
    if (!file_exists($caminho_imagem)) {
        return false;
    }
    
    // Obtém o tipo MIME da imagem
    $tipo_mime = mime_content_type($caminho_imagem);
    
    // Lê o conteúdo do arquivo
    $dados_imagem = file_get_contents($caminho_imagem);
    
    // Codifica o conteúdo em Base64
    $base64 = base64_encode($dados_imagem);
    
    // Retorna o data URI completo
    return 'data:' . $tipo_mime . ';base64,' . $base64;
}

// Exemplo de uso:
// $imagem_base64 = converter_imagem_para_base64("caminho/para/imagem.jpg");
// echo "<img src='$imagem_base64' alt='Imagem codificada'>";
?>
