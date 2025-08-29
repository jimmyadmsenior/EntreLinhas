<?php
// Criar um helper de usuário para recuperar a foto de perfil
// Este arquivo será incluído em todas as páginas que precisam exibir a foto de perfil do usuário

/**
 * Obtém a foto de perfil do usuário - FUNÇÃO CORRIGIDA (versão PDO)
 * 
 * @param PDO $conn A conexão com o banco de dados
 * @param int $usuario_id O ID do usuário
 * @return string|null A imagem em base64 ou null se não existir
 */
function obter_foto_perfil($conn, $usuario_id) {
    $foto_perfil = null;
    
    try {
        // Verificar se a conexão foi passada e está ativa
        if (!$conn || !($conn instanceof PDO)) {
            // Verificar se temos as constantes definidas
            if (!defined('DB_SERVER') || !defined('DB_USERNAME') || !defined('DB_PASSWORD') || !defined('DB_NAME')) {
                // Tentar carregar o arquivo de configuração se não estiver carregado
                if (file_exists(__DIR__ . '/config.php')) {
                    require_once __DIR__ . '/config.php';
                } else {
                    error_log('Arquivo de configuração não encontrado');
                    return null;
                }
            }
            
            // Criar uma nova conexão PDO
            try {
                $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $conn = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
            } catch (PDOException $e) {
                error_log('Falha ao conectar ao banco de dados: ' . $e->getMessage());
                return null;
            }
        }
        
        // Fazer a consulta com PDO
        $sql_foto = "SELECT imagem_base64 FROM fotos_perfil WHERE id_usuario = :id_usuario";
        $stmt_foto = $conn->prepare($sql_foto);
        $stmt_foto->bindParam(':id_usuario', $usuario_id, PDO::PARAM_INT);
        $stmt_foto->execute();
        
        if ($row = $stmt_foto->fetch(PDO::FETCH_ASSOC)) {
            $foto_perfil = $row['imagem_base64'];
        }
        
    } catch (Exception $e) {
        error_log('Erro ao obter foto de perfil: ' . $e->getMessage());
    }
    
    return $foto_perfil;
}