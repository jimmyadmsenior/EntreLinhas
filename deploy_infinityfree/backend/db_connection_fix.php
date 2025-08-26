<?php
// Este arquivo garante que as conexões com o banco de dados sejam gerenciadas corretamente
// Ele detecta e resolve o problema de "mysqli object already closed"

// Verificar se as constantes de banco de dados existem
if (!defined('DB_SERVER') || !defined('DB_USERNAME') || !defined('DB_PASSWORD') || !defined('DB_NAME')) {
    // Se não existirem, definir com valores padrão
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'entrelinhas');
}

// Função para criar uma nova conexão com o banco de dados
function get_db_connection() {
    static $conn = null;
    
    // Se a conexão já existe e está ativa, reutilizar
    if ($conn !== null && $conn instanceof mysqli && !$conn->connect_errno) {
        return $conn;
    }
    
    // Criar uma nova conexão
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Verificar conexão
    if (!$conn) {
        die("ERRO: Não foi possível conectar ao MySQL. " . mysqli_connect_error());
    }
    
    // Configurar charset para UTF-8
    mysqli_set_charset($conn, "utf8mb4");
    
    return $conn;
}

// Modificar a função obter_foto_perfil para usar a função get_db_connection
if (!function_exists('obter_foto_perfil_safe')) {
    function obter_foto_perfil_safe($conn, $usuario_id) {
        // Sempre usar uma conexão segura
        $local_conn = get_db_connection();
        
        $foto_perfil = null;
        
        $sql_foto = "SELECT imagem_base64 FROM fotos_perfil WHERE id_usuario = ?";
        
        if ($stmt_foto = mysqli_prepare($local_conn, $sql_foto)) {
            mysqli_stmt_bind_param($stmt_foto, "i", $usuario_id);
            mysqli_stmt_execute($stmt_foto);
            mysqli_stmt_bind_result($stmt_foto, $imagem_base64);
            
            if (mysqli_stmt_fetch($stmt_foto)) {
                $foto_perfil = $imagem_base64;
            }
            
            mysqli_stmt_close($stmt_foto);
        }
        
        return $foto_perfil;
    }
}

// Substituir a função original pela versão segura
if (function_exists('obter_foto_perfil')) {
    // Renomear a função original para evitar conflitos
    function obter_foto_perfil_original($conn, $usuario_id) {
        return obter_foto_perfil($conn, $usuario_id);
    }
    
    // Redefinir a função para usar a versão segura
    function obter_foto_perfil($conn, $usuario_id) {
        return obter_foto_perfil_safe($conn, $usuario_id);
    }
}

// Usar esta variável para armazenar a conexão global
$GLOBALS['conn'] = get_db_connection();

// Se a variável $conn foi definida em outro lugar e está fechada, substituí-la
if (isset($conn) && is_object($conn) && method_exists($conn, 'ping') && !@$conn->ping()) {
    $conn = $GLOBALS['conn'];
}
?>
