<?php
// Script para verificar o status da migração para PDO e gerar um relatório detalhado

// Configuração
$dir = __DIR__; // Diretório raiz do projeto
$report_file = __DIR__ . '/relatorio_migracao_pdo.html';

// Padrões de busca
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

$padroes_pdo = [
    'new PDO',
    'PDO::',
    '->prepare',
    '->execute',
    '->fetch',
    '->query',
    'PDO::PARAM_'
];

$arquivos_config = [
    'config.php',
    'config_infinityfree.php',
    'config_pdo.php',
    'backend/config.php',
    'backend/config_infinityfree.php',
    'backend/config_pdo.php'
];

$arquivos_importantes = [
    'PAGES/login.php',
    'backend/process_login.php',
    'PAGES/artigos.php',
    'PAGES/artigo.php',
    'PAGES/enviar-artigo.php',
    'PAGES/index.php',
    'PAGES/register.php',
    'PAGES/meus-artigos.php',
    'PAGES/admin_dashboard.php'
];

// Função para verificar arquivos recursivamente
function verificarArquivos($dir, $padroes, $tipo = 'mysqli') {
    $resultado = [];
    $arquivos = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($arquivos as $arquivo) {
        if ($arquivo->getExtension() === 'php') {
            $caminho = $arquivo->getPathname();
            $conteudo = file_get_contents($caminho);
            $encontrados = [];
            
            foreach ($padroes as $padrao) {
                if (stripos($conteudo, $padrao) !== false) {
                    preg_match_all('/' . preg_quote($padrao, '/') . '/', $conteudo, $matches);
                    $count = count($matches[0]);
                    $encontrados[$padrao] = $count;
                }
            }
            
            if (!empty($encontrados)) {
                $resultado[$caminho] = [
                    'padroes' => $encontrados,
                    'total' => array_sum($encontrados)
                ];
            }
        }
    }
    
    return $resultado;
}

// Arquivos a serem ignorados (arquivos de backup, etc)
$ignorar_padroes = [
    '_bak',
    '_old',
    '.bak.',
    'backup',
    '_backup'
];

// Verificar se um arquivo deve ser ignorado
function deveIgnorar($caminho) {
    global $ignorar_padroes;
    foreach ($ignorar_padroes as $padrao) {
        if (stripos($caminho, $padrao) !== false) {
            return true;
        }
    }
    return false;
}

// Verificar os arquivos de configuração
function verificarConfiguracoes($arquivos_config) {
    $resultado = [];
    
    foreach ($arquivos_config as $arquivo) {
        $caminho = __DIR__ . '/' . $arquivo;
        if (file_exists($caminho)) {
            $conteudo = file_get_contents($caminho);
            $usa_mysqli = stripos($conteudo, 'mysqli_connect') !== false;
            $usa_pdo = stripos($conteudo, 'new PDO') !== false;
            
            $resultado[$arquivo] = [
                'existe' => true,
                'usa_mysqli' => $usa_mysqli,
                'usa_pdo' => $usa_pdo
            ];
        } else {
            $resultado[$arquivo] = [
                'existe' => false,
                'usa_mysqli' => false,
                'usa_pdo' => false
            ];
        }
    }
    
    return $resultado;
}

// Verificar os arquivos importantes
function verificarArquivosImportantes($arquivos_importantes) {
    $resultado = [];
    
    foreach ($arquivos_importantes as $arquivo) {
        $caminho = __DIR__ . '/' . $arquivo;
        if (file_exists($caminho)) {
            $conteudo = file_get_contents($caminho);
            $usa_mysqli = (
                stripos($conteudo, 'mysqli_') !== false || 
                stripos($conteudo, 'config.php') !== false ||
                stripos($conteudo, '$conn =') !== false
            );
            $usa_pdo = (
                stripos($conteudo, 'new PDO') !== false || 
                stripos($conteudo, 'config_pdo.php') !== false ||
                stripos($conteudo, '$pdo->') !== false
            );
            
            $resultado[$arquivo] = [
                'existe' => true,
                'usa_mysqli' => $usa_mysqli,
                'usa_pdo' => $usa_pdo,
                'status' => $usa_pdo ? 'Migrado para PDO' : 'Precisa ser migrado'
            ];
        } else {
            $resultado[$arquivo] = [
                'existe' => false,
                'usa_mysqli' => false,
                'usa_pdo' => false,
                'status' => 'Arquivo não encontrado'
            ];
        }
    }
    
    return $resultado;
}

