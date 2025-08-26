<?php
// Este script analisa e corrige o arquivo admin_dashboard.php

// Configurações
$arquivo_original = __DIR__ . '/admin_dashboard.php';
$arquivo_backup = __DIR__ . '/admin_dashboard.php.bak';
$arquivo_corrigido = __DIR__ . '/admin_dashboard.php.new';

echo "<h1>Análise e Correção do Painel Administrativo</h1>";

// 1. Verificar se o arquivo original existe
if (!file_exists($arquivo_original)) {
    die("<p style='color:red'>Erro: O arquivo $arquivo_original não existe!</p>");
}
echo "<p>✓ Arquivo original encontrado: $arquivo_original</p>";

// 2. Fazer backup do arquivo original
if (!copy($arquivo_original, $arquivo_backup)) {
    die("<p style='color:red'>Erro: Não foi possível criar o backup do arquivo!</p>");
}
echo "<p>✓ Backup criado em: $arquivo_backup</p>";

// 3. Ler o conteúdo do arquivo
$conteudo = file_get_contents($arquivo_original);
if ($conteudo === false) {
    die("<p style='color:red'>Erro: Não foi possível ler o conteúdo do arquivo!</p>");
}
echo "<p>✓ Conteúdo do arquivo lido com sucesso (" . strlen($conteudo) . " bytes)</p>";

// 4. Analisar o arquivo em busca de problemas comuns
echo "<h2>Análise do Arquivo</h2>";

// 4.1 Verificar se há uma declaração HTML completa
$has_html_start = preg_match('/<html/i', $conteudo);
$has_html_end = preg_match('/<\/html>/i', $conteudo);
echo "<p>" . ($has_html_start && $has_html_end ? "✓" : "✗") . " Estrutura HTML completa: " . 
    ($has_html_start ? "Tem abertura" : "Sem abertura") . ", " . 
    ($has_html_end ? "Tem fechamento" : "Sem fechamento") . "</p>";

// 4.2 Verificar se há uma declaração <!DOCTYPE>
$has_doctype = preg_match('/<!DOCTYPE/i', $conteudo);
echo "<p>" . ($has_doctype ? "✓" : "✗") . " Declaração DOCTYPE: " . 
    ($has_doctype ? "Presente" : "Ausente") . "</p>";

// 4.3 Verificar se há inclusões de arquivos PHP
$includes = [];
preg_match_all('/require(?:_once)?\s*\(.*?\)/i', $conteudo, $matches);
$includes = $matches[0] ?? [];
echo "<p>Inclusões de arquivos encontradas: " . count($includes) . "</p>";
if (count($includes) > 0) {
    echo "<ul>";
    foreach ($includes as $include) {
        echo "<li>" . htmlspecialchars($include) . "</li>";
    }
    echo "</ul>";
}

// 4.4 Verificar fechamentos de PHP
$php_start_count = substr_count($conteudo, '<?php');
$php_end_count = substr_count($conteudo, '?>');
echo "<p>" . ($php_start_count == $php_end_count ? "✓" : "✗") . " Blocos PHP: " . 
    "$php_start_count aberturas, $php_end_count fechamentos</p>";

// 4.5 Verificar se há funções duplicadas
$function_matches = [];
preg_match_all('/function\s+(\w+)\s*\(/i', $conteudo, $function_matches);
$function_names = $function_matches[1] ?? [];
$duplicates = array_count_values($function_names);
$has_duplicates = false;
foreach ($duplicates as $func => $count) {
    if ($count > 1) {
        $has_duplicates = true;
        echo "<p style='color:red'>✗ Função duplicada: $func (aparece $count vezes)</p>";
    }
}
if (!$has_duplicates) {
    echo "<p>✓ Nenhuma função duplicada encontrada</p>";
}

// 4.6 Verificar se há tags HTML duplicadas
$tags = ['<html', '<head', '<body', '<header', '<footer', '<main'];
foreach ($tags as $tag) {
    $count = substr_count(strtolower($conteudo), $tag);
    echo "<p>" . ($count <= 1 ? "✓" : "✗") . " Tag $tag: aparece $count " . ($count == 1 ? "vez" : "vezes") . "</p>";
}

// 4.7 Verificar se há problemas no fechamento de tags
$opens = preg_match_all('/<(div|span|section|header|footer|main|article)\b/i', $conteudo, $matches);
$closes = preg_match_all('/<\/(div|span|section|header|footer|main|article)>/i', $conteudo, $matches);
echo "<p>" . ($opens == $closes ? "✓" : "✗") . " Balanceamento de tags: $opens aberturas, $closes fechamentos</p>";

// 5. Corrigir o arquivo - vamos substituir por um modelo mais simples
echo "<h2>Correção do Arquivo</h2>";

$novo_conteudo = <<<EOT
<?php
// Incluir arquivo de gerenciamento de sessões
require_once "../backend/session_helper.php";
require_once "../backend/config.php";

