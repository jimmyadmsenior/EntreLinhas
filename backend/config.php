<?php
// Arquivo de configuração do banco de dados para InfinityFree
define('DB_SERVER', 'localhost'); // Importante: Use 'localhost' quando estiver no servidor InfinityFree
define('DB_USERNAME', 'if0_39798697');  // Usuário do banco de dados InfinityFree
define('DB_PASSWORD', 'xKIcJzBS13BB50t');  // Senha do banco de dados InfinityFree
define('DB_NAME', 'if0_39798697_entrelinhas');  // Nome do banco de dados InfinityFree

// Email do administrador que receberá as notificações
define('ADMIN_EMAIL', 'jimmycastilho555@gmail.com');

// Tentativa de conexão com o banco de dados MySQL
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if (!$conn) {
    die("ERRO: Não foi possível conectar ao MySQL. " . mysqli_connect_error());
}

// Configurar charset para UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Criar o banco de dados se não existir
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (mysqli_query($conn, $sql)) {
    // Selecionar o banco de dados
    mysqli_select_db($conn, DB_NAME);
    
    // Criar tabela de usuários se não existir
    $sql_users = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reset_token VARCHAR(64) DEFAULT NULL,
        reset_expiry DATETIME DEFAULT NULL,
        ativo BOOLEAN DEFAULT TRUE
    )";
    
    if (!mysqli_query($conn, $sql_users)) {
        echo "ERRO: Não foi possível criar a tabela de usuários. " . mysqli_error($conn);
    }
    
    // Criar tabela de artigos se não existir
    $sql_articles = "CREATE TABLE IF NOT EXISTS artigos (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        titulo VARCHAR(255) NOT NULL,
        conteudo TEXT NOT NULL,
        categoria VARCHAR(50) NOT NULL,
        imagem VARCHAR(255),
        id_usuario INT NOT NULL,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_publicacao TIMESTAMP NULL,
        status ENUM('pendente', 'aprovado', 'rejeitado', 'rascunho') DEFAULT 'pendente',
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $sql_articles)) {
        echo "ERRO: Não foi possível criar a tabela de artigos. " . mysqli_error($conn);
    }
    
    // Criar tabela de comentários se não existir
    $sql_comments = "CREATE TABLE IF NOT EXISTS comentarios (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        id_artigo INT NOT NULL,
        id_usuario INT NOT NULL,
        comentario TEXT NOT NULL,
        data_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'aprovado',
        FOREIGN KEY (id_artigo) REFERENCES artigos(id) ON DELETE CASCADE,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $sql_comments)) {
        echo "ERRO: Não foi possível criar a tabela de comentários. " . mysqli_error($conn);
    }
    
    // Criar tabela de imagens de artigos se não existir
    $sql_images = "CREATE TABLE IF NOT EXISTS imagens_artigos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        artigo_id INT NOT NULL,
        caminho VARCHAR(255) NOT NULL,
        ordem INT DEFAULT 0,
        descricao TEXT,
        data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (artigo_id) REFERENCES artigos(id) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $sql_images)) {
        echo "ERRO: Não foi possível criar a tabela de imagens de artigos. " . mysqli_error($conn);
    }
} else {
    echo "ERRO: Não foi possível criar o banco de dados. " . mysqli_error($conn);
}
?>
