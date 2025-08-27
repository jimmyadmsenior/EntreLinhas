<?php
// Arquivo de configuração do banco de dados para InfinityFree
define('DB_SERVER', 'localhost'); // Importante: Use 'localhost' quando estiver no servidor
define('DB_USERNAME', 'if0_39798697');  
define('DB_PASSWORD', 'xKIcJzBS13BB50t');      
define('DB_NAME', 'if0_39798697_entrelinhas');

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
?>
