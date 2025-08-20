<?php
// Arquivo de configuração do banco de dados
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');  // Altere para o seu usuário do MySQL
define('DB_PASSWORD', '');      // Altere para a sua senha do MySQL
define('DB_NAME', 'entrelinhas');

// Email do administrador que receberá as notificações
define('ADMIN_EMAIL', 'jimmycastilho555@gmail.com');

// Tentativa de conexão com o banco de dados MySQL
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

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
} else {
    echo "ERRO: Não foi possível criar o banco de dados. " . mysqli_error($conn);
}
?>
