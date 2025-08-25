<?php
/**
 * Script para remover marcadores de conflito de Git de arquivos PHP
 * 
 * Este script procura por arquivos PHP em um diretório (e seus subdiretórios)
 * e remove os marcadores de conflito de Git, escolhendo uma das versões (geralmente "Updated upstream").
 */

// Configuração
$rootDir = __DIR__; // Diretório raiz do projeto
$extensions = ['php', 'js']; // Extensões de arquivo a processar

// Contadores
$processed = 0;
$modified = 0;
$errors = 0;

echo "Iniciando processamento de arquivos...\n";

// Processar arquivos recursivamente
processDirectory($rootDir);

echo "\nProcessamento concluído!\n";
echo "Arquivos processados: $processed\n";
echo "Arquivos modificados: $modified\n";
echo "Erros: $errors\n";

/**
 * Processa um diretório recursivamente
 */
function processDirectory($dir) {
    global $extensions, $processed, $modified, $errors;
    
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Ignorar diretórios específicos como .git e vendor
            if (in_array($file, ['.git', 'vendor', 'node_modules'])) continue;
            
            processDirectory($path);
        } else {
            // Verificar extensão
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $extensions)) continue;
            
            // Processar arquivo
            $processed++;
            echo "Processando: $path\n";
            
            try {
                $content = file_get_contents($path);
                $newContent = fixGitConflicts($content);
                
                if ($content !== $newContent) {
                    file_put_contents($path, $newContent);
                    $modified++;
                    echo " - Modificado: Conflitos de Git removidos\n";
                }
            } catch (Exception $e) {
                $errors++;
                echo " - Erro: " . $e->getMessage() . "\n";
            }
        }
    }
}

/**
 * Corrige conflitos de Git no conteúdo do arquivo
 */
function fixGitConflicts($content) {
    // Padrão para encontrar blocos de conflito
    $pattern = '/(.*?)/s';
    
    // Substituir cada bloco de conflito pelo conteúdo do "Updated upstream"
    $newContent = preg_replace_callback($pattern, function($matches) {
        return $matches[1]; // Apenas o conteúdo de "Updated upstream"
    }, $content);
    
    // Se não houver matches, tentar invertendo as branches (às vezes o conflito está ao contrário)
    if ($newContent === $content) {
        $pattern = '/.*?/s';
        $newContent = preg_replace_callback($pattern, function($matches) {
            return $matches[1]; // Apenas o conteúdo de "HEAD"
        }, $content);
    }
    
    // Remover quaisquer marcadores órfãos (não apanhados pelos padrões acima)
    $newContent = str_replace('', '', $newContent);
    $newContent = str_replace('', '', $newContent);
    $newContent = str_replace('', '', $newContent);
    
    return $newContent;
}
