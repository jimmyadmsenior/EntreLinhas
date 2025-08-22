<?php
// Criar um helper de usuário para recuperar a foto de perfil
// Este arquivo será incluído em todas as páginas que precisam exibir a foto de perfil do usuário

/**
 * Obtém a foto de perfil do usuário
 * 
 * @param mysqli $conn A conexão com o banco de dados
 * @param int $usuario_id O ID do usuário
 * @return string|null A imagem em base64 ou null se não existir
 */
function obter_foto_perfil($conn, $usuario_id) {
    $foto_perfil = null;
    $sql_foto = "SELECT imagem_base64 FROM fotos_perfil WHERE id_usuario = ?";
    
    if ($stmt_foto = mysqli_prepare($conn, $sql_foto)) {
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
?>