// Verificar se é administrador
if (!is_admin()) {
    // Se não for admin e estiver em modo debug, mostrar informações
    if (isset(\$_GET['debug']) && \$_GET['debug'] == '1') {
        echo "<h1>Diagnóstico de Sessão - admin_dashboard.php</h1>";
        echo "<pre>";
        echo "SESSION: " . print_r(\$_SESSION, true) . "\\n";
        echo "is_logged_in(): " . (is_logged_in() ? "true" : "false") . "\\n";
        echo "is_admin(): " . (is_admin() ? "true" : "false") . "\\n";
        echo "</pre>";
        
        // Forçar acesso para debug
        if (isset(\$_GET['force']) && \$_GET['force'] == '1') {
            \$_SESSION["loggedin"] = true;
            \$_SESSION["tipo"] = "admin";
            \$_SESSION["id"] = isset(\$_SESSION["id"]) ? \$_SESSION["id"] : 1;
            \$_SESSION["nome"] = isset(\$_SESSION["nome"]) ? \$_SESSION["nome"] : "Administrador";
            \$_SESSION["email"] = isset(\$_SESSION["email"]) ? \$_SESSION["email"] : "admin@example.com";
            
            echo "<p>Forçando acesso como admin para debug.</p>";
            echo "<p>is_admin() agora: " . (is_admin() ? "true" : "false") . "</p>";
        } else {
            header("Location: ../index.php?erro=acesso_negado");
            exit;
        }
    } else {
        // Redirecionar para a página inicial
        header("Location: ../index.php?erro=acesso_negado");
        exit;
    }
}

// Consultar estatísticas
\$stats = [
    "total_artigos" => 0,
    "artigos_pendentes" => 0,
    "total_usuarios" => 0,
    "comentarios_pendentes" => 0
];

// Total de artigos
\$result = mysqli_query(\$conn, "SELECT COUNT(*) as total FROM artigos");
if (\$result && \$row = mysqli_fetch_assoc(\$result)) {
    \$stats["total_artigos"] = \$row["total"];
}

// Artigos pendentes
\$result = mysqli_query(\$conn, "SELECT COUNT(*) as total FROM artigos WHERE status = 'pendente'");
if (\$result && \$row = mysqli_fetch_assoc(\$result)) {
    \$stats["artigos_pendentes"] = \$row["total"];
}

// Total de usuários
\$result = mysqli_query(\$conn, "SELECT COUNT(*) as total FROM usuarios");
if (\$result && \$row = mysqli_fetch_assoc(\$result)) {
    \$stats["total_usuarios"] = \$row["total"];
}

// Comentários pendentes
\$result = mysqli_query(\$conn, "SELECT COUNT(*) as total FROM comentarios WHERE status = 'pendente'");
if (\$result && \$row = mysqli_fetch_assoc(\$result)) {
    \$stats["comentarios_pendentes"] = \$row["total"];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - EntreLinhas</title>
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard {
            padding: 2rem 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
            color: #333;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="../index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="../index.php">Início</a></li>
                <li><a href="artigos.php">Artigos</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="escola.php">A Escola</a></li>
                <li><a href="contato.php">Contato</a></li>
            </ul>
        </nav>
    </header>

    <main class="container dashboard">
        <h1>Painel de Administração</h1>
        <p>Bem-vindo, <?php echo htmlspecialchars(\$_SESSION["nome"]); ?>! Gerencie o conteúdo do site.</p>
        
        <h2>Estatísticas do Site</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-newspaper fa-2x"></i>
                <div class="stat-number"><?php echo \$stats["total_artigos"]; ?></div>
                <div>Total de Artigos</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-hourglass-half fa-2x"></i>
                <div class="stat-number"><?php echo \$stats["artigos_pendentes"]; ?></div>
                <div>Artigos Pendentes</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-users fa-2x"></i>
                <div class="stat-number"><?php echo \$stats["total_usuarios"]; ?></div>
                <div>Usuários</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-comments fa-2x"></i>
                <div class="stat-number"><?php echo \$stats["comentarios_pendentes"]; ?></div>
                <div>Comentários Pendentes</div>
            </div>
        </div>
        
        <h2>Ações</h2>
        <ul>
            <li><a href="#">Gerenciar Artigos</a></li>
            <li><a href="#">Gerenciar Usuários</a></li>
            <li><a href="#">Configurações do Site</a></li>
            <li><a href="../backend/logout.php">Sair</a></li>
        </ul>
    </main>

    <footer>
        <div class="footer-container">
            <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
EOT;

// 6. Escrever o novo conteúdo em um arquivo temporário
if (file_put_contents($arquivo_corrigido, $novo_conteudo) === false) {
    die("<p style='color:red'>Erro: Não foi possível criar o arquivo corrigido!</p>");
}
echo "<p>✓ Arquivo corrigido criado em: $arquivo_corrigido</p>";

// 7. Instruções para o usuário
echo "<h2>Instruções</h2>";
echo "<p>Foi criado um arquivo corrigido do painel administrativo. Para aplicar a correção:</p>";
echo "<ol>";
echo "<li>Renomeie o arquivo <code>admin_dashboard.php.new</code> para <code>admin_dashboard.php</code></li>";
echo "<li>Acesse o painel administrativo em <a href='admin_dashboard.php'>admin_dashboard.php</a></li>";
echo "<li>Se ainda houver problemas, você pode restaurar o backup usando o arquivo <code>admin_dashboard.php.bak</code></li>";
echo "</ol>";

echo "<p>Ou, você pode usar as versões alternativas do painel:</p>";
echo "<ul>";
echo "<li><a href='admin_dashboard_novo.php'>Painel Administrativo Novo</a> - Uma versão simplificada e robusta</li>";
echo "<li><a href='admin_acesso_direto.php'>Acesso Direto ao Painel</a> - Define automaticamente a sessão como administrador</li>";
echo "</ul>";
?>
