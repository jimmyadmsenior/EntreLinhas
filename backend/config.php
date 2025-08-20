<?php
// Arquivo de configuração do banco de dados
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'seu_usuario_mysql');
define('DB_PASSWORD', 'sua_senha_mysql');
define('DB_NAME', 'entrelinhas_db');

// Email do administrador que receberá as notificações
define('ADMIN_EMAIL', 'jimmycastilho555@gmail.com');

// Tentativa de conexão com o banco de dados MySQL
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Verificar conexão
if (!$conn) {
    die("ERRO: Não foi possível conectar ao MySQL. " . mysqli_connect_error());
}

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
        id_autor INT NOT NULL,
        data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_publicacao TIMESTAMP NULL,
        status ENUM('pendente', 'aprovado', 'rejeitado', 'rascunho') DEFAULT 'pendente',
        FOREIGN KEY (id_autor) REFERENCES usuarios(id)
    )";
    
    if (!mysqli_query($conn, $sql_articles)) {
        echo "ERRO: Não foi possível criar a tabela de artigos. " . mysqli_error($conn);
    }
} else {
    echo "ERRO: Não foi possível criar o banco de dados. " . mysqli_error($conn);
}

// Retornar a conexão
return $conn;
?>
