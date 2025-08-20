<?php
// Funções para processamento de imagens de artigos

/**
 * Processa o upload de múltiplas imagens para um artigo
 * @param array $imagens Array de arquivos de imagem
 * @return array Resultado do processamento com status e caminhos
 */
function processarMultiplasImagens($imagens) {
    $resultado = [
        'status' => true,
        'mensagem' => '',
        'caminhos' => []
    ];
    
    // Verificar se existem imagens
    if (empty($imagens['name'][0])) {
        $resultado['status'] = false;
        $resultado['mensagem'] = "Nenhuma imagem fornecida.";
        return $resultado;
    }
    
    // Diretório de upload
    $upload_dir = "../uploads/artigos/";
    
    // Criar diretório se não existir
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $resultado['status'] = false;
            $resultado['mensagem'] = "Erro ao criar diretório de upload.";
            return $resultado;
        }
    }
    
    // Tipos de arquivo permitidos
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    // Tamanho máximo (2MB)
    $max_size = 2 * 1024 * 1024;
    
    // Processar cada imagem
    $total_imagens = count($imagens['name']);
    
    for ($i = 0; $i < $total_imagens; $i++) {
        // Verificar se houve erro no upload
        if ($imagens['error'][$i] !== UPLOAD_ERR_OK) {
            $resultado['status'] = false;
            $resultado['mensagem'] = "Erro no upload da imagem " . ($i + 1) . ".";
            return $resultado;
        }
        
        // Verificar tipo de arquivo
        if (!in_array($imagens['type'][$i], $allowed_types)) {
            $resultado['status'] = false;
            $resultado['mensagem'] = "Tipo de arquivo não permitido para a imagem " . ($i + 1) . ". Use apenas JPG, PNG ou GIF.";
            return $resultado;
        }
        
        // Verificar tamanho
        if ($imagens['size'][$i] > $max_size) {
            $resultado['status'] = false;
            $resultado['mensagem'] = "A imagem " . ($i + 1) . " excede o tamanho máximo permitido (2MB).";
            return $resultado;
        }
        
        // Gerar nome único para o arquivo
        $nome_unico = uniqid() . "_" . time() . "_" . $i;
        
        // Pegar extensão do arquivo
        $ext = pathinfo($imagens['name'][$i], PATHINFO_EXTENSION);
        
        // Caminho completo do arquivo
        $caminho_arquivo = $upload_dir . $nome_unico . "." . $ext;
        
        // Mover o arquivo para o diretório de uploads
        if (!move_uploaded_file($imagens['tmp_name'][$i], $caminho_arquivo)) {
            $resultado['status'] = false;
            $resultado['mensagem'] = "Erro ao mover a imagem " . ($i + 1) . " para o diretório de uploads.";
            return $resultado;
        }
        
        // Adicionar caminho relativo do arquivo ao resultado
        $caminho_relativo = "uploads/artigos/" . $nome_unico . "." . $ext;
        $resultado['caminhos'][] = $caminho_relativo;
    }
    
    return $resultado;
}

/**
 * Associa imagens a um artigo no banco de dados
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @param array $caminhos_imagens Array com os caminhos das imagens
 * @return bool Verdadeiro se bem sucedido, falso caso contrário
 */
function associarImagensAoArtigo($conn, $artigo_id, $caminhos_imagens) {
    // Verificar se a tabela existe
    $sql_check = "SHOW TABLES LIKE 'imagens_artigos'";
    $result = mysqli_query($conn, $sql_check);
    
    if (mysqli_num_rows($result) == 0) {
        // Criar tabela se não existir
        $sql_create = "CREATE TABLE imagens_artigos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            artigo_id INT NOT NULL,
            caminho VARCHAR(255) NOT NULL,
            ordem INT DEFAULT 0,
            descricao TEXT,
            data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (artigo_id) REFERENCES artigos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!mysqli_query($conn, $sql_create)) {
            return false;
        }
    }
    
    // Inserir cada imagem na tabela
    $sucesso = true;
    $ordem = 1;
    
    foreach ($caminhos_imagens as $caminho) {
        $sql = "INSERT INTO imagens_artigos (artigo_id, caminho, ordem) VALUES (?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "isi", $artigo_id, $caminho, $ordem);
            
            if (!mysqli_stmt_execute($stmt)) {
                $sucesso = false;
            }
            
            mysqli_stmt_close($stmt);
            $ordem++;
        } else {
            $sucesso = false;
        }
    }
    
    return $sucesso;
}

/**
 * Obtém todas as imagens de um artigo
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @return array Array com os dados das imagens
 */
function obterImagensArtigo($conn, $artigo_id) {
    $imagens = [];
    
    // Verificar se a tabela existe
    $sql_check = "SHOW TABLES LIKE 'imagens_artigos'";
    $result = mysqli_query($conn, $sql_check);
    
    if (mysqli_num_rows($result) == 0) {
        return $imagens;
    }
    
    $sql = "SELECT id, caminho, ordem, descricao FROM imagens_artigos 
            WHERE artigo_id = ? ORDER BY ordem ASC";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $artigo_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $imagens[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $imagens;
}

/**
 * Atualiza a descrição de uma imagem
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $imagem_id ID da imagem
 * @param string $descricao Nova descrição
 * @return bool Verdadeiro se bem sucedido, falso caso contrário
 */
function atualizarDescricaoImagem($conn, $imagem_id, $descricao) {
    $sql = "UPDATE imagens_artigos SET descricao = ? WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $descricao, $imagem_id);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $resultado;
    }
    
    return false;
}

/**
 * Remove uma imagem do artigo
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $imagem_id ID da imagem
 * @return bool Verdadeiro se bem sucedido, falso caso contrário
 */
function removerImagemArtigo($conn, $imagem_id) {
    // Primeiro, obter o caminho da imagem
    $sql = "SELECT caminho FROM imagens_artigos WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $imagem_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $caminho);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        // Excluir o arquivo físico
        if ($caminho && file_exists("../" . $caminho)) {
            unlink("../" . $caminho);
        }
        
        // Remover do banco de dados
        $sql = "DELETE FROM imagens_artigos WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $imagem_id);
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $resultado;
        }
    }
    
    return false;
}
?>
