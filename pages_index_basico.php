<?php
// Versão parcial do index.php com funcionalidades básicas
// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Informações de conexão com o banco de dados
// NOTA: Em servidores compartilhados como InfinityFree, 
// as conexões só funcionam a partir do próprio servidor
define('DB_SERVER', 'localhost');  // Use 'localhost' em vez do endereço real quando estiver em produção
define('DB_USERNAME', 'if0_39798697');
define('DB_PASSWORD', 'xKIcJzBS13BB50t');
define('DB_NAME', 'if0_39798697_entrelinhas');

// Tentar conectar ao banco de dados
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Verificar conexão
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
    
    // Definir charset para utf8
    $conn->set_charset("utf8");
    
    // Funcionalidade básica: buscar alguns artigos para exibir
    $sql = "SELECT id, titulo, resumo, data_publicacao FROM artigos ORDER BY data_publicacao DESC LIMIT 5";
    $result = $conn->query($sql);
    
    // Iniciar o HTML básico
    echo '<!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EntreLinhas - Jornal Digital</title>
        <link rel="stylesheet" href="/assets/css/style.css">
    </head>
    <body>
        <header>
            <h1>EntreLinhas</h1>
            <nav>
                <ul>
                    <li><a href="/PAGES/index.php">Início</a></li>
                    <li><a href="/PAGES/artigos.php">Artigos</a></li>
                    <li><a href="/PAGES/contato.php">Contato</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section class="featured">
                <h2>Artigos Recentes</h2>';
                
    // Exibir artigos
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '<article>
                <h3>' . htmlspecialchars($row["titulo"]) . '</h3>
                <p class="date">' . date('d/m/Y', strtotime($row["data_publicacao"])) . '</p>
                <p>' . htmlspecialchars($row["resumo"]) . '</p>
                <a href="/PAGES/artigo.php?id=' . $row["id"] . '">Ler mais</a>
            </article>';
        }
    } else {
        echo '<p>Nenhum artigo encontrado.</p>';
    }
                
    echo '</section>
        </main>
        
        <footer>
            <p>&copy; ' . date("Y") . ' EntreLinhas - Jornal Digital. Todos os direitos reservados.</p>
            <p>Rua Israel, 100 - São Paulo/SP - (11) 4029-8635</p>
        </footer>
    </body>
    </html>';
    
    // Fechar conexão
    $conn->close();
    
} catch (Exception $e) {
    // Em caso de erro, exibir uma página básica com a mensagem de erro
    echo '<!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>EntreLinhas - Erro</title>
    </head>
    <body>
        <h1>Ops! Ocorreu um erro</h1>
        <p>Detalhes do erro: ' . $e->getMessage() . '</p>
        <p><a href="/">Voltar à página inicial</a></p>
    </body>
    </html>';
}
?>
