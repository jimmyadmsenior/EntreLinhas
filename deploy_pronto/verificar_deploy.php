<?php
/**
 * Verificação pré-deploy para o EntreLinhas
 * Este script verifica se o site está pronto para deploy
 */

echo "=================================================\n";
echo "     VERIFICAÇÃO PRÉ-DEPLOY DO ENTRELINHAS       \n";
echo "=================================================\n\n";

// Verificar PHP e extensões necessárias
echo "## Verificando PHP e extensões\n";
echo "PHP versão: " . phpversion() . "\n";

$required_extensions = ['mysqli', 'curl', 'mbstring', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ Extensão {$ext}: Instalada\n";
    } else {
        echo "✗ ERRO: Extensão {$ext} não está instalada!\n";
    }
}
echo "\n";

// Verificar configuração do banco de dados
echo "## Verificando configuração do banco de dados\n";
if (file_exists(__DIR__ . '/backend/config.php')) {
    echo "✓ Arquivo config.php encontrado\n";
    
    // Incluir o arquivo de configuração
    require_once __DIR__ . '/backend/config.php';
    
    // Verificar constantes do banco de dados
    if (defined('DB_SERVER') && defined('DB_USERNAME') && defined('DB_PASSWORD') && defined('DB_NAME')) {
        echo "✓ Constantes do banco de dados definidas\n";
        
        // Testar conexão
        if (isset($conn) && $conn) {
            echo "✓ Conexão com o banco de dados bem-sucedida\n";
            
            // Verificar tabelas essenciais
            $tabelas_essenciais = ['usuarios', 'artigos', 'comentarios'];
            foreach ($tabelas_essenciais as $tabela) {
                $result = mysqli_query($conn, "SHOW TABLES LIKE '{$tabela}'");
                if (mysqli_num_rows($result) > 0) {
                    echo "✓ Tabela {$tabela}: Encontrada\n";
                } else {
                    echo "✗ AVISO: Tabela {$tabela} não encontrada\n";
                }
            }
        } else {
            echo "✗ ERRO: Não foi possível conectar ao banco de dados\n";
        }
    } else {
        echo "✗ ERRO: Constantes do banco de dados não definidas corretamente\n";
    }
} else {
    echo "✗ ERRO: Arquivo config.php não encontrado\n";
}
echo "\n";

// Verificar diretórios e permissões
echo "## Verificando diretórios e permissões\n";
$diretorios = [
    __DIR__ . '/uploads',
    __DIR__ . '/assets',
    __DIR__ . '/backend',
    __DIR__ . '/PAGES'
];

foreach ($diretorios as $dir) {
    if (is_dir($dir)) {
        echo "✓ Diretório {$dir}: Encontrado\n";
        
        // Verificar permissões (apenas para o diretório uploads)
        if (basename($dir) == 'uploads') {
            $permissoes = substr(sprintf('%o', fileperms($dir)), -4);
            echo "  Permissões: {$permissoes}\n";
            
            if (is_writable($dir)) {
                echo "  ✓ Diretório tem permissão de escrita\n";
            } else {
                echo "  ✗ AVISO: Diretório não tem permissão de escrita\n";
            }
        }
    } else {
        echo "✗ ERRO: Diretório {$dir} não encontrado\n";
    }
}
echo "\n";

// Verificar configuração SendGrid
echo "## Verificando configuração SendGrid\n";
if (file_exists(__DIR__ . '/backend/sendgrid_email.php')) {
    echo "✓ Arquivo sendgrid_email.php encontrado\n";
    
    // Incluir o arquivo (se não foi incluído ainda)
    if (!defined('SENDGRID_API_KEY')) {
        require_once __DIR__ . '/backend/sendgrid_email.php';
    }
    
    if (defined('SENDGRID_API_KEY') && !empty(SENDGRID_API_KEY)) {
        echo "✓ SendGrid API Key configurada\n";
    } else {
        echo "✗ AVISO: SendGrid API Key não configurada ou vazia\n";
    }
} else {
    echo "✗ ERRO: Arquivo sendgrid_email.php não encontrado\n";
}
echo "\n";

// Verificar arquivos essenciais
echo "## Verificando arquivos essenciais\n";
$arquivos_essenciais = [
    __DIR__ . '/index.php',
    __DIR__ . '/PAGES/index.php',
    __DIR__ . '/PAGES/login.php',
    __DIR__ . '/PAGES/cadastro.php',
    __DIR__ . '/PAGES/artigos.php',
    __DIR__ . '/PAGES/artigo.php',
    __DIR__ . '/PAGES/contato.php',
    __DIR__ . '/PAGES/admin_dashboard.php'
];

foreach ($arquivos_essenciais as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✓ Arquivo " . basename($arquivo) . ": Encontrado\n";
    } else {
        echo "✗ ERRO: Arquivo " . basename($arquivo) . " não encontrado\n";
    }
}
echo "\n";

echo "=================================================\n";
echo "     VERIFICAÇÃO CONCLUÍDA                       \n";
echo "=================================================\n\n";

echo "PRÓXIMOS PASSOS:\n";
echo "1. Atualize config.php com as credenciais do InfinityFree\n";
echo "2. Atualize as URLs em sendgrid_email.php\n";
echo "3. Faça upload de todos os arquivos para o InfinityFree\n";
echo "4. Importe o banco de dados no phpMyAdmin do InfinityFree\n";
echo "5. Teste todas as funcionalidades do site\n";
echo "\n";
?>
