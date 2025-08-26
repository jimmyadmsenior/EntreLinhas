<?php
// Funções para processamento de artigos
require_once 'imagens_artigos.php';

/**
 * Função para enviar um artigo com suporte a múltiplas imagens
 * @param mysqli $conn Conexão com o banco de dados
 * @param array $artigo Dados do artigo (titulo, conteudo, categoria, id_usuario)
 * @param array $imagens Dados das imagens (opcional)
 * @return array Resultado do envio com status e mensagem
 */
function enviarArtigo($conn, $artigo, $imagens = null) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'artigo_id' => 0
    ];
    
    // Validar campos obrigatórios
    if (empty($artigo['titulo']) || empty($artigo['conteudo']) || empty($artigo['categoria']) || empty($artigo['id_usuario'])) {
        $resultado['mensagem'] = "Todos os campos obrigatórios devem ser preenchidos.";
        return $resultado;
    }
    
    // Iniciar transação
    mysqli_begin_transaction($conn);
    
    try {
        // Preparar a inserção no banco de dados (sem imagem inicialmente)
        $sql = "INSERT INTO artigos (titulo, conteudo, categoria, imagem, id_usuario, data_criacao, status) 
                VALUES (?, ?, ?, '', ?, NOW(), 'pendente')";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular parâmetros
            mysqli_stmt_bind_param($stmt, "sssi", 
                $artigo['titulo'], 
                $artigo['conteudo'], 
                $artigo['categoria'],
                $artigo['id_usuario']
            );
            
            // Executar a instrução
            if (mysqli_stmt_execute($stmt)) {
                $resultado['artigo_id'] = mysqli_insert_id($conn);
                
                // Processar imagens, se houver
                if ($imagens && !empty($imagens['name'][0])) {
                    $upload_resultado = processarMultiplasImagens($imagens);
                    
                    if (!$upload_resultado['status']) {
                        // Reverter em caso de erro
                        mysqli_rollback($conn);
                        $resultado['mensagem'] = $upload_resultado['mensagem'];
                        return $resultado;
                    }
                    
                    // Associar imagens ao artigo
                    if (!associarImagensAoArtigo($conn, $resultado['artigo_id'], $upload_resultado['caminhos'])) {
                        // Reverter em caso de erro
                        mysqli_rollback($conn);
                        $resultado['mensagem'] = "Erro ao associar imagens ao artigo.";
                        return $resultado;
                    }
                    
                    // Atualizar campo imagem na tabela artigos com o caminho da primeira imagem
                    $sql_update = "UPDATE artigos SET imagem = ? WHERE id = ?";
                    if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                        $primeira_imagem = $upload_resultado['caminhos'][0];
                        mysqli_stmt_bind_param($stmt_update, "si", $primeira_imagem, $resultado['artigo_id']);
                        mysqli_stmt_execute($stmt_update);
                        mysqli_stmt_close($stmt_update);
                    }
                }
                
                // Confirmar transação
                mysqli_commit($conn);
                
                $resultado['status'] = true;
                $resultado['mensagem'] = "Artigo enviado com sucesso! Aguarde a aprovação.";
            } else {
                // Reverter em caso de erro
                mysqli_rollback($conn);
                $resultado['mensagem'] = "Erro ao enviar artigo. Por favor, tente novamente.";
            }
            
            // Fechar a instrução
            mysqli_stmt_close($stmt);
        } else {
            // Reverter em caso de erro
            mysqli_rollback($conn);
            $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
        }
    } catch (Exception $e) {
        // Em caso de exceção, reverter transação
        mysqli_rollback($conn);
        $resultado['mensagem'] = "Erro: " . $e->getMessage();
    }
    
    return $resultado;
}

/**
 * Processa o upload de uma imagem
 * @param array $imagem Dados da imagem do formulário $_FILES
 * @return array Resultado do upload com status e mensagem
 */
function processarUploadImagem($imagem) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'nome_arquivo' => ''
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
    
    // Gerar um nome de arquivo único
    $nome_arquivo = uniqid() . '.' . $extensao;
    $destino = $diretorio_upload . $nome_arquivo;
    
    // Tentar fazer o upload
    if (move_uploaded_file($imagem["tmp_name"], $destino)) {
        $resultado['status'] = true;
        $resultado['mensagem'] = "Imagem enviada com sucesso.";
        $resultado['nome_arquivo'] = $nome_arquivo;
    } else {
        $resultado['mensagem'] = "Houve um erro ao fazer o upload do arquivo.";
    }
    
    return $resultado;
}

/**
 * Envia uma notificação por email ao administrador sobre um novo artigo
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo enviado
 * @return bool Resultado do envio de email
 */
