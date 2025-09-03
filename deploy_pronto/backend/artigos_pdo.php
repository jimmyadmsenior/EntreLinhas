<?php
// Funções de artigos com PDO

/**
 * Obter um artigo específico pelo ID
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $artigo_id ID do artigo
 * @return array|false Dados do artigo ou false se não encontrado
 */
function obterArtigo_pdo($pdo, $artigo_id) {
    $sql = "SELECT a.*, u.nome as nome_autor, u.email as email_autor 
            FROM artigos a 
            JOIN usuarios u ON a.id_usuario = u.id 
            WHERE a.id = :artigo_id";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':artigo_id', $artigo_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $artigo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($artigo) {
            // Buscar imagens associadas ao artigo
            $artigo['imagens'] = obterImagensArtigo_pdo($pdo, $artigo_id);
            return $artigo;
        }
        
        return false;
    } catch (PDOException $e) {
        // Registrar erro
        error_log("Erro ao obter artigo: " . $e->getMessage());
        return false;
    }
}

/**
 * Obter imagens associadas a um artigo
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $artigo_id ID do artigo
 * @return array Lista de imagens
 */
function obterImagensArtigo_pdo($pdo, $artigo_id) {
    $imagens = [];
    
    $sql = "SELECT id, caminho, legenda 
            FROM artigos_imagens 
            WHERE artigo_id = :artigo_id 
            ORDER BY ordem ASC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':artigo_id', $artigo_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Registrar erro
        error_log("Erro ao obter imagens do artigo: " . $e->getMessage());
        return [];
    }
}

/**
 * Verificar se um usuário é administrador
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $usuario_id ID do usuário
 * @return bool True se o usuário for administrador
 */
function isAdmin_pdo($pdo, $usuario_id) {
    $sql = "SELECT admin FROM usuarios WHERE id = :usuario_id";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar se o resultado existe e o valor da coluna admin é 1
        return ($result && $result['admin'] == 1);
    } catch (PDOException $e) {
        // Registrar erro
        error_log("Erro ao verificar se usuário é admin: " . $e->getMessage());
        return false;
    }
}

/**
 * Listar artigos com base em critérios
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param array $filtros Filtros de busca (categoria, status, id_usuario, etc)
 * @param int $limite Quantidade máxima de resultados
 * @param int $pagina Número da página para paginação
 * @return array Lista de artigos
 */
function listarArtigos_pdo($pdo, $filtros = [], $limite = 10, $pagina = 1) {
    // Construir a consulta SQL base
    $sql = "SELECT a.*, u.nome as nome_autor 
            FROM artigos a 
            JOIN usuarios u ON a.id_usuario = u.id 
            WHERE 1=1";
    
    $params = [];
    
    // Adicionar filtros à consulta
    if (isset($filtros['categoria']) && !empty($filtros['categoria'])) {
        $sql .= " AND a.categoria = :categoria";
        $params[':categoria'] = $filtros['categoria'];
    }
    
    if (isset($filtros['status']) && !empty($filtros['status'])) {
        $sql .= " AND a.status = :status";
        $params[':status'] = $filtros['status'];
    }
    
    if (isset($filtros['id_usuario']) && !empty($filtros['id_usuario'])) {
        $sql .= " AND a.id_usuario = :id_usuario";
        $params[':id_usuario'] = $filtros['id_usuario'];
    }
    
    if (isset($filtros['busca']) && !empty($filtros['busca'])) {
        $sql .= " AND (a.titulo LIKE :busca OR a.conteudo LIKE :busca)";
        $params[':busca'] = '%' . $filtros['busca'] . '%';
    }
    
    // Ordenar resultados
    if (isset($filtros['ordem']) && !empty($filtros['ordem'])) {
        switch ($filtros['ordem']) {
            case 'recentes':
                $sql .= " ORDER BY a.data_criacao DESC";
                break;
            case 'antigos':
                $sql .= " ORDER BY a.data_criacao ASC";
                break;
            case 'titulo':
                $sql .= " ORDER BY a.titulo ASC";
                break;
            default:
                $sql .= " ORDER BY a.data_criacao DESC";
        }
    } else {
        $sql .= " ORDER BY a.data_criacao DESC";
    }
    
    // Adicionar paginação
    $offset = ($pagina - 1) * $limite;
    $sql .= " LIMIT :limite OFFSET :offset";
    $params[':limite'] = $limite;
    $params[':offset'] = $offset;
    
    try {
        $stmt = $pdo->prepare($sql);
        
        // Vincular todos os parâmetros
        foreach ($params as $param => $value) {
            $tipo = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($param, $value, $tipo);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Registrar erro
        error_log("Erro ao listar artigos: " . $e->getMessage());
        return [];
    }
}

/**
 * Contar o total de artigos para paginação
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param array $filtros Filtros de busca
 * @return int Total de artigos
 */
function contarArtigos_pdo($pdo, $filtros = []) {
    // Construir a consulta SQL base
    $sql = "SELECT COUNT(*) as total FROM artigos a WHERE 1=1";
    
    $params = [];
    
    // Adicionar filtros à consulta
    if (isset($filtros['categoria']) && !empty($filtros['categoria'])) {
        $sql .= " AND a.categoria = :categoria";
        $params[':categoria'] = $filtros['categoria'];
    }
    
    if (isset($filtros['status']) && !empty($filtros['status'])) {
        $sql .= " AND a.status = :status";
        $params[':status'] = $filtros['status'];
    }
    
    if (isset($filtros['id_usuario']) && !empty($filtros['id_usuario'])) {
        $sql .= " AND a.id_usuario = :id_usuario";
        $params[':id_usuario'] = $filtros['id_usuario'];
    }
    
    if (isset($filtros['busca']) && !empty($filtros['busca'])) {
        $sql .= " AND (a.titulo LIKE :busca OR a.conteudo LIKE :busca)";
        $params[':busca'] = '%' . $filtros['busca'] . '%';
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        
        // Vincular todos os parâmetros
        foreach ($params as $param => $value) {
            $tipo = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($param, $value, $tipo);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    } catch (PDOException $e) {
        // Registrar erro
        error_log("Erro ao contar artigos: " . $e->getMessage());
        return 0;
    }
}

/**
 * Processa o upload de uma imagem para o artigo
 * @param array $imagem Array com informações da imagem ($_FILES['imagem'])
 * @return array Array com o resultado do upload
 */
function processarUploadImagem_pdo($imagem) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'nome_arquivo' => '',
        'caminho_relativo' => ''
    ];
    
    // Diretório de upload
    $diretorio_upload = "../assets/images/artigos/";
    
    // Verificar se o diretório existe, caso contrário, criar
    if (!is_dir($diretorio_upload)) {
        mkdir($diretorio_upload, 0755, true);
    }
    
    // Verificar se é uma imagem válida
    $check = getimagesize($imagem["tmp_name"]);
    if ($check === false) {
        $resultado['mensagem'] = "O arquivo enviado não é uma imagem válida.";
        return $resultado;
    }
    
    // Limitar tamanho do arquivo (5MB)
    if ($imagem["size"] > 5000000) {
        $resultado['mensagem'] = "O arquivo é muito grande. O tamanho máximo permitido é 5MB.";
        return $resultado;
    }
    
    // Permitir apenas certos formatos de arquivo
    $extensoes_permitidas = ["jpg", "jpeg", "png", "gif"];
    $extensao = strtolower(pathinfo($imagem["name"], PATHINFO_EXTENSION));
    
    if (!in_array($extensao, $extensoes_permitidas)) {
        $resultado['mensagem'] = "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
        return $resultado;
    }
    
    // Criar um nome único para o arquivo
    $nome_arquivo = uniqid('artigo_') . '.' . $extensao;
    $caminho_arquivo = $diretorio_upload . $nome_arquivo;
    $caminho_relativo = "assets/images/artigos/" . $nome_arquivo;
    
    // Tentar mover o arquivo para o diretório de uploads
    if (move_uploaded_file($imagem["tmp_name"], $caminho_arquivo)) {
        $resultado['status'] = true;
        $resultado['mensagem'] = "Imagem enviada com sucesso.";
        $resultado['nome_arquivo'] = $nome_arquivo;
        $resultado['caminho_relativo'] = $caminho_relativo;
    } else {
        $resultado['mensagem'] = "Ocorreu um erro ao enviar a imagem.";
    }
    
    return $resultado;
}

