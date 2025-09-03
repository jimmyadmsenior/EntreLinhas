<?php
// Criar um helper de usuário para recuperar a foto de perfil - Versão PDO
// Este arquivo será incluído em todas as páginas que precisam exibir a foto de perfil do usuário

/**
 * Obtém a foto de perfil do usuário usando PDO
 * 
 * @param PDO $pdo A conexão PDO com o banco de dados (ignorada, será criada uma nova)
 * @param int $usuario_id O ID do usuário
 * @return string|null A imagem em base64 ou null se não existir
 */
function obter_foto_perfil_pdo($pdo, $usuario_id) {
    $foto_perfil = null;
    
    // SEMPRE criar uma nova conexão para evitar problemas com conexões fechadas
    $new_conn = null;
    
    try {
        // Verificar se temos as constantes definidas
        if (!defined('DB_SERVER') || !defined('DB_USERNAME') || !defined('DB_PASSWORD') || !defined('DB_NAME')) {
            // Tentar carregar o arquivo de configuração se não estiver carregado
            if (file_exists(__DIR__ . '/../config_pdo.php')) {
                require_once __DIR__ . '/../config_pdo.php';
            } else {
                error_log('Arquivo de configuração PDO não encontrado');
                return null;
            }
        }
        
        // Criar uma nova conexão PDO (ignorando a que foi passada)
        try {
            $new_conn = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME.";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
            $new_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log('Falha ao conectar ao banco de dados: ' . $e->getMessage());
            return null;
        }
        
        // Fazer a consulta com a nova conexão
        $sql_foto = "SELECT imagem_base64 FROM fotos_perfil WHERE id_usuario = ?";
        
        try {
            $stmt_foto = $new_conn->prepare($sql_foto);
            $stmt_foto->bindParam(1, $usuario_id, PDO::PARAM_INT);
            $stmt_foto->execute();
            $result = $stmt_foto->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $foto_perfil = $result["imagem_base64"];
            }
            
            $stmt_foto = null; // Libera o statement
        } catch (PDOException $e) {
            error_log('Erro na consulta de foto de perfil: ' . $e->getMessage());
            return null;
        }
    } catch (Exception $e) {
        error_log('Erro ao obter foto de perfil: ' . $e->getMessage());
    }
    
    // Conexão PDO é fechada automaticamente quando a variável sai de escopo
    $new_conn = null; // Explicitamente limpa a referência
    
    return $foto_perfil;
}

/**
 * Versão de compatibilidade que chama a função PDO
 * Mantida para compatibilidade com código existente
 */
function obter_foto_perfil($conn, $usuario_id) {
    // Usar a função PDO
    return obter_foto_perfil_pdo(null, $usuario_id);
}
?>
