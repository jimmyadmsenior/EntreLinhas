<?php
// Arquivo de configuração do banco de dados usando PDO para InfinityFree
define('DB_SERVER', 'localhost'); // Importante: Use 'localhost' quando estiver no servidor
define('DB_USERNAME', 'if0_39798697');  
define('DB_PASSWORD', 'xKIcJzBS13BB50t');      
define('DB_NAME', 'if0_39798697_entrelinhas');

// Email do administrador que receberá as notificações
define('ADMIN_EMAIL', 'jimmycastilho555@gmail.com');

// Tentativa de conexão com o banco de dados usando PDO
try {
    $pdo = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME.";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    
    // Configurar PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar para retornar resultados como arrays associativos
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("ERRO: Não foi possível conectar ao banco de dados. " . $e->getMessage());
}

// Para compatibilidade com código existente (opcional)
$conn = $pdo; // Permite usar $conn nas partes do código que esperam essa variável
?>
