<?php
/**
 * Resolve Git merge conflicts in PHP files
 * This script searches for git conflict markers and removes them,
 * keeping the "Updated upstream" version by default.
 */

// Diretório raiz onde procurar arquivos PHP
$rootDir = __DIR__;

echo "<h1>Resolução de Conflitos Git em Arquivos PHP</h1>";

// Função para resolver conflitos em um arquivo
function resolveGitConflicts($filePath, $keepUpdated = true) {
    echo "<h3>Processando: " . basename($filePath) . "</h3>";
    
    // Ler o conteúdo do arquivo
    $content = file_get_contents($filePath);
    
    // Verificar se existem marcadores de conflito
    if (strpos($content, "<<<<<<<") === false) {
        echo "<p>Nenhum conflito encontrado.</p>";
        return false;
    }
    
    // Resolver conflitos
    $pattern = '/<<<<<<< .*?\n(.*?)\n(.*?)>>>>>>> .*?\n/s';
    
    // Substituir o padrão pelo conteúdo desejado
    $newContent = preg_replace_callback(
        $pattern,
        function($matches) use ($keepUpdated) {
            // $matches[1] contém o conteúdo "Updated upstream"
            // $matches[2] contém o conteúdo "Stashed changes"
            return $keepUpdated ? $matches[1] : $matches[2];
        },
        $content
    );
    
    // Se algo foi alterado, salvar o arquivo
    if ($newContent !== $content) {
        file_put_contents($filePath, $newContent);
        echo "<p style='color:green'>Conflitos resolvidos e arquivo salvo!</p>";
        return true;
    }
    
    echo "<p>Erro ao resolver conflitos.</p>";
    return false;
}

// Procurar todos os arquivos PHP no diretório
function findPHPFiles($dir, &$results = array()) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $file);
        
        if (!is_dir($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) == 'php') {
                $results[] = $path;
            }
        } else if ($file != "." && $file != "..") {
            findPHPFiles($path, $results);
        }
    }
    
    return $results;
}

// Encontrar todos os arquivos PHP
$phpFiles = findPHPFiles($rootDir);
$conflictCount = 0;
$resolvedCount = 0;

echo "<h2>Analisando " . count($phpFiles) . " arquivos PHP...</h2>";

// Processar cada arquivo
foreach ($phpFiles as $file) {
    // Verificar se o arquivo contém conflitos
    $content = file_get_contents($file);
    if (strpos($content, "<<<<<<<") !== false) {
        $conflictCount++;
        if (resolveGitConflicts($file, true)) {
            $resolvedCount++;
            
            // Copiar o arquivo corrigido para o diretório XAMPP
            $relPath = str_replace(__DIR__, '', $file);
            $xamppPath = "C:\\xampp\\htdocs\\EntreLinhas" . $relPath;
            
            if (file_exists(dirname($xamppPath))) {
                if (copy($file, $xamppPath)) {
                    echo "<p style='color:green'>Arquivo copiado para: " . $xamppPath . "</p>";
                } else {
                    echo "<p style='color:orange'>Não foi possível copiar para: " . $xamppPath . "</p>";
                }
            }
        }
    }
}

echo "<h2>Resumo</h2>";
echo "<p>Total de arquivos com conflitos: " . $conflictCount . "</p>";
echo "<p>Arquivos resolvidos: " . $resolvedCount . "</p>";

if ($conflictCount > 0) {
    echo "<p><a href='?'>Verificar novamente</a></p>";
}

// Link para o painel de administração
echo "<p><a href='PAGES/admin_dashboard.php'>Ir para o Painel de Administração</a></p>";
?>
