<?php
// Iniciar a sessão para poder acessar as variáveis de sessão
session_start();

// Define caminhos absolutos para o projeto
define('SITE_ROOT', dirname(dirname(__FILE__)));
define('BACKEND_PATH', SITE_ROOT . '/backend/');
define('PAGES_PATH', SITE_ROOT . '/PAGES/');
define('INCLUDES_PATH', PAGES_PATH . 'includes/');
define('ASSETS_PATH', SITE_ROOT . '/assets/');

// Define URLs base
$current_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$current_url .= $_SERVER['HTTP_HOST'];
$current_url .= $_SERVER['REQUEST_URI'];
$base_url = dirname(dirname($current_url));

define('BASE_URL', $base_url);
define('ASSETS_URL', BASE_URL . '/assets/');
define('PAGES_URL', BASE_URL . '/PAGES/');

// Incluir arquivo de configuração para conexão com o banco de dados
require_once BACKEND_PATH . 'config.php';

// Função para resolver caminhos relativos e absolutos
function get_path($relative_path) {
    if (strpos($relative_path, '../') === 0) {
        return SITE_ROOT . '/' . substr($relative_path, 3);
    }
    return $relative_path;
}

// Função para resolver URLs
function get_url($path) {
    if (strpos($path, '../') === 0) {
        return BASE_URL . '/' . substr($path, 3);
    }
    return $path;
}
?><?php
// Incluir arquivo com funções do cabeçalho
require_once INCLUDES_PATH . 'cabecalho_helper.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EntreLinhas - Jornal Digital (Solução)</title>
    <meta name="description" content="EntreLinhas - Jornal digital colaborativo com notícias, artigos e textos da comunidade.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>images/jornal.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
    <style>
        .debug-info {
            margin: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .path-display {
            margin: 10px 0;
            font-family: monospace;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <h1>EntreLinhas - Solução de Caminhos</h1>
                <p>Esta página demonstra uma solução para o problema de caminhos relativos no projeto EntreLinhas.</p>
            </div>
        </div>
        
        <div class="debug-info">
            <h3>Informações de Depuração</h3>
            <div class="path-display">
                <strong>SITE_ROOT:</strong> <?php echo SITE_ROOT; ?>
            </div>
            <div class="path-display">
                <strong>BACKEND_PATH:</strong> <?php echo BACKEND_PATH; ?>
                <?php if (file_exists(BACKEND_PATH . 'config.php')) echo '<span class="success">(Válido)</span>'; else echo '<span class="error">(Inválido)</span>'; ?>
            </div>
            <div class="path-display">
                <strong>PAGES_PATH:</strong> <?php echo PAGES_PATH; ?>
                <?php if (file_exists(PAGES_PATH)) echo '<span class="success">(Válido)</span>'; else echo '<span class="error">(Inválido)</span>'; ?>
            </div>
            <div class="path-display">
                <strong>INCLUDES_PATH:</strong> <?php echo INCLUDES_PATH; ?>
                <?php if (file_exists(INCLUDES_PATH)) echo '<span class="success">(Válido)</span>'; else echo '<span class="error">(Inválido)</span>'; ?>
            </div>
            <div class="path-display">
                <strong>ASSETS_PATH:</strong> <?php echo ASSETS_PATH; ?>
                <?php if (file_exists(ASSETS_PATH)) echo '<span class="success">(Válido)</span>'; else echo '<span class="error">(Inválido)</span>'; ?>
            </div>
            <div class="path-display">
                <strong>BASE_URL:</strong> <?php echo BASE_URL; ?>
            </div>
            <div class="path-display">
                <strong>ASSETS_URL:</strong> <?php echo ASSETS_URL; ?>
            </div>
            <div class="path-display">
                <strong>PAGES_URL:</strong> <?php echo PAGES_URL; ?>
            </div>
            
            <h4 class="mt-3">Teste de Conexão</h4>
            <div class="path-display">
                <?php
                if (isset($conn) && !mysqli_connect_errno()) {
                    echo '<span class="success">Conexão com o banco de dados bem-sucedida!</span>';
                } else {
                    echo '<span class="error">Falha na conexão com o banco de dados: ' . mysqli_connect_error() . '</span>';
                }
                ?>
            </div>
        </div>
        
        <div class="mt-4">
            <h3>Próximos Passos</h3>
            <p>Para corrigir os problemas de caminho no projeto EntreLinhas:</p>
            <ol>
                <li>Copie estas definições de caminho para o início do seu arquivo index.php original</li>
                <li>Substitua todos os caminhos relativos (como "../backend/config.php") pelas constantes definidas (como BACKEND_PATH . 'config.php')</li>
                <li>Use as constantes de URL para links e recursos (CSS, JS, imagens)</li>
            </ol>
            <a href="../index.php" class="btn btn-primary">Voltar para a Página Inicial</a>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
