<?php
// Verificar se o index.php no diretório raiz está redirecionando para a pasta PAGES
echo "<h1>Verificação de Redirecionamento</h1>";

// Caminho atual
echo "<p>Arquivo Atual: " . __FILE__ . "</p>";

// Verificar se o index.php na raiz existe
$indexPath = dirname(dirname(__FILE__)) . '/index.php';
echo "<p>Path do index.php raiz: " . $indexPath . "</p>";

if (file_exists($indexPath)) {
    echo "<p style='color:green'>✅ O arquivo index.php existe na raiz!</p>";
    
    // Mostrar conteúdo
    $content = file_get_contents($indexPath);
    echo "<h2>Conteúdo do index.php:</h2>";
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
    
    // Verificar se tem redirecionamento
    if (strpos($content, 'header("Location: PAGES/index.php")') !== false) {
        echo "<p style='color:green'>✅ O arquivo index.php contém redirecionamento correto!</p>";
    } else {
        echo "<p style='color:orange'>⚠️ O arquivo index.php não contém o redirecionamento esperado.</p>";
    }
} else {
    echo "<p style='color:red'>❌ O arquivo index.php não existe na raiz!</p>";
    
    // Criar um arquivo index.php com redirecionamento
    echo "<p>Criando arquivo index.php na raiz com redirecionamento...</p>";
    
    $newContent = '<?php
// Redirecionar para a página principal
header("Location: PAGES/index.php");
exit;
?>';
    
    $result = file_put_contents($indexPath, $newContent);
    if ($result !== false) {
        echo "<p style='color:green'>✅ Arquivo index.php criado com sucesso!</p>";
    } else {
        echo "<p style='color:red'>❌ Falha ao criar o arquivo index.php!</p>";
    }
}

// Verificar permissões
echo "<h2>Permissões do diretório:</h2>";
echo "<p>Permissões de escrita na raiz: " . (is_writable(dirname(dirname(__FILE__))) ? 'Sim' : 'Não') . "</p>";
echo "<p>Permissões de escrita na pasta PAGES: " . (is_writable(dirname(__FILE__)) ? 'Sim' : 'Não') . "</p>";
echo "<p>Permissões de escrita na pasta backend: " . (is_writable(dirname(dirname(__FILE__)) . '/backend') ? 'Sim' : 'Não') . "</p>";
?>
