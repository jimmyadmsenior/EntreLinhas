<?php
// Funções para gerenciamento de comentários com PDO

/**
 * Lista os comentários de um artigo
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $artigo_id ID do artigo
 * @return array Lista de comentários
 */
function listarComentarios_pdo($pdo, $artigo_id) {
    $comentarios = [];
    
    $sql = "SELECT c.id, c.conteudo, c.data_criacao AS data_comentario, u.id AS usuario_id, u.nome AS nome_usuario 
            FROM comentarios c 
            JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.artigo_id = :artigo_id 
            ORDER BY c.data_criacao DESC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':artigo_id', $artigo_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Registrar erro
        error_log("Erro ao listar comentários: " . $e->getMessage());
        return [];
    }
}

/**
 * Adiciona um novo comentário
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param array $comentario Dados do comentário (usuario_id, artigo_id, conteudo)
 * @return array Resultado da operação com status e mensagem
 */
function adicionarComentario_pdo($pdo, $comentario) {
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
    
    try {
        // Verificar se o artigo existe
        $sql = "SELECT id, titulo FROM artigos WHERE id = :artigo_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':artigo_id', $comentario['artigo_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            $resultado['mensagem'] = "Artigo não encontrado.";
            return $resultado;
        }
        
        // Verificar se o usuário existe
        $sql = "SELECT id, nome, email FROM usuarios WHERE id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario_id', $comentario['usuario_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            $resultado['mensagem'] = "Usuário não encontrado.";
            return $resultado;
        }
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Inserir o comentário
        $sql = "INSERT INTO comentarios (usuario_id, artigo_id, conteudo) VALUES (:usuario_id, :artigo_id, :conteudo)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario_id', $comentario['usuario_id'], PDO::PARAM_INT);
        $stmt->bindParam(':artigo_id', $comentario['artigo_id'], PDO::PARAM_INT);
        $stmt->bindParam(':conteudo', $comentario['conteudo'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Comentário adicionado com sucesso!";
            $resultado['comentario_id'] = $pdo->lastInsertId();
            
            // Notificar autor do artigo sobre o novo comentário
            // notificarNovoComentario_pdo($pdo, $resultado['comentario_id'], $usuario);
        } else {
            $resultado['mensagem'] = "Erro ao adicionar comentário. Por favor, tente novamente.";
        }
    } catch (PDOException $e) {
        $resultado['mensagem'] = "Erro no sistema: " . $e->getMessage();
        error_log("Erro ao adicionar comentário: " . $e->getMessage());
    }
    
    return $resultado;
}

/**
 * Excluir um comentário
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $comentario_id ID do comentário
 * @param int $usuario_id ID do usuário (para verificar permissão)
 * @param bool $is_admin Se o usuário é admin
 * @return bool Resultado da exclusão
 */
function excluirComentario_pdo($pdo, $comentario_id, $usuario_id, $is_admin = false) {
    try {
        // Se não for admin, verificar se o comentário pertence ao usuário
        if (!$is_admin) {
            $sql = "SELECT id FROM comentarios WHERE id = :comentario_id AND usuario_id = :usuario_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':comentario_id', $comentario_id, PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                return false; // Não tem permissão
            }
        }
        
        // Excluir o comentário
        $sql = "DELETE FROM comentarios WHERE id = :comentario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comentario_id', $comentario_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erro ao excluir comentário: " . $e->getMessage());
        return false;
    }
}

/**
 * Obter um comentário específico
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $comentario_id ID do comentário
 * @return array|bool Dados do comentário ou false se não encontrado
 */
function obterComentario_pdo($pdo, $comentario_id) {
    $sql = "SELECT c.*, u.nome as nome_usuario 
            FROM comentarios c 
            JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.id = :comentario_id";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comentario_id', $comentario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao obter comentário: " . $e->getMessage());
        return false;
    }
}

/**
 * Marcar um comentário como moderado (aprovado ou rejeitado)
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $comentario_id ID do comentário
 * @param string $status Status (aprovado, rejeitado)
 * @return bool Resultado da operação
 */
function moderarComentario_pdo($pdo, $comentario_id, $status) {
    $sql = "UPDATE comentarios SET status = :status WHERE id = :comentario_id";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':comentario_id', $comentario_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erro ao moderar comentário: " . $e->getMessage());
        return false;
    }
}

/**
 * Notificar o autor do artigo sobre um novo comentário
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $comentario_id ID do comentário
 * @param array $autor_comentario Dados do autor do comentário
 */
function notificarNovoComentario_pdo($pdo, $comentario_id, $autor_comentario) {
    try {
        // Buscar informações do comentário e do artigo
        $sql = "SELECT c.id, c.conteudo, c.artigo_id, a.titulo, a.id_usuario 
                FROM comentarios c 
                JOIN artigos a ON c.artigo_id = a.id 
                WHERE c.id = :comentario_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comentario_id', $comentario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Buscar informações do autor do artigo
            $sql_autor = "SELECT email, nome FROM usuarios WHERE id = :id_usuario";
            $stmt_autor = $pdo->prepare($sql_autor);
            $stmt_autor->bindParam(':id_usuario', $row['id_usuario'], PDO::PARAM_INT);
            $stmt_autor->execute();
            
            if ($autor_artigo = $stmt_autor->fetch(PDO::FETCH_ASSOC)) {
                // Implementar lógica de envio de email
                // ...
            }
        }
    } catch (PDOException $e) {
        error_log("Erro ao notificar sobre novo comentário: " . $e->getMessage());
    }
}
?>
