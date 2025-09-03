<?php
// Arquivo de teste para caminhos
echo "<h1>Verificação de Caminhos</h1>";

// Diretório atual
echo "<p>Diretório atual: " . __DIR__ . "</p>";

// Documento raiz
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// URI atual
echo "<p>URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Tentar carregar o arquivo config.php
$configPath = dirname(__DIR__) . '/backend/config.php';
echo "<p>Tentando carregar: " . $configPath . "</p>";
if (file_exists($configPath)) {
    echo "<p style='color:green'>✅ O arquivo config.php existe!</p>";
    
    // Incluir o arquivo sem executá-lo
    $configContent = file_get_contents($configPath);
    echo "<p>Primeiras 100 caracteres do arquivo:</p>";
    echo "<pre>" . htmlspecialchars(substr($configContent, 0, 100)) . "...</pre>";
} else {
    echo "<p style='color:red'>❌ O arquivo config.php não existe!</p>";
}

// Verificar se está sendo acessado pelo XAMPP
echo "<p>Server Name: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>PHP_SELF: " . $_SERVER['PHP_SELF'] . "</p>";

// Definir caminho para a pasta do projeto
echo "<p>Pasta do Projeto: " . dirname(dirname(__FILE__)) . "</p>";

// Listar os arquivos do diretório backend
echo "<h2>Arquivos no diretório backend:</h2>";
$backendDir = dirname(__DIR__) . '/backend';
if (is_dir($backendDir)) {
    echo "<ul>";
    $files = scandir($backendDir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo "<li>" . $file . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>❌ O diretório backend não existe!</p>";
}
?>
