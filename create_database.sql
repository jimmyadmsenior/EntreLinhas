-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS entrelinhas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE entrelinhas;

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'professor', 'admin') NOT NULL DEFAULT 'aluno',
    status ENUM('pendente', 'ativo', 'inativo') NOT NULL DEFAULT 'pendente',
    token_recuperacao VARCHAR(255) DEFAULT NULL,
    expiracao_token DATETIME DEFAULT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criar tabela de artigos
CREATE TABLE IF NOT EXISTS artigos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    resumo TEXT NOT NULL,
    usuario_id INT NOT NULL,
    status ENUM('pendente', 'aprovado', 'rejeitado') NOT NULL DEFAULT 'pendente',
    destaque BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Criar tabela de comentários
CREATE TABLE IF NOT EXISTS comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    artigo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    conteudo TEXT NOT NULL,
    status ENUM('pendente', 'aprovado', 'rejeitado') NOT NULL DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artigo_id) REFERENCES artigos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Criar tabela para imagens dos artigos
CREATE TABLE IF NOT EXISTS imagens_artigos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    artigo_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artigo_id) REFERENCES artigos(id) ON DELETE CASCADE
);

-- Criar um usuário administrador padrão (senha: Admin@123)
INSERT INTO usuarios (nome, email, senha, tipo, status) 
VALUES ('Administrador', 'jimmycastilho555@gmail.com', '$2y$10$uJRfPaOfDvHWQBx14oj.wOZA4ZRAVa6vsZ2qixG0xHzK0p6SjaxSq', 'admin', 'ativo')
ON DUPLICATE KEY UPDATE status = 'ativo', tipo = 'admin';
http://localhost/EntreLinhas/backend/teste_db.php