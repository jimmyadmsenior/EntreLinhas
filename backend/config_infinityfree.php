<?php
// Arquivo de configuração do banco de dados para InfinityFree
// Configurado com as credenciais atualizadas
define('DB_SERVER', 'sql302.infinityfree.com'); // Servidor SQL atualizado
define('DB_USERNAME', 'if0_39798697'); // Nome de usuário atualizado
define('DB_PASSWORD', 'jimmysenai123'); // Senha atualizada
define('DB_NAME', 'if0_39798697_entrelinhas'); // Nome do banco atualizado

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

// Definir URL base do site (importante para links absolutos)
define('BASE_URL', 'https://entrelinhas.infinityfreeapp.com'); // Substitua pelo seu subdomínio
?>
