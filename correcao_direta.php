<?php
// Correção direta para o erro "mysqli object is already closed"
// Este script modifica diretamente o arquivo usuario_helper.php

// Exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção de Erro - mysqli object already closed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        .code { background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; overflow-x: auto; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Correção para o erro "mysqli object is already closed"</h1>';

// Caminho para o arquivo usuario_helper.php
$helper_file = __DIR__ . '/backend/usuario_helper.php';

// Verificar se o arquivo existe
if (!file_exists($helper_file)) {
    echo '<div class="error">
        <p>O arquivo <code>backend/usuario_helper.php</code> não foi encontrado.</p>
        <p>Por favor, verifique o caminho do arquivo.</p>
    </div>';
    exit;
}

// Ler o conteúdo atual do arquivo
$content = file_get_contents($helper_file);

// Fazer backup do arquivo original
$backup_file = $helper_file . '.bak.' . date('YmdHis');
if (!copy($helper_file, $backup_file)) {
    echo '<div class="error">
        <p>Não foi possível fazer backup do arquivo. Verifique as permissões.</p>
    </div>';
    exit;
}

echo '<div class="alert alert-info">
    <p>Backup do arquivo original criado em: <code>' . basename($backup_file) . '</code></p>
</div>';

// Nova implementação da função obter_foto_perfil
$new_function = '<?php
// Criar um helper de usuário para recuperar a foto de perfil
// Este arquivo será incluído em todas as páginas que precisam exibir a foto de perfil do usuário

/**
 * Obtém a foto de perfil do usuário - FUNÇÃO CORRIGIDA
 * 
 * @param mysqli $conn A conexão com o banco de dados (ignorada, será criada uma nova)
 * @param int $usuario_id O ID do usuário
 * @return string|null A imagem em base64 ou null se não existir
 */
function obter_foto_perfil($conn, $usuario_id) {
    $foto_perfil = null;
    
    // SEMPRE criar uma nova conexão para evitar problemas com conexões fechadas
    $new_conn = null;
    
    try {
        // Verificar se temos as constantes definidas
        if (!defined(\'DB_SERVER\') || !defined(\'DB_USERNAME\') || !defined(\'DB_PASSWORD\') || !defined(\'DB_NAME\')) {
            // Tentar carregar o arquivo de configuração se não estiver carregado
            if (file_exists(__DIR__ . \'/config.php\')) {
                require_once __DIR__ . \'/config.php\';
            } else {
                error_log(\'Arquivo de configuração não encontrado\');
                return null;
            }
        }
        
        // Criar uma nova conexão (ignorando a que foi passada)
        $new_conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if (!$new_conn) {
            error_log(\'Falha ao conectar ao banco de dados: \' . mysqli_connect_error());
            return null;
        }
        
        // Configurar charset
        mysqli_set_charset($new_conn, "utf8mb4");
        
        // Fazer a consulta com a nova conexão
        $sql_foto = "SELECT imagem_base64 FROM fotos_perfil WHERE id_usuario = ?";
        
        if ($stmt_foto = mysqli_prepare($new_conn, $sql_foto)) {
            mysqli_stmt_bind_param($stmt_foto, "i", $usuario_id);
            mysqli_stmt_execute($stmt_foto);
            mysqli_stmt_bind_result($stmt_foto, $imagem_base64);
            
            if (mysqli_stmt_fetch($stmt_foto)) {
                $foto_perfil = $imagem_base64;
            }
            
            mysqli_stmt_close($stmt_foto);
        }
    } catch (Exception $e) {
        error_log(\'Erro ao obter foto de perfil: \' . $e->getMessage());
    }
    
    // Sempre fechar a conexão que criamos
    if ($new_conn) {
        mysqli_close($new_conn);
    }
    
    return $foto_perfil;
}';

// Salvar o novo conteúdo
if (file_put_contents($helper_file, $new_function)) {
    echo '<div class="success">
        <h2>Arquivo atualizado com sucesso!</h2>
        <p>O arquivo <code>usuario_helper.php</code> foi modificado com uma implementação mais segura da função <code>obter_foto_perfil</code>.</p>
        <p>A nova versão sempre cria uma nova conexão com o banco de dados e a fecha no final, evitando o erro "mysqli object is already closed".</p>
    </div>';
    
    echo '<div class="mt-4">
        <h3>Nova Implementação:</h3>
        <div class="code">' . htmlspecialchars($new_function) . '</div>
    </div>';
} else {
    echo '<div class="error">
        <p>Não foi possível atualizar o arquivo. Verifique as permissões.</p>
    </div>';
}

echo '<div class="mt-4">
    <a href="PAGES/enviar-artigo.php" class="btn btn-primary">Testar Página de Envio</a>
    <a href="index.php" class="btn btn-secondary ms-2">Voltar para a Página Inicial</a>
</div>

</div>
</body>
</html>';
?>
