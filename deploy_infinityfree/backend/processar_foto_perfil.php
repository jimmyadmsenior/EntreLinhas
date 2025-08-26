<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../PAGES/login.php");
    exit;
}

// Incluir arquivo de configuração e funções Base64
require_once "config.php";
require_once "funcoes_base64.php";

// Verificar se foi enviado um arquivo
if (!isset($_FILES["foto_perfil"]) || $_FILES["foto_perfil"]["error"] == UPLOAD_ERR_NO_FILE) {
    $_SESSION["foto_erro"] = "Nenhuma imagem foi selecionada.";
    header("location: ../PAGES/perfil.php");
    exit;
}

// Verificar se o arquivo é uma imagem
$check = getimagesize($_FILES["foto_perfil"]["tmp_name"]);
if ($check === false) {
    $_SESSION["foto_erro"] = "O arquivo enviado não é uma imagem válida.";
    header("location: ../PAGES/perfil.php");
    exit;
}

// Verificar o tipo de arquivo
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES["foto_perfil"]["type"], $allowed_types)) {
    $_SESSION["foto_erro"] = "Apenas imagens JPG, PNG e GIF são permitidas.";
    header("location: ../PAGES/perfil.php");
    exit;
}

// Verificar o tamanho do arquivo (máximo 2MB)
if ($_FILES["foto_perfil"]["size"] > 2 * 1024 * 1024) {
    $_SESSION["foto_erro"] = "O arquivo é muito grande. O tamanho máximo permitido é 2MB.";
    header("location: ../PAGES/perfil.php");
    exit;
}

try {
    // Criar diretório temporário para salvar a imagem antes de converter para Base64
    $temp_dir = "../uploads/temp/";
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    // Mover o arquivo para o diretório temporário
    $temp_file = $temp_dir . uniqid() . "_" . basename($_FILES["foto_perfil"]["name"]);
    if (!move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $temp_file)) {
        throw new Exception("Erro ao fazer upload da imagem.");
    }
    
    // Converter a imagem para Base64
    $imagem_base64 = converter_imagem_para_base64($temp_file);
    if (!$imagem_base64) {
        throw new Exception("Erro ao converter a imagem para Base64.");
    }
    
    // Verificar se já existe uma imagem de perfil para o usuário
    $sql_check = "SELECT * FROM fotos_perfil WHERE id_usuario = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    
    // Guardar o resultado antes de fechar a declaração
    $tem_imagem = mysqli_stmt_num_rows($stmt_check) > 0;
    
    if ($tem_imagem) {
        // Atualizar a imagem existente
        $sql = "UPDATE fotos_perfil SET imagem_base64 = ?, data_atualizacao = NOW() WHERE id_usuario = ?";
    } else {
        // Inserir nova imagem
        $sql = "INSERT INTO fotos_perfil (id_usuario, imagem_base64, data_criacao) VALUES (?, ?, NOW())";
    }
    
    mysqli_stmt_close($stmt_check);
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        if ($tem_imagem) {
            mysqli_stmt_bind_param($stmt, "si", $imagem_base64, $_SESSION["id"]);
        } else {
            mysqli_stmt_bind_param($stmt, "is", $_SESSION["id"], $imagem_base64);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            // Remover arquivo temporário
            @unlink($temp_file);
            
            $_SESSION["foto_sucesso"] = "Foto de perfil atualizada com sucesso!";
            header("location: ../PAGES/perfil.php");
            exit;
        } else {
            throw new Exception("Erro ao salvar a imagem no banco de dados: " . mysqli_error($conn));
        }
    } else {
        throw new Exception("Erro ao preparar a consulta: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    // Remover arquivo temporário em caso de erro
    if (isset($temp_file) && file_exists($temp_file)) {
        @unlink($temp_file);
    }
    
    $_SESSION["foto_erro"] = $e->getMessage();
    header("location: ../PAGES/perfil.php");
    exit;
}

// Fechar conexão
mysqli_close($conn);
?>
