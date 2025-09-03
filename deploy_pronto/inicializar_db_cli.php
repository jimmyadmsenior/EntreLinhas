<?php
// Script de linha de comando para inicializar o banco de dados
echo "Inicializando banco de dados...\n";

// Incluir arquivo de configuração
require_once "backend/config.php";

// Verificar se o banco de dados está configurado corretamente
if ($conn) {
    echo "✅ Conexão com o banco de dados estabelecida com sucesso!\n";
    
    // Verificar se as tabelas existem
    $tabelas = ['usuarios', 'artigos', 'comentarios', 'imagens_artigos'];
    
    echo "Status das Tabelas:\n";
    
    foreach ($tabelas as $tabela) {
        $check_query = "SHOW TABLES LIKE '$tabela'";
        $result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($result) > 0) {
            echo "✅ Tabela '$tabela' já existe\n";
        } else {
            echo "❌ Tabela '$tabela' não encontrada\n";
            
            // Criar tabela conforme necessário
            switch ($tabela) {
                case 'usuarios':
                    $sql = "CREATE TABLE usuarios (
                        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                        nome VARCHAR(100) NOT NULL,
                        email VARCHAR(100) NOT NULL UNIQUE,
                        senha VARCHAR(255) NOT NULL,
                        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        reset_token VARCHAR(64) DEFAULT NULL,
                        reset_expiry DATETIME DEFAULT NULL,
                        ativo BOOLEAN DEFAULT TRUE
                    )";
                    break;
                case 'artigos':
                    $sql = "CREATE TABLE artigos (
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
                    break;
                case 'comentarios':
                    $sql = "CREATE TABLE comentarios (
                        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                        id_artigo INT NOT NULL,
                        id_usuario INT NOT NULL,
                        comentario TEXT NOT NULL,
                        data_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'aprovado',
                        FOREIGN KEY (id_artigo) REFERENCES artigos(id) ON DELETE CASCADE,
                        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
                    )";
                    break;
                case 'imagens_artigos':
                    $sql = "CREATE TABLE imagens_artigos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        artigo_id INT NOT NULL,
                        caminho VARCHAR(255) NOT NULL,
                        ordem INT DEFAULT 0,
                        descricao TEXT,
                        data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (artigo_id) REFERENCES artigos(id) ON DELETE CASCADE
                    )";
                    break;
            }
            
            if (mysqli_query($conn, $sql)) {
                echo "✅ Tabela '$tabela' criada com sucesso\n";
            } else {
                echo "❌ Erro ao criar tabela '$tabela': " . mysqli_error($conn) . "\n";
            }
        }
    }
    
    echo "\nConfigurações iniciais do banco de dados concluídas!\n";
} else {
    echo "❌ Falha na conexão com o banco de dados: " . mysqli_connect_error() . "\n";
}
?>
