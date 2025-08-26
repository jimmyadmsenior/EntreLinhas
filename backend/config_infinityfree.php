<?php
// Arquivo de configuração do banco de dados para InfinityFree
// IMPORTANTE: Atualize esses valores com suas próprias credenciais do InfinityFree
define('DB_SERVER', 'sql309.infinityfree.com'); // Substitua pelo servidor MySQL fornecido
define('DB_USERNAME', 'if0_35610247'); // Substitua pelo seu nome de usuário MySQL
define('DB_PASSWORD', 'sua_senha_aqui'); // Substitua pela sua senha MySQL
define('DB_NAME', 'if0_35610247_entrelinhas'); // Substitua pelo nome do seu banco de dados

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
