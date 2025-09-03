<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Verificar se o ID do usuário foi especificado
if (!isset($_GET["id"])) {
    header("location: index.html");
    exit;
}

$usuario_id = intval($_GET["id"]);

// Buscar foto de perfil do usuário
$foto_perfil = null;
$sql_foto = "SELECT imagem_base64 FROM fotos_perfil WHERE id_usuario = ?";
if ($stmt_foto = mysqli_prepare($conn, $sql_foto)) {
    mysqli_stmt_bind_param($stmt_foto, "i", $usuario_id);
    mysqli_stmt_execute($stmt_foto);
    mysqli_stmt_bind_result($stmt_foto, $imagem_base64);
    if (mysqli_stmt_fetch($stmt_foto)) {
        $foto_perfil = $imagem_base64;
        
        // Definir cabeçalhos corretos para a imagem (detectar o tipo da imagem base64)
        $tipo_imagem = "image/jpeg"; // padrão
        
        if (strpos($foto_perfil, "data:image/png;base64,") !== false) {
            $tipo_imagem = "image/png";
        } elseif (strpos($foto_perfil, "data:image/gif;base64,") !== false) {
            $tipo_imagem = "image/gif";
        } elseif (strpos($foto_perfil, "data:image/jpeg;base64,") !== false) {
            $tipo_imagem = "image/jpeg";
        }
        
        // Extrair a parte base64 real (remover o prefixo data:image/xxx;base64,)
        $base64_puro = explode(",", $foto_perfil)[1];
        
        // Decodificar a string base64
        $imagem_decodificada = base64_decode($base64_puro);
        
        // Enviar cabeçalhos HTTP
        header("Content-Type: " . $tipo_imagem);
        header("Content-Length: " . strlen($imagem_decodificada));
        header("Cache-Control: max-age=2592000"); // cache por 30 dias
        
        // Exibir a imagem
        echo $imagem_decodificada;
    } else {
        // Usuário não tem foto de perfil, mostrar imagem padrão
        header("location: ../assets/images/default-profile.png");
    }
    mysqli_stmt_close($stmt_foto);
} else {
    // Erro na consulta
    header("location: ../assets/images/default-profile.png");
}

// Fechar conexão
mysqli_close($conn);
?>
