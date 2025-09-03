<?php
/**
 * Configuração Universal do Banco de Dados
 * 
 * Este arquivo detecta automaticamente o ambiente e carrega as credenciais corretas
 * para o banco de dados. Ele funciona tanto em ambiente local quanto no InfinityFree.
 */

// Credenciais do InfinityFree (produção)
$infinity_config = [
    'host' => 'sql302.infinityfree.com', // Host correto do InfinityFree
    'username' => 'if0_39798697',
    'password' => 'jimmysenai123',
    'dbname' => 'if0_39798697_entrelinhas',
    'charset' => 'utf8mb4'
];

// Credenciais locais (desenvolvimento)
$local_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'entrelinhas',
    'charset' => 'utf8mb4'
];

/**
 * Detecta se estamos em ambiente de produção (InfinityFree) ou local
 */
function is_production_environment() {
    // Verifica por nomes de domínio típicos do InfinityFree
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($host, '.infinityfreeapp.com') !== false || 
            strpos($host, '.rf.gd') !== false ||
            strpos($host, '.epizy.com') !== false) {
            return true;
        }
    }
    
    // Verifica se estamos em localhost ou IP local
    if (!isset($_SERVER['SERVER_NAME']) || 
        $_SERVER['SERVER_NAME'] == 'localhost' || 
        $_SERVER['SERVER_NAME'] == '127.0.0.1' ||
        strpos($_SERVER['SERVER_NAME'], '192.168.') === 0) {
        return false;
    }
    
    // Caso especial: verificar se o ambiente atual é o InfinityFree pelo path
    $path = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (strpos($path, '/htdocs') !== false || strpos($path, '/storage/ssd') !== false) {
        return true;
    }
    
    // Se não tivermos certeza, assumimos que é produção (mais seguro)
    return false;
}

// Escolhe a configuração apropriada
$db_config = is_production_environment() ? $infinity_config : $local_config;

// Define constantes globais para compatibilidade com código existente
define('DB_SERVER', $db_config['host']);
define('DB_USERNAME', $db_config['username']);
define('DB_PASSWORD', $db_config['password']);
define('DB_NAME', $db_config['dbname']);
define('DB_CHARSET', $db_config['charset']);

// Define o modo de produção
define('IS_PRODUCTION', is_production_environment());

// Email do administrador que receberá as notificações
define('ADMIN_EMAIL', 'jimmycastilho555@gmail.com');

/**
 * Cria uma conexão PDO com o banco de dados
 */
function get_pdo_connection() {
    static $pdo = null;
    
    // Retorna conexão existente se estiver ativa
    if ($pdo !== null) {
        try {
            $pdo->query('SELECT 1');
            return $pdo;
        } catch (PDOException $e) {
            // Conexão expirou, criar uma nova
            $pdo = null;
        }
    }
    
    try {
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Em produção, registra o erro mas exibe mensagem genérica
        if (IS_PRODUCTION) {
            error_log("Erro de conexão PDO: " . $e->getMessage());
            die("Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.");
        }
        // Em desenvolvimento, mostra o erro completo
        else {
            die("Erro PDO: " . $e->getMessage());
        }
    }
}

// Cria a conexão global PDO
$conn = get_pdo_connection();
?>
