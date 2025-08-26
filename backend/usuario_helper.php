<?php
// Criar um helper de usuário para recuperar a foto de perfil
// Este arquivo será incluído em todas as páginas que precisam exibir a foto de perfil do usuário

/**
 * Obtém a foto de perfil do usuário - FUNÇÃO CORRIGIDA
 * 
 * @param mysqli $conn A conexão com o banco de dados (ignorada, será criada uma nova)
 * @param int $usuario_id O ID do usuário
 * @return string|null A imagem em base64 ou null se não existir
 */
function obter_foto_perfil($conn, $usuario_id) {
    $foto_perfil = null;
    
    // SEMPRE criar uma nova conexão para evitar problemas com conexões fechadas
    $new_conn = null;
    
    try {
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
        
        // Criar uma nova conexão (ignorando a que foi passada)
        $new_conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if (!$new_conn) {
            error_log('Falha ao conectar ao banco de dados: ' . mysqli_connect_error());
            return null;
        }
        
        // Configurar charset
        mysqli_set_charset($new_conn, "utf8mb4");
        
        // Fazer a consulta com a nova conexão
        $sql_foto = "SELECT imagem_base64 FROM fotos_perfil WHERE id_usuario = ?";
        
        if ($stmt_foto = mysqli_prepare($new_conn, $sql_foto)) {
            mysqli_stmt_bind_param($stmt_foto, "i", $usuario_id);
            mysqli_stmt_execute($stmt_foto);
            mysqli_stmt_bind_result($stmt_foto, $imagem_base64);
            
            if (mysqli_stmt_fetch($stmt_foto)) {
                $foto_perfil = $imagem_base64;
            }
            
            mysqli_stmt_close($stmt_foto);
        }
    } catch (Exception $e) {
        error_log('Erro ao obter foto de perfil: ' . $e->getMessage());
    }
    
    // Sempre fechar a conexão que criamos
    if ($new_conn) {
        mysqli_close($new_conn);
    }
    
    return $foto_perfil;
}