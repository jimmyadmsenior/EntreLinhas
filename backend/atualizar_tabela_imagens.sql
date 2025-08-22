-- Verificar se a tabela de imagens existe e criá-la se não existir
CREATE TABLE IF NOT EXISTS imagens_artigos (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    imagem_base64 MEDIUMTEXT NOT NULL, -- Pode armazenar até 16MB de texto (adequado para Base64)
    tipo_mime VARCHAR(50) NOT NULL,
    data_upload DATETIME NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índices para melhorar a performance
CREATE INDEX IF NOT EXISTS idx_imagens_usuario ON imagens_artigos(usuario_id);