function enviarNotificacaoNovoArtigo($conn, $artigo_id) {
    // Buscar informações do artigo e do usuário
    $sql = "SELECT a.titulo, a.categoria, a.data_criacao, u.nome, u.email 
            FROM artigos a 
            JOIN usuarios u ON a.id_usuario = u.id 
            WHERE a.id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $artigo_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($artigo = mysqli_fetch_assoc($result)) {
            // Destinatário (administrador)
            $para = ADMIN_EMAIL;
            
            // Assunto do e-mail
            $assunto = "Novo Artigo Pendente - EntreLinhas";
            
            // Montar corpo do e-mail em HTML
            $mensagem = "
            <html>
            <head>
                <title>Novo Artigo Pendente</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #000; color: #fff; padding: 15px; text-align: center; }
                    .content { padding: 20px; border: 1px solid #ddd; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                    .btn { display: inline-block; padding: 10px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>EntreLinhas - Novo Artigo</h1>
                    </div>
                    <div class='content'>
                        <h2>Um novo artigo foi enviado para aprovação</h2>
                        <p><strong>Título:</strong> {$artigo['titulo']}</p>
                        <p><strong>Autor:</strong> {$artigo['nome']} ({$artigo['email']})</p>
                        <p><strong>Categoria:</strong> {$artigo['categoria']}</p>
                        <p><strong>Data de envio:</strong> " . date("d/m/Y H:i", strtotime($artigo['data_criacao'])) . "</p>
                        <p>Para revisar e aprovar este artigo, acesse o painel de administração.</p>
                        <p><a href='http://seusite.com.br/PAGES/admin.php' class='btn'>Acessar Painel</a></p>
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
            
            // Tentar enviar e-mail
            return mail($para, $assunto, $mensagem, $headers);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Atualiza o status de um artigo
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @param string $status Novo status (aprovado, rejeitado, pendente)
 * @return bool Resultado da atualização
 */
function atualizarStatusArtigo($conn, $artigo_id, $status) {
    $sql = "UPDATE artigos SET status = ?";
    
    // Se for aprovado, definir a data de publicação
    if ($status == 'aprovado') {
        $sql .= ", data_publicacao = NOW()";
    }
    
    $sql .= " WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $status, $artigo_id);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            // Enviar notificação por email ao autor sobre o status do artigo
            enviarNotificacaoStatusArtigo($conn, $artigo_id, $status);
            return true;
        }
    }
    
    return false;
}

/**
 * Envia uma notificação por email ao autor sobre o status do artigo
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @param string $status Status do artigo (aprovado, rejeitado)
 * @return bool Resultado do envio de email
 */
function enviarNotificacaoStatusArtigo($conn, $artigo_id, $status) {
    // Buscar informações do artigo e do usuário
    $sql = "SELECT a.titulo, u.nome, u.email 
            FROM artigos a 
            JOIN usuarios u ON a.id_usuario = u.id 
            WHERE a.id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $artigo_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($artigo = mysqli_fetch_assoc($result)) {
            // Destinatário (autor do artigo)
            $para = $artigo['email'];
            
            // Assunto do e-mail
            $assunto = "Seu Artigo foi " . ($status == 'aprovado' ? 'Aprovado' : 'Rejeitado') . " - EntreLinhas";
            
            // Conteúdo do e-mail com base no status
            $conteudo = "";
            $botao_texto = "";
            $botao_url = "";
            
            if ($status == 'aprovado') {
                $conteudo = "
                <h2>Seu Artigo Foi Aprovado!</h2>
                <p>Temos o prazer de informar que seu artigo <strong>{$artigo['titulo']}</strong> foi aprovado e já está publicado no EntreLinhas.</p>
                <p>Agradecemos sua contribuição e esperamos continuar contando com seus excelentes textos!</p>
                ";
                $botao_texto = "Ver Meu Artigo";
                $botao_url = "http://seusite.com.br/PAGES/artigo.php?id=" . $artigo_id;
            } else {
                $conteudo = "
                <h2>Revisão do Seu Artigo</h2>
                <p>Agradecemos pelo envio do seu artigo <strong>{$artigo['titulo']}</strong>.</p>
                <p>Após análise da nossa equipe editorial, infelizmente o conteúdo não foi aprovado para publicação neste momento.</p>
                <p>Você pode revisar e editar seu artigo para uma nova submissão.</p>
                ";
                $botao_texto = "Editar Meu Artigo";
                $botao_url = "http://seusite.com.br/PAGES/editar-artigo.php?id=" . $artigo_id;
            }
            
            // Montar corpo do e-mail em HTML
            $mensagem = "
            <html>
            <head>
                <title>" . ($status == 'aprovado' ? 'Artigo Aprovado' : 'Artigo Rejeitado') . "</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #000; color: #fff; padding: 15px; text-align: center; }
                    .content { padding: 20px; border: 1px solid #ddd; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                    .btn { display: inline-block; padding: 10px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>EntreLinhas</h1>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>{$artigo['nome']}</strong>,</p>
                        $conteudo
                        <p><a href='$botao_url' class='btn'>$botao_texto</a></p>
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
            
            // Tentar enviar e-mail
            return mail($para, $assunto, $mensagem, $headers);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Obter lista de artigos com base em critérios
 * @param mysqli $conn Conexão com o banco de dados
 * @param array $filtros Filtros de busca (categoria, status, id_usuario, etc)
 * @param int $limite Quantidade máxima de resultados
 * @param int $pagina Número da página para paginação
 * @return array Lista de artigos
 */
function listarArtigos($conn, $filtros = [], $limite = 10, $pagina = 1) {
    // Construir a consulta SQL base
    $sql = "SELECT a.*, u.nome as nome_autor 
            FROM artigos a 
            JOIN usuarios u ON a.id_usuario = u.id 
            WHERE 1=1";
    
    // Adicionar filtros à consulta
    if (isset($filtros['categoria']) && !empty($filtros['categoria'])) {
        $sql .= " AND a.categoria = '" . mysqli_real_escape_string($conn, $filtros['categoria']) . "'";
    }
    
    if (isset($filtros['status']) && !empty($filtros['status'])) {
        $sql .= " AND a.status = '" . mysqli_real_escape_string($conn, $filtros['status']) . "'";
    }
    
    if (isset($filtros['id_usuario']) && !empty($filtros['id_usuario'])) {
        $sql .= " AND a.id_usuario = " . intval($filtros['id_usuario']);
    }
    
    if (isset($filtros['busca']) && !empty($filtros['busca'])) {
        $busca = mysqli_real_escape_string($conn, $filtros['busca']);
        $sql .= " AND (a.titulo LIKE '%$busca%' OR a.conteudo LIKE '%$busca%')";
    }
    
    // Ordenar resultados
    $sql .= " ORDER BY ";
    if (isset($filtros['status']) && $filtros['status'] == 'aprovado') {
        $sql .= "a.data_publicacao DESC";
    } else {
        $sql .= "a.data_criacao DESC";
    }
    
    // Adicionar paginação
    $offset = ($pagina - 1) * $limite;
    $sql .= " LIMIT $limite OFFSET $offset";
    
    // Executar a consulta
    $resultado = mysqli_query($conn, $sql);
    $artigos = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $artigos[] = $row;
        }
        mysqli_free_result($resultado);
    }
    
    return $artigos;
}

/**
 * Obter detalhes de um artigo específico com suas imagens
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @return array|bool Dados do artigo ou false se não encontrado
 */
function obterArtigo($conn, $artigo_id) {
    $sql = "SELECT a.*, u.nome as nome_autor, u.email as email_autor 
            FROM artigos a 
            JOIN usuarios u ON a.id_usuario = u.id 
            WHERE a.id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $artigo_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($artigo = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            
            // Buscar imagens associadas ao artigo
            $artigo['imagens'] = obterImagensArtigo($conn, $artigo_id);
            
            return $artigo;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Editar um artigo existente com suporte a múltiplas imagens
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @param array $dados Novos dados do artigo
 * @param array $imagens Dados das novas imagens (opcional)
 * @return array Resultado da edição com status e mensagem
 */
function editarArtigo($conn, $artigo_id, $dados, $imagens = null) {
    $resultado = [
        'status' => false,
        'mensagem' => ''
    ];
    
    // Verificar se o artigo existe e se o usuário tem permissão
    $artigo_atual = obterArtigo($conn, $artigo_id);
    if (!$artigo_atual) {
        $resultado['mensagem'] = "Artigo não encontrado.";
        return $resultado;
    }
    
    // Verificar se é um administrador ou o autor do artigo
    $is_admin = isset($_SESSION['email']) && $_SESSION['email'] === ADMIN_EMAIL;
    $is_autor = isset($_SESSION['id']) && $_SESSION['id'] === $artigo_atual['id_usuario'];
    
    if (!$is_admin && !$is_autor) {
        $resultado['mensagem'] = "Você não tem permissão para editar este artigo.";
        return $resultado;
    }
    
    // Iniciar transação
    mysqli_begin_transaction($conn);
    
    try {
        // Processar upload de novas imagens, se houver
        if ($imagens && !empty($imagens['name'][0])) {
            $upload_resultado = processarMultiplasImagens($imagens);
            
            if (!$upload_resultado['status']) {
                $resultado['mensagem'] = $upload_resultado['mensagem'];
                return $resultado;
            }
            
            // Associar novas imagens ao artigo
            if (!associarImagensAoArtigo($conn, $artigo_id, $upload_resultado['caminhos'])) {
                mysqli_rollback($conn);
                $resultado['mensagem'] = "Erro ao associar imagens ao artigo.";
                return $resultado;
            }
            
            // Atualizar campo imagem na tabela artigos com o caminho da primeira imagem nova
            $sql_update_img = "UPDATE artigos SET imagem = ? WHERE id = ?";
            if ($stmt_update = mysqli_prepare($conn, $sql_update_img)) {
                $primeira_imagem = $upload_resultado['caminhos'][0];
                mysqli_stmt_bind_param($stmt_update, "si", $primeira_imagem, $artigo_id);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
            }
        }
        
        // Preparar a atualização dos dados do artigo
        $sql = "UPDATE artigos SET 
                titulo = ?, 
                conteudo = ?, 
                categoria = ?, 
                status = ?
                WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Definir o status com base em quem está editando
            $status = 'pendente'; // Por padrão, volta para pendente
            
            if ($is_admin) {
                // Se for admin, mantém o status atual
                $status = $artigo_atual['status'];
            }
            
            // Vincular parâmetros
            mysqli_stmt_bind_param($stmt, "ssssi", 
                $dados['titulo'], 
                $dados['conteudo'], 
                $dados['categoria'], 
                $status,
                $artigo_id
            );
            
            // Executar a instrução
            if (mysqli_stmt_execute($stmt)) {
                // Confirmar transação
                mysqli_commit($conn);
                
                $resultado['status'] = true;
                
                if ($is_admin) {
                    $resultado['mensagem'] = "Artigo atualizado com sucesso!";
                } else {
                    $resultado['mensagem'] = "Artigo atualizado com sucesso! Ele será revisado novamente.";
                    // Notificar o administrador sobre a edição
                    enviarNotificacaoNovoArtigo($conn, $artigo_id);
                }
            } else {
                // Reverter em caso de erro
                mysqli_rollback($conn);
                $resultado['mensagem'] = "Erro ao atualizar artigo. Por favor, tente novamente.";
            }
            
            // Fechar a instrução
            mysqli_stmt_close($stmt);
        } else {
            // Reverter em caso de erro
            mysqli_rollback($conn);
            $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
        }
    } catch (Exception $e) {
        // Em caso de exceção, reverter transação
        mysqli_rollback($conn);
        $resultado['mensagem'] = "Erro: " . $e->getMessage();
    }
    
    return $resultado;
}

/**
 * Excluir um artigo e todas suas imagens associadas
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $artigo_id ID do artigo
 * @return bool Resultado da exclusão
 */
function excluirArtigo($conn, $artigo_id) {
    // Verificar se o artigo existe
    $artigo = obterArtigo($conn, $artigo_id);
    if (!$artigo) {
        return false;
    }
    
    // Iniciar transação
    mysqli_begin_transaction($conn);
    
    try {
        // Remover todas as imagens associadas ao artigo
        if (!empty($artigo['imagens'])) {
            foreach ($artigo['imagens'] as $imagem) {
                removerImagemArtigo($conn, $imagem['id']);
            }
        }
        
        // Excluir o artigo do banco de dados
        $sql = "DELETE FROM artigos WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $artigo_id);
            
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            if ($resultado) {
                mysqli_commit($conn);
                return true;
            } else {
                mysqli_rollback($conn);
                return false;
            }
        } else {
            mysqli_rollback($conn);
            return false;
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

/**
 * Contar o total de artigos para paginação
 * @param mysqli $conn Conexão com o banco de dados
 * @param array $filtros Filtros de busca
 * @return int Total de artigos
 */
function contarArtigos($conn, $filtros = []) {
    // Construir a consulta SQL base
    $sql = "SELECT COUNT(*) as total FROM artigos a WHERE 1=1";
    
    // Adicionar filtros à consulta
    if (isset($filtros['categoria']) && !empty($filtros['categoria'])) {
        $sql .= " AND a.categoria = '" . mysqli_real_escape_string($conn, $filtros['categoria']) . "'";
    }
    
    if (isset($filtros['status']) && !empty($filtros['status'])) {
        $sql .= " AND a.status = '" . mysqli_real_escape_string($conn, $filtros['status']) . "'";
    }
    
    if (isset($filtros['id_usuario']) && !empty($filtros['id_usuario'])) {
        $sql .= " AND a.id_usuario = " . intval($filtros['id_usuario']);
    }
    
    if (isset($filtros['busca']) && !empty($filtros['busca'])) {
        $busca = mysqli_real_escape_string($conn, $filtros['busca']);
        $sql .= " AND (a.titulo LIKE '%$busca%' OR a.conteudo LIKE '%$busca%')";
    }
    
    // Executar a consulta
    $resultado = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($resultado);
    
    return $row['total'];
}

// Outras funções relacionadas a artigos podem ser adicionadas conforme necessário
?>
