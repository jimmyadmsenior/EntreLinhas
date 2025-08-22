<?php
// Arquivo para configuração do banco de dados
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'entrelinhas';

// Tentar estabelecer conexão com o servidor MySQL
$conn = new mysqli($host, $user, $password);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
echo "<h2>✅ Conexão com o servidor MySQL estabelecida com sucesso!</h2>";

// Criar banco de dados se não existir
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Banco de dados '$dbname' criado ou já existente</p>";
} else {
    die("Erro ao criar o banco de dados: " . $conn->error);
}

// Selecionar o banco de dados
$conn->select_db($dbname);

// Criar tabelas
$tables = [
    "usuarios" => "CREATE TABLE IF NOT EXISTS usuarios (
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
    )",
    
    "artigos" => "CREATE TABLE IF NOT EXISTS artigos (
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
    )",
    
    "comentarios" => "CREATE TABLE IF NOT EXISTS comentarios (
        id INT PRIMARY KEY AUTO_INCREMENT,
        artigo_id INT NOT NULL,
        usuario_id INT NOT NULL,
        conteudo TEXT NOT NULL,
        status ENUM('pendente', 'aprovado', 'rejeitado') NOT NULL DEFAULT 'pendente',
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (artigo_id) REFERENCES artigos(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )",
    
    "imagens_artigos" => "CREATE TABLE IF NOT EXISTS imagens_artigos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        artigo_id INT NOT NULL,
        nome_arquivo VARCHAR(255) NOT NULL,
        data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (artigo_id) REFERENCES artigos(id) ON DELETE CASCADE
    )"
];

echo "<h3>Status das Tabelas:</h3>";
echo "<ul>";
foreach ($tables as $tablename => $query) {
    if ($conn->query($query) === TRUE) {
        echo "<li>✅ Tabela '$tablename' criada ou já existente</li>";
    } else {
        echo "<li>❌ Erro ao criar a tabela '$tablename': " . $conn->error . "</li>";
    }
}
echo "</ul>";

// Inserir usuário administrador se não existir
$admin_email = 'jimmycastilho555@gmail.com';
$admin_name = 'Administrador';
// Senha: Admin@123 (já com hash)
$admin_password = '$2y$10$uJRfPaOfDvHWQBx14oj.wOZA4ZRAVa6vsZ2qixG0xHzK0p6SjaxSq';

// Verificar se o administrador já existe
$check_admin = "SELECT * FROM usuarios WHERE email = '$admin_email'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    // Inserir o administrador se não existir
    $insert_admin = "INSERT INTO usuarios (nome, email, senha, tipo, status) 
                     VALUES ('$admin_name', '$admin_email', '$admin_password', 'admin', 'ativo')";
    
    if ($conn->query($insert_admin) === TRUE) {
        echo "<p>✅ Usuário administrador criado com sucesso!</p>";
        echo "<p>Email: $admin_email</p>";
        echo "<p>Senha: Admin@123</p>";
    } else {
        echo "<p>❌ Erro ao criar o usuário administrador: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ Usuário administrador já existe.</p>";
    echo "<p>Email: $admin_email</p>";
    echo "<p>Senha: Admin@123 (se não foi alterada)</p>";
}

echo "<h3>Configuração concluída!</h3>";
echo "<p>Agora você pode:</p>";
echo "<ol>";
echo "<li>Fazer <a href='/EntreLinhas/PAGES/login.html'>login</a> como administrador</li>";
echo "<li>Gerenciar artigos e usuários</li>";
echo "<li>Começar a usar o sistema EntreLinhas</li>";
echo "</ol>";

// Fechar conexão
$conn->close();
?>
