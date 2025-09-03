<?php
// Funções para gerenciamento de usuários com PDO

/**
 * Registrar um novo usuário
 * @param PDO $pdo Conexão com o banco de dados
 * @param array $usuario Dados do usuário (nome, email, senha)
 * @return array Resultado do registro com status e mensagem
 */
function registrarUsuario_pdo($pdo, $usuario) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'usuario_id' => 0
    ];
    
    try {
        // Verificar se o email já está em uso
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $usuario['email'], PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $resultado['mensagem'] = "Este e-mail já está sendo usado.";
            return $resultado;
        }
        
        // Criptografar a senha
        $senha_hash = password_hash($usuario['senha'], PASSWORD_DEFAULT);
        
        // Preparar a inserção
        $sql = "INSERT INTO usuarios (nome, email, senha, data_registro) VALUES (:nome, :email, :senha, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $usuario['nome'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $usuario['email'], PDO::PARAM_STR);
        $stmt->bindParam(':senha', $senha_hash, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Registro realizado com sucesso!";
            $resultado['usuario_id'] = $pdo->lastInsertId();
        } else {
            $resultado['mensagem'] = "Erro ao registrar. Por favor, tente novamente mais tarde.";
        }
    } catch (PDOException $e) {
        $resultado['mensagem'] = "Erro no sistema: " . $e->getMessage();
        // Log do erro em um arquivo
        error_log("Erro ao registrar usuário: " . $e->getMessage());
    }
    
    return $resultado;
}

/**
 * Verificar credenciais de login
 * @param PDO $pdo Conexão com o banco de dados
 * @param string $email Email do usuário
 * @param string $senha Senha do usuário
 * @return array Resultado do login com status, mensagem e dados do usuário
 */
function verificarLogin_pdo($pdo, $email, $senha) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'usuario' => null
    ];
    
    try {
        // Preparar a consulta
        $sql = "SELECT id, nome, email, senha, admin, foto_perfil, data_registro FROM usuarios WHERE email = :email";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            if ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Verificar a senha
                if (password_verify($senha, $usuario['senha'])) {
                    // Senha correta
                    $resultado['status'] = true;
                    $resultado['mensagem'] = "Login realizado com sucesso!";
                    
                    // Remover a senha do array antes de retornar
                    unset($usuario['senha']);
                    $resultado['usuario'] = $usuario;
                } else {
                    $resultado['mensagem'] = "Senha incorreta.";
                }
            }
        } else {
            $resultado['mensagem'] = "Nenhuma conta encontrada com esse e-mail.";
        }
    } catch (PDOException $e) {
        $resultado['mensagem'] = "Erro no sistema: " . $e->getMessage();
        // Log do erro
        error_log("Erro ao verificar login: " . $e->getMessage());
    }
    
    return $resultado;
}

/**
 * Atualizar perfil do usuário
 * @param PDO $pdo Conexão com o banco de dados
 * @param array $dados Dados do perfil (id, nome, email, etc)
 * @return array Resultado da atualização com status e mensagem
 */
function atualizarPerfil_pdo($pdo, $dados) {
    $resultado = [
        'status' => false,
        'mensagem' => ''
    ];
    
    try {
        // Construir a query de atualização
        $sql = "UPDATE usuarios SET nome = :nome";
        $params = [':nome' => $dados['nome'], ':id' => $dados['id']];
        
        // Verificar se o email foi alterado
        if (isset($dados['email']) && !empty($dados['email'])) {
            // Verificar se o novo email já está em uso por outro usuário
            $check_sql = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->bindParam(':email', $dados['email'], PDO::PARAM_STR);
            $check_stmt->bindParam(':id', $dados['id'], PDO::PARAM_INT);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                $resultado['mensagem'] = "Este e-mail já está sendo usado por outro usuário.";
                return $resultado;
            }
            
            $sql .= ", email = :email";
            $params[':email'] = $dados['email'];
        }
        
        // Verificar se a senha foi fornecida para alteração
        if (isset($dados['senha']) && !empty($dados['senha'])) {
            $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $sql .= ", senha = :senha";
            $params[':senha'] = $senha_hash;
        }
        
        // Verificar se há foto de perfil para atualizar
        if (isset($dados['foto_perfil']) && !empty($dados['foto_perfil'])) {
            $sql .= ", foto_perfil = :foto_perfil";
            $params[':foto_perfil'] = $dados['foto_perfil'];
        }
        
        // Finalizar a query
        $sql .= " WHERE id = :id";
        
        // Executar a atualização
        $stmt = $pdo->prepare($sql);
        foreach ($params as $param => $value) {
            $tipo = (is_int($value)) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($param, $value, $tipo);
        }
        
        if ($stmt->execute()) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Perfil atualizado com sucesso!";
        } else {
            $resultado['mensagem'] = "Erro ao atualizar perfil.";
        }
    } catch (PDOException $e) {
        $resultado['mensagem'] = "Erro no sistema: " . $e->getMessage();
        error_log("Erro ao atualizar perfil: " . $e->getMessage());
    }
    
    return $resultado;
}

/**
 * Processar upload de foto de perfil
 * @param array $foto Dados da foto do $_FILES
 * @return array Resultado do upload com status, mensagem e caminho da foto
 */
function processarFotoPerfil_pdo($foto) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'caminho' => ''
    ];
    
    // Diretório para upload
    $diretorio_upload = "../assets/images/perfis/";
    
    // Verificar se o diretório existe, caso contrário, criar
    if (!is_dir($diretorio_upload)) {
        mkdir($diretorio_upload, 0755, true);
    }
    
    // Verificar se é uma imagem válida
    $check = getimagesize($foto["tmp_name"]);
    if ($check === false) {
        $resultado['mensagem'] = "O arquivo enviado não é uma imagem válida.";
        return $resultado;
    }
    
    // Limitar tamanho do arquivo (2MB)
    if ($foto["size"] > 2000000) {
        $resultado['mensagem'] = "O arquivo é muito grande. O tamanho máximo permitido é 2MB.";
        return $resultado;
    }
    
    // Permitir apenas certos formatos de arquivo
    $extensoes_permitidas = ["jpg", "jpeg", "png", "gif"];
    $extensao = strtolower(pathinfo($foto["name"], PATHINFO_EXTENSION));
    
    if (!in_array($extensao, $extensoes_permitidas)) {
        $resultado['mensagem'] = "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
        return $resultado;
    }
    
    // Gerar um nome de arquivo único
    $nome_arquivo = uniqid() . '.' . $extensao;
    $destino = $diretorio_upload . $nome_arquivo;
    
    // Tentar fazer o upload
    if (move_uploaded_file($foto["tmp_name"], $destino)) {
        $resultado['status'] = true;
        $resultado['mensagem'] = "Foto de perfil enviada com sucesso.";
        $resultado['caminho'] = "assets/images/perfis/" . $nome_arquivo;
    } else {
        $resultado['mensagem'] = "Houve um erro ao fazer o upload do arquivo.";
    }
    
    return $resultado;
}
?>
