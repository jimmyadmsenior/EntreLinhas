<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once "config.php";

// Criar tabela de fotos de perfil se não existir
$sql_fotos_perfil = "CREATE TABLE IF NOT EXISTS fotos_perfil (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    imagem_base64 LONGTEXT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql_fotos_perfil)) {
    die("Erro ao criar tabela de fotos de perfil: " . mysqli_error($conn));
}

// Fechar conexão
mysqli_close($conn);

// Redirecionar sem saída anterior
header("location: ../PAGES/admin.php");
exit;
?>