// Executar as verificações
$arquivos_mysqli = verificarArquivos($dir, $padroes_mysqli, 'mysqli');
$arquivos_pdo = verificarArquivos($dir, $padroes_pdo, 'pdo');
$config_status = verificarConfiguracoes($arquivos_config);
$arquivos_importantes_status = verificarArquivosImportantes($arquivos_importantes);

// Filtrar arquivos de backup
$arquivos_mysqli_filtrados = [];
foreach ($arquivos_mysqli as $caminho => $info) {
    if (!deveIgnorar($caminho)) {
        $arquivos_mysqli_filtrados[$caminho] = $info;
    }
}

$arquivos_pdo_filtrados = [];
foreach ($arquivos_pdo as $caminho => $info) {
    if (!deveIgnorar($caminho)) {
        $arquivos_pdo_filtrados[$caminho] = $info;
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
    if ($file->getExtension() === 'php' && !deveIgnorar($file->getPathname())) {
        $total_php++;
    }
}

$total_com_mysqli = count($arquivos_mysqli_filtrados);
$total_com_pdo = count($arquivos_pdo_filtrados);
$percentagem_mysqli = $total_php > 0 ? round(($total_com_mysqli / $total_php) * 100, 1) : 0;
$percentagem_pdo = $total_php > 0 ? round(($total_com_pdo / $total_php) * 100, 1) : 0;

// Gerar relatório HTML
$html = "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Relatório de Migração para PDO</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .stats { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .progress-bar { 
            background-color: #f0f0f0; 
            height: 20px; 
            border-radius: 10px; 
            margin: 10px 0;
            overflow: hidden;
        }
        .progress { 
            height: 100%; 
            background-color: #4CAF50; 
            text-align: center; 
            line-height: 20px; 
            color: white; 
            transition: width 0.5s;
        }
        .warning { color: #dc3545; }
        .success { color: #28a745; }
        .info { color: #17a2b8; }
        .section { margin: 30px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
        }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-info { background-color: #17a2b8; }
    </style>
</head>
<body>
    <h1>Relatório de Migração para PDO</h1>
    <p>Este relatório mostra o status da migração de mysqli para PDO no projeto.</p>
    
    <div class='stats section'>
        <h2>Estatísticas Gerais</h2>
        <p><strong>Total de arquivos PHP:</strong> {$total_php}</p>
        <p><strong>Arquivos usando mysqli:</strong> {$total_com_mysqli} ({$percentagem_mysqli}%)</p>
        <p><strong>Arquivos usando PDO:</strong> {$total_com_pdo} ({$percentagem_pdo}%)</p>
        
        <h3>Progresso da Migração:</h3>
        <div class='progress-bar'>
            <div class='progress' style='width: " . (100 - $percentagem_mysqli) . "%;'>" . (100 - $percentagem_mysqli) . "%</div>
        </div>
    </div>
    
    <div class='section'>
        <h2>Arquivos de Configuração</h2>
        <table>
            <tr>
                <th>Arquivo</th>
                <th>Existe</th>
                <th>Usa mysqli</th>
                <th>Usa PDO</th>
                <th>Status</th>
            </tr>";

foreach ($config_status as $arquivo => $info) {
    $status = '';
    if (!$info['existe']) {
        $status = '<span class="badge badge-warning">Não encontrado</span>';
    } elseif ($info['usa_pdo']) {
        $status = '<span class="badge badge-success">PDO</span>';
    } elseif ($info['usa_mysqli']) {
        $status = '<span class="badge badge-danger">mysqli</span>';
    } else {
        $status = '<span class="badge badge-info">Indefinido</span>';
    }
    
    $html .= "<tr>
        <td>{$arquivo}</td>
        <td>" . ($info['existe'] ? 'Sim' : 'Não') . "</td>
        <td>" . ($info['usa_mysqli'] ? 'Sim' : 'Não') . "</td>
        <td>" . ($info['usa_pdo'] ? 'Sim' : 'Não') . "</td>
        <td>{$status}</td>
    </tr>";
}

$html .= "</table>
    </div>
    
    <div class='section'>
        <h2>Arquivos Importantes</h2>
        <p>Esta seção mostra o status dos arquivos mais importantes do projeto que provavelmente precisam de acesso ao banco de dados.</p>
        
        <table>
            <tr>
                <th>Arquivo</th>
                <th>Existe</th>
                <th>Usa mysqli</th>
                <th>Usa PDO</th>
                <th>Status</th>
            </tr>";

foreach ($arquivos_importantes_status as $arquivo => $info) {
    $status = '';
    if (!$info['existe']) {
        $status = '<span class="badge badge-warning">Não encontrado</span>';
    } elseif ($info['usa_pdo'] && !$info['usa_mysqli']) {
        $status = '<span class="badge badge-success">Migrado para PDO</span>';
    } elseif ($info['usa_mysqli'] && !$info['usa_pdo']) {
        $status = '<span class="badge badge-danger">Precisa migrar</span>';
    } elseif ($info['usa_mysqli'] && $info['usa_pdo']) {
        $status = '<span class="badge badge-warning">Migração parcial</span>';
    } else {
        $status = '<span class="badge badge-info">Não usa DB</span>';
    }
    
    $html .= "<tr>
        <td>{$arquivo}</td>
        <td>" . ($info['existe'] ? 'Sim' : 'Não') . "</td>
        <td>" . ($info['usa_mysqli'] ? 'Sim' : 'Não') . "</td>
        <td>" . ($info['usa_pdo'] ? 'Sim' : 'Não') . "</td>
        <td>{$status}</td>
    </tr>";
}

$html .= "</table>
    </div>
    
    <div class='section'>
        <h2>Arquivos que ainda usam mysqli</h2>";

if (empty($arquivos_mysqli_filtrados)) {
    $html .= "<p class='success'>Parabéns! Nenhum arquivo está usando funções mysqli.</p>";
} else {
    $html .= "<p class='warning'>Os seguintes arquivos ainda contêm funções mysqli e precisam ser migrados:</p>
        <table>
            <tr>
                <th>Arquivo</th>
                <th>Funções mysqli encontradas</th>
            </tr>";
    
    foreach ($arquivos_mysqli_filtrados as $caminho => $info) {
        $caminho_relativo = str_replace($dir . '/', '', $caminho);
        $html .= "<tr>
            <td>{$caminho_relativo}</td>
            <td>";
        
        foreach ($info['padroes'] as $padrao => $contagem) {
            $html .= "{$padrao} ({$contagem})<br>";
        }
        
        $html .= "</td>
        </tr>";
    }
    
    $html .= "</table>";
}

$html .= "
    </div>
    
    <div class='section'>
        <h2>Próximos Passos</h2>
        <ol>
            <li>Migrar os arquivos importantes restantes para PDO</li>
            <li>Garantir que todos os arquivos incluam config_pdo.php em vez de config.php</li>
            <li>Testar todas as funcionalidades para garantir que a migração não causou problemas</li>
            <li>Considerar a remoção dos arquivos mysqli antigos após confirmar que tudo funciona corretamente</li>
        </ol>
    </div>
    
    <footer>
        <p>Relatório gerado em: " . date('d/m/Y H:i:s') . "</p>
    </footer>
</body>
</html>";

// Salvar o relatório
file_put_contents($report_file, $html);

// Redirecionar para o relatório
header("Location: relatorio_migracao_pdo.html");
exit;
?>
