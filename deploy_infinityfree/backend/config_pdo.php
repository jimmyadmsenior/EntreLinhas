<?php
// Arquivo de configuração do banco de dados para InfinityFree usando PDO
// IMPORTANTE: Atualize esses valores com suas próprias credenciais do InfinityFree
define('DB_SERVER', 'sql302.infinityfree.com'); // Servidor MySQL do InfinityFree
define('DB_USERNAME', 'if0_39798697'); // Nome de usuário do banco de dados no InfinityFree
define('DB_PASSWORD', 'xKIcJzBS13BB50t'); // Senha do banco de dados no InfinityFree
define('DB_NAME', 'if0_39798697_entrelinhas'); // Nome do banco de dados no InfinityFree

// Email do administrador que receberá as notificações
define('ADMIN_EMAIL', 'jimmycastilho555@gmail.com');

// Tentativa de conexão com o banco de dados usando PDO
try {
    $pdo = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME.";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    
    // Configurar PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar para retornar resultados como arrays associativos por padrão
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Para compatibilidade com código existente
    $conn = $pdo;
    
} catch (PDOException $e) {
    die("ERRO: Não foi possível conectar ao banco de dados. " . $e->getMessage());
}

// Definir URL base do site (importante para links absolutos)
define('BASE_URL', 'https://entrelinhas.infinityfreeapp.com'); // Substitua pelo seu subdomínio
?>
