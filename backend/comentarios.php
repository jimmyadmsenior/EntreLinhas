<?php
// Funções para gerenciamento de comentários

/**
 * Adiciona um novo comentário
 * @param mysqli $conn Conexão com o banco de dados
 * @param array $comentario Dados do comentário (usuario_id, artigo_id, conteudo)
 * @return array Resultado da operação com status e mensagem
 */
function adicionarComentario($conn, $comentario) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'comentario_id' => 0
    ];
    
    // Validar campos obrigatórios
    if (empty($comentario['usuario_id']) || empty($comentario['artigo_id']) || empty($comentario['conteudo'])) {
        $resultado['mensagem'] = "Todos os campos são obrigatórios.";
        return $resultado;
    }
    
    // Verificar se o artigo existe
    $sql = "SELECT id, titulo FROM artigos WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $comentario['artigo_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) == 0) {
            $resultado['mensagem'] = "Artigo não encontrado.";
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Verificar se o usuário existe
    $sql = "SELECT id, nome, email FROM usuarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $comentario['usuario_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!($usuario = mysqli_fetch_assoc($result))) {
            $resultado['mensagem'] = "Usuário não encontrado.";
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Inserir o comentário
    $sql = "INSERT INTO comentarios (usuario_id, artigo_id, conteudo, data_comentario) VALUES (?, ?, ?, NOW())";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iis", $comentario['usuario_id'], $comentario['artigo_id'], $comentario['conteudo']);
        
        if (mysqli_stmt_execute($stmt)) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Comentário adicionado com sucesso!";
            $resultado['comentario_id'] = mysqli_insert_id($conn);
            
            // Notificar autor do artigo sobre o novo comentário
            notificarNovoComentario($conn, $resultado['comentario_id'], $usuario);
        } else {
            $resultado['mensagem'] = "Erro ao adicionar comentário. Por favor, tente novamente.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Notifica o autor do artigo sobre um novo comentário
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $comentario_id ID do comentário
 * @param array $autor_comentario Dados do autor do comentário
 */
function notificarNovoComentario($conn, $comentario_id, $autor_comentario) {
    // Buscar informações do comentário e do artigo
    $sql = "SELECT c.id, c.conteudo, c.artigo_id, a.titulo, a.usuario_id 
            FROM comentarios c 
            JOIN artigos a ON c.artigo_id = a.id 
            WHERE c.id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $comentario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Buscar informações do autor do artigo
            $sql_autor = "SELECT nome, email FROM usuarios WHERE id = ?";
            
            if ($stmt_autor = mysqli_prepare($conn, $sql_autor)) {
                mysqli_stmt_bind_param($stmt_autor, "i", $row['usuario_id']);
                mysqli_stmt_execute($stmt_autor);
                $result_autor = mysqli_stmt_get_result($stmt_autor);
                
                if ($autor_artigo = mysqli_fetch_assoc($result_autor)) {
                    // Enviar e-mail de notificação ao autor do artigo
                    $assunto = "Novo comentário no seu artigo - EntreLinhas";
                    
                    // Resumo do comentário (limitado a 100 caracteres)
                    $resumo_comentario = mb_substr($row['conteudo'], 0, 100);
                    if (strlen($row['conteudo']) > 100) {
                        $resumo_comentario .= "...";
                    }
                    
                    // Corpo do e-mail em HTML
                    $mensagem = "
                    <html>
                    <head>
                        <title>Novo comentário no seu artigo</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #000; color: #fff; padding: 15px; text-align: center; }
                            .content { padding: 20px; border: 1px solid #ddd; }
                            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                            .btn { display: inline-block; padding: 10px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px; }
                            .comentario { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #333; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>EntreLinhas</h1>
                            </div>
                            <div class='content'>
                                <h2>Novo comentário no seu artigo!</h2>
                                <p>Olá, {$autor_artigo['nome']}!</p>
                                <p><strong>{$autor_comentario['nome']}</strong> acabou de comentar no seu artigo <strong>{$row['titulo']}</strong>.</p>
                                
                                <div class='comentario'>
                                    <p>\"$resumo_comentario\"</p>
                                </div>
                                
                                <p><a href='http://seusite.com.br/PAGES/artigo.php?id={$row['artigo_id']}' class='btn'>Ver Comentário</a></p>
                            </div>
                            <div class='footer'>
                                <p>Este é um e-mail automático. Por favor, não responda.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    // Cabeçalhos para envio de e-mail em HTML
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                    $headers .= "From: EntreLinhas <noreply@entrelinhas.com.br>\r\n";
                    
                    // Enviar e-mail
                    mail($autor_artigo['email'], $assunto, $mensagem, $headers);
                }
                
                mysqli_stmt_close($stmt_autor);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
}

/**
 * Listar comentários de um artigo
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @return array Lista de comentários
 */
function listarComentarios($conn, $artigo_id) {
    $comentarios = [];
    
    $sql = "SELECT c.id, c.conteudo, c.data_comentario, u.id AS usuario_id, u.nome AS nome_usuario 
            FROM comentarios c 
            JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.artigo_id = ? 
            ORDER BY c.data_comentario DESC";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $artigo_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $comentarios[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $comentarios;
}

/**
 * Obter um comentário específico
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $comentario_id ID do comentário
 * @return array|bool Dados do comentário ou false se não encontrado
 */
function obterComentario($conn, $comentario_id) {
    $sql = "SELECT c.id, c.usuario_id, c.artigo_id, c.conteudo, c.data_comentario, u.nome AS nome_usuario 
            FROM comentarios c 
            JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $comentario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($comentario = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $comentario;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Editar um comentário
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $comentario_id ID do comentário
 * @param int $usuario_id ID do usuário (para verificar permissão)
 * @param string $novo_conteudo Novo conteúdo do comentário
 * @return array Resultado da operação com status e mensagem
 */
function editarComentario($conn, $comentario_id, $usuario_id, $novo_conteudo) {
    $resultado = [
        'status' => false,
        'mensagem' => ''
    ];
    
    // Verificar se o comentário existe e pertence ao usuário
    $sql = "SELECT usuario_id FROM comentarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $comentario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Verificar se o usuário é o autor do comentário ou um administrador
            if ($row['usuario_id'] != $usuario_id && !isAdmin($conn, $usuario_id)) {
                $resultado['mensagem'] = "Você não tem permissão para editar este comentário.";
                mysqli_stmt_close($stmt);
                return $resultado;
            }
        } else {
            $resultado['mensagem'] = "Comentário não encontrado.";
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Atualizar o comentário
    $sql = "UPDATE comentarios SET conteudo = ? WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $novo_conteudo, $comentario_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Comentário atualizado com sucesso!";
        } else {
            $resultado['mensagem'] = "Erro ao atualizar comentário. Por favor, tente novamente.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Excluir um comentário
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $comentario_id ID do comentário
 * @param int $usuario_id ID do usuário (para verificar permissão)
 * @return array Resultado da operação com status e mensagem
 */
function excluirComentario($conn, $comentario_id, $usuario_id) {
    $resultado = [
        'status' => false,
        'mensagem' => ''
    ];
    
    // Verificar se o comentário existe e pertence ao usuário
    $sql = "SELECT usuario_id FROM comentarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $comentario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Verificar se o usuário é o autor do comentário ou um administrador
            if ($row['usuario_id'] != $usuario_id && !isAdmin($conn, $usuario_id)) {
                $resultado['mensagem'] = "Você não tem permissão para excluir este comentário.";
                mysqli_stmt_close($stmt);
                return $resultado;
            }
        } else {
            $resultado['mensagem'] = "Comentário não encontrado.";
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Excluir o comentário
    $sql = "DELETE FROM comentarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $comentario_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Comentário excluído com sucesso!";
        } else {
            $resultado['mensagem'] = "Erro ao excluir comentário. Por favor, tente novamente.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Contar comentários de um artigo
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @return int Número de comentários
 */
function contarComentarios($conn, $artigo_id) {
    $sql = "SELECT COUNT(*) AS total FROM comentarios WHERE artigo_id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $artigo_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['total'];
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return 0;
}
?>
