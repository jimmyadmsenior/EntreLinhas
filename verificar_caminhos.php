<?php
// Script para verificar caminhos de inclusão de arquivos
echo "<h1>Verificação de Caminhos</h1>";

// Diretório atual
echo "<h2>Diretório Atual</h2>";
echo "Diretório atual: " . __DIR__ . "<br>";
echo "Arquivo atual: " . __FILE__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Verificar backend/session_helper.php
$session_helper_path = __DIR__ . "/backend/session_helper.php";
echo "<h2>Verificação de backend/session_helper.php</h2>";
echo "Caminho: $session_helper_path<br>";
echo "Existe: " . (file_exists($session_helper_path) ? "Sim" : "Não") . "<br>";

// Verificar backend/session.php
$session_path = __DIR__ . "/backend/session.php";
echo "<h2>Verificação de backend/session.php</h2>";
echo "Caminho: $session_path<br>";
echo "Existe: " . (file_exists($session_path) ? "Sim" : "Não") . "<br>";

// Verificar backend/config.php
$config_path = __DIR__ . "/backend/config.php";
echo "<h2>Verificação de backend/config.php</h2>";
echo "Caminho: $config_path<br>";
echo "Existe: " . (file_exists($config_path) ? "Sim" : "Não") . "<br>";

// Testar caminhos alternativos
$alt_session_helper_path = dirname(__DIR__) . "/backend/session_helper.php";
echo "<h2>Caminhos Alternativos</h2>";
echo "Caminho alternativo session_helper.php: $alt_session_helper_path<br>";
echo "Existe: " . (file_exists($alt_session_helper_path) ? "Sim" : "Não") . "<br>";

// Listar arquivos no diretório backend
echo "<h2>Arquivos no diretório backend</h2>";
$backend_dir = __DIR__ . "/backend";
if (is_dir($backend_dir)) {
    $files = scandir($backend_dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "O diretório backend não foi encontrado em $backend_dir<br>";
    
    // Tentar encontrar em outros locais
    $alt_backend_dir = dirname(__DIR__) . "/backend";
    echo "Tentando caminho alternativo: $alt_backend_dir<br>";
    if (is_dir($alt_backend_dir)) {
        $files = scandir($alt_backend_dir);
        echo "<ul>";
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                echo "<li>$file</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "O diretório backend também não foi encontrado em $alt_backend_dir<br>";
    }
}

// Verificar include_path
echo "<h2>Include Path</h2>";
echo "PHP include_path: " . get_include_path() . "<br>";

// Recomendações
echo "<h2>Recomendações</h2>";
echo "1. Certifique-se de que os caminhos nos requires estão corretos.<br>";
echo "2. Verifique se há conflitos entre session.php e session_helper.php.<br>";
echo "3. Certifique-se de que session.php inclui session_helper.php corretamente.<br>";
?>
