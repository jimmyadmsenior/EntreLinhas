<?php
// Script para verificar quais arquivos ainda usam mysqli ao invés de PDO

// Configuração
$dir = __DIR__; // Diretório raiz do projeto
$padroes_mysqli = [
    'mysqli_connect',
    'mysqli_query',
    'mysqli_fetch',
    'mysqli_prepare',
    'mysqli_stmt',
    'mysqli_real_escape_string',
    'mysqli_error',
    'mysqli_close',
    'mysqli_set_charset',
    'mysqli_affected_rows',
    'mysqli_insert_id'
];

// Função para verificar recursivamente todos os arquivos PHP
function verificarArquivos($dir, $padroes) {
    $resultado = [];
    $arquivos = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($arquivos as $arquivo) {
        if ($arquivo->getExtension() === 'php') {
            $conteudo = file_get_contents($arquivo->getPathname());
            $encontrados = [];
            
            foreach ($padroes as $padrao) {
                if (stripos($conteudo, $padrao) !== false) {
                    $encontrados[] = $padrao;
                }
            }
            
            if (!empty($encontrados)) {
                $resultado[$arquivo->getPathname()] = $encontrados;
            }
        }
    }
    
    return $resultado;
}

// Arquivos a serem ignorados (geralmente arquivos de backup, históricos, etc)
$ignorar_arquivos = [
    '_bak',
    '_old',
    '.bak.',
    'backup'
];

// Executar verificação
$arquivos_com_mysqli = verificarArquivos($dir, $padroes_mysqli);

// Exibir resultados em HTML
echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificação de Migração para PDO</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .resultado { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .stats { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .ignore { color: #6c757d; text-decoration: line-through; }
        .warning { color: #dc3545; }
    </style>
</head>
<body>
    <h1>Verificação de Migração para PDO</h1>";

// Filtrar arquivos ignorados
$arquivos_filtrados = [];
foreach ($arquivos_com_mysqli as $caminho => $funcoes) {
    $ignorar = false;
    foreach ($ignorar_arquivos as $padrao) {
        if (stripos($caminho, $padrao) !== false) {
            $ignorar = true;
            break;
        }
    }
    
    if (!$ignorar) {
        $arquivos_filtrados[$caminho] = $funcoes;
    }
}

// Estatísticas
$total_arquivos = iterator_count(new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY,
    FilesystemIterator::CURRENT_AS_PATHNAME
));

$total_php = 0;
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $total_php++;
    }
}

$total_com_mysqli = count($arquivos_filtrados);
$percentagem = round(($total_com_mysqli / $total_php) * 100, 1);

echo "<div class='stats'>
    <h2>Estatísticas da Verificação</h2>
    <p><strong>Total de arquivos no projeto:</strong> {$total_arquivos}</p>
    <p><strong>Total de arquivos PHP:</strong> {$total_php}</p>
    <p><strong>Arquivos PHP com funções mysqli:</strong> {$total_com_mysqli} ({$percentagem}%)</p>
</div>";

// Tabela de resultados
echo "<div class='resultado'>
    <h2>Arquivos que ainda usam funções mysqli</h2>";

if (empty($arquivos_filtrados)) {
    echo "<p>Parabéns! Nenhum arquivo usa mais funções mysqli. A migração para PDO está completa!</p>";
} else {
    echo "<table>
        <tr>
            <th>Arquivo</th>
            <th>Funções mysqli encontradas</th>
        </tr>";
    
    foreach ($arquivos_filtrados as $caminho => $funcoes) {
        $caminho_relativo = str_replace($dir . '/', '', $caminho);
        echo "<tr>
            <td>{$caminho_relativo}</td>
            <td>" . implode(', ', $funcoes) . "</td>
        </tr>";
    }
    
    echo "</table>";
}

echo "</div>

<div class='resultado'>
    <h2>Próximos passos para completar a migração</h2>
    <ol>
        <li>Substitua o arquivo de configuração por config_pdo.php em todos os scripts</li>
        <li>Utilize as funções do pdo_helper.php para simplificar a migração</li>
        <li>Converta as consultas mysqli para prepared statements PDO</li>
        <li>Execute este script novamente para verificar o progresso</li>
    </ol>
</div>

</body>
</html>";
?>
