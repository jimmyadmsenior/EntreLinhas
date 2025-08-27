<?php
// Iniciar a sessão para poder acessar as variáveis de sessão
session_start();

// Incluir arquivo de configuração para conexão com o banco de dados
require_once "../backend/config.php";

// Verificar se todos os arquivos necessários existem
$requiredFiles = [
    'includes/cabecalho_helper.php' => file_exists('includes/cabecalho_helper.php')
];

// Se algum arquivo necessário estiver faltando, exibir mensagem de erro
$missingFiles = array_filter($requiredFiles, function($exists) { return !$exists; });
if (!empty($missingFiles)) {
    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>EntreLinhas - Configuração</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: 0 auto; }
            .error { color: red; background: #ffeeee; padding: 10px; border-left: 5px solid red; }
            .success { color: green; background: #eeffee; padding: 10px; border-left: 5px solid green; }
            code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <h1>EntreLinhas - Configuração Pendente</h1>
        <div class='error'>
            <h2>Arquivos necessários não encontrados:</h2>
            <ul>";
            
    foreach(array_keys($missingFiles) as $file) {
        echo "<li><code>$file</code></li>";
    }
            
    echo "</ul>
        </div>
        <p>Verifique se os arquivos necessários foram enviados para o servidor.</p>
    </body>
    </html>";
    exit;
}

// Se chegou aqui, todos os arquivos necessários existem
// Incluir arquivo com funções do cabeçalho
require_once 'includes/cabecalho_helper.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EntreLinhas - Jornal Digital</title>
    <meta name="description" content="EntreLinhas - Jornal digital colaborativo com notícias, artigos e textos da comunidade.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user-menu.css">
    <link rel="stylesheet" href="../assets/css/alerts.css">
    <script src="../assets/js/user-menu.js" defer></script>
    <script src="../assets/js/theme.js" defer></script>
</head>
<body>
    <?php 
    try {
        // Tenta gerar o cabeçalho com o menu
        if (function_exists('gerar_cabecalho')) {
            echo gerar_cabecalho($conn, 'index.php');
        } else {
            echo "<header style='background-color: #0d47a1; color: white; padding: 1rem;'>
                <h1>EntreLinhas</h1>
                <nav>
                    <ul style='list-style: none; display: flex; gap: 20px;'>
                        <li><a href='/' style='color: white;'>Início</a></li>
                        <li><a href='/PAGES/artigos.php' style='color: white;'>Artigos</a></li>
                        <li><a href='/PAGES/contato.php' style='color: white;'>Contato</a></li>
                    </ul>
                </nav>
            </header>";
        }
    } catch (Exception $e) {
        echo "<div style='background-color: #ffeeee; color: red; padding: 10px; margin: 10px 0;'>
            Erro ao gerar cabeçalho: " . $e->getMessage() . "
        </div>";
        
        echo "<header style='background-color: #0d47a1; color: white; padding: 1rem;'>
            <h1>EntreLinhas</h1>
            <nav>
                <ul style='list-style: none; display: flex; gap: 20px;'>
                    <li><a href='/' style='color: white;'>Início</a></li>
                    <li><a href='/PAGES/artigos.php' style='color: white;'>Artigos</a></li>
                    <li><a href='/PAGES/contato.php' style='color: white;'>Contato</a></li>
                </ul>
            </nav>
        </header>";
    }
    ?>

    <main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <section class="featured">
            <h2>Artigos Recentes</h2>
            <?php
            try {
                // Buscar artigos recentes
                $sql = "SELECT id, titulo, resumo, data_publicacao FROM artigos ORDER BY data_publicacao DESC LIMIT 5";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<article style="border-bottom: 1px solid #eee; padding: 15px 0;">
                            <h3>' . htmlspecialchars($row["titulo"]) . '</h3>
                            <p style="color: #666; font-size: 0.9em;">' . date('d/m/Y', strtotime($row["data_publicacao"])) . '</p>
                            <p>' . htmlspecialchars($row["resumo"]) . '</p>
                            <a href="/PAGES/artigo.php?id=' . $row["id"] . '">Ler mais</a>
                        </article>';
                    }
                } else {
                    echo '<p>Nenhum artigo encontrado.</p>';
                }
            } catch (Exception $e) {
                echo "<div style='background-color: #ffeeee; color: red; padding: 10px; margin: 10px 0;'>
                    Erro ao buscar artigos: " . $e->getMessage() . "
                </div>";
            }
            ?>
        </section>
    </main>

    <footer style="text-align: center; padding: 20px; background-color: #f5f5f5; margin-top: 30px;">
        <p>&copy; <?php echo date("Y"); ?> EntreLinhas - Jornal Digital. Todos os direitos reservados.</p>
        <p>Rua Israel, 100 - São Paulo/SP - (11) 4029-8635</p>
    </footer>
</body>
</html>
