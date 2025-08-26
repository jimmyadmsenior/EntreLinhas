<?php
// Arquivo para diagnosticar os erros ao tentar enviar artigos

// Exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Erros - Envio de Artigos</h1>";

// Verificar versão do PHP
echo "<h2>1. Ambiente PHP</h2>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";

// Verificar extensões necessárias
echo "<h2>2. Extensões PHP</h2>";
$required_extensions = ['mysqli', 'curl', 'session', 'fileinfo'];
foreach ($required_extensions as $ext) {
    echo "<p>Extensão $ext: " . (extension_loaded($ext) ? "✅ Carregada" : "❌ Não encontrada") . "</p>";
}

// Verificar diretórios importantes
echo "<h2>3. Diretórios</h2>";
$directories = [
    '../uploads/' => "Diretório de uploads",
    '../uploads/artigos/' => "Diretório de uploads de artigos",
    '../logs/' => "Diretório de logs"
];

foreach ($directories as $dir => $label) {
    $full_path = __DIR__ . '/' . $dir;
    echo "<p>$label ($full_path): ";
    if (is_dir($full_path)) {
        echo "✅ Existe";
        echo " | Permissão: " . substr(sprintf('%o', fileperms($full_path)), -4);
        echo " | Gravável: " . (is_writable($full_path) ? "Sim" : "Não");
    } else {
        echo "❌ Não existe";
        // Tentar criar o diretório
        if (mkdir($full_path, 0755, true)) {
            echo " → ✅ Criado agora";
        } else {
            echo " → ❌ Não foi possível criar";
        }
    }
    echo "</p>";
}

// Verificar arquivos importantes
echo "<h2>4. Verificação de Arquivos</h2>";
$files = [
    '../backend/config.php' => "Arquivo de configuração",
    '../backend/artigos.php' => "Processamento de artigos",
    '../backend/email_notification.php' => "Notificações por email",
    '../backend/email_integration.php' => "Integração de email",
    '../backend/sendgrid_email.php' => "SendGrid Email"
];

foreach ($files as $file => $label) {
    $full_path = __DIR__ . '/' . $file;
    echo "<p>$label ($full_path): ";
    if (file_exists($full_path)) {
        echo "✅ Existe";
        echo " | Tamanho: " . filesize($full_path) . " bytes";
    } else {
        echo "❌ Não existe";
    }
    echo "</p>";
}

// Simular um envio de formulário
echo "<h2>5. Simulação de Envio</h2>";
if (isset($_GET['simular'])) {
    try {
        echo "<p>Simulando envio de artigo...</p>";
        
        // Carregar classes necessárias
        require_once '../backend/config.php';
        require_once '../backend/artigos.php';
        
        // Simular dados de um artigo
        $artigo = [
            'titulo' => "Artigo de teste " . date('Y-m-d H:i:s'),
            'conteudo' => "Este é um artigo de teste criado para diagnóstico.",
            'categoria' => "Tecnologia",
            'id_usuario' => 1, // Ajuste conforme necessário
            'imagem' => ""
        ];
        
        // Chamar a função diretamente
        $resultado = enviarArtigo($conn, $artigo);
        
        // Exibir resultado
        echo "<pre>";
        print_r($resultado);
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Exceção: " . $e->getMessage() . "</p>";
        
        // Exibir stack trace
        echo "<pre>";
        print_r($e->getTrace());
        echo "</pre>";
    }
} else {
    echo "<p><a href='?simular=1'>Clique aqui para simular um envio de artigo</a></p>";
}

// Mostrar valores de sessão importantes
echo "<h2>6. Variáveis de Sessão</h2>";
session_start();
echo "<pre>";
// Exibir apenas variáveis relevantes
$session_vars = [
    'id' => isset($_SESSION['id']) ? $_SESSION['id'] : 'Não definido',
    'nome' => isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Não definido',
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : 'Não definido',
    'tipo' => isset($_SESSION['tipo']) ? $_SESSION['tipo'] : 'Não definido',
    'loggedin' => isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : 'Não definido',
    'mensagem' => isset($_SESSION['mensagem']) ? $_SESSION['mensagem'] : 'Não definido',
    'tipo_mensagem' => isset($_SESSION['tipo_mensagem']) ? $_SESSION['tipo_mensagem'] : 'Não definido'
];
print_r($session_vars);
echo "</pre>";
?>