/**
 * Editar um artigo existente com PDO
 * @param PDO $pdo Conexão PDO com o banco de dados
 * @param int $artigo_id ID do artigo
 * @param string $titulo Novo título do artigo
 * @param string $conteudo Novo conteúdo do artigo
 * @param string $categoria Nova categoria do artigo
 * @param string $imagem Caminho da nova imagem (se houver)
 * @param int $usuario_id ID do usuário que está editando
 * @return bool Resultado da operação
 */
function editarArtigo_pdo($pdo, $artigo_id, $titulo, $conteudo, $categoria, $imagem, $usuario_id) {
    try {
        // Verificar se o usuário tem permissão para editar este artigo
        $sql_check = "SELECT id_usuario, status FROM artigos WHERE id = :artigo_id";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':artigo_id', $artigo_id, PDO::PARAM_INT);
        $stmt_check->execute();
        
        $artigo = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if (!$artigo) {
            return false; // Artigo não encontrado
        }
        
        // Verificar se o usuário é o autor ou um administrador
        $sql_admin = "SELECT tipo FROM usuarios WHERE id = :usuario_id";
        $stmt_admin = $pdo->prepare($sql_admin);
        $stmt_admin->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt_admin->execute();
        
        $usuario = $stmt_admin->fetch(PDO::FETCH_ASSOC);
        $is_admin = $usuario && $usuario['tipo'] === 'admin';
        
        if ($artigo['id_usuario'] != $usuario_id && !$is_admin) {
            return false; // Sem permissão para editar
        }
        
        // Atualizar o artigo
        $sql = "UPDATE artigos SET 
                titulo = :titulo, 
                conteudo = :conteudo, 
                categoria = :categoria";
        
        // Adicionar a imagem à query apenas se fornecida
        if (!empty($imagem)) {
            $sql .= ", imagem = :imagem";
        }
        
        // Status é redefinido para pendente se não for admin
        if (!$is_admin) {
            $sql .= ", status = 'pendente'";
        }
        
        $sql .= " WHERE id = :artigo_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
        $stmt->bindParam(':conteudo', $conteudo, PDO::PARAM_STR);
        $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
        $stmt->bindParam(':artigo_id', $artigo_id, PDO::PARAM_INT);
        
        if (!empty($imagem)) {
            $stmt->bindParam(':imagem', $imagem, PDO::PARAM_STR);
        }
        
        $result = $stmt->execute();
        
        return $result;
    } catch (PDOException $e) {
        error_log("Erro ao editar artigo: " . $e->getMessage());
        return false;
    }
}
?>
