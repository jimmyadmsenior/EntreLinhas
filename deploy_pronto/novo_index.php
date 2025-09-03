<?php
// Página inicial básica do EntreLinhas
// Configurando para exibir erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para exibir mensagens de erro/sucesso
function showMessage($message, $type = 'info') {
    $color = 'blue';
    if ($type == 'error') $color = 'red';
    if ($type == 'success') $color = 'green';
    echo "<div style='padding: 10px; margin: 10px 0; background-color: #f8f9fa; border-left: 5px solid $color;'>{$message}</div>";
}

// Tentar conexão com o banco de dados
$dbConnected = false;
try {
    // Usando PDO para maior compatibilidade
    $db = new PDO('mysql:host=localhost;dbname=if0_39798697_entrelinhas', 'if0_39798697', 'xKIcJzBS13BB50t');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbConnected = true;
} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
}

// HTML básico
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EntreLinhas - Jornal Digital</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        header {
            background-color: #0d47a1;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            padding: 0;
            margin: 0;
        }
        nav li {
            margin: 0 10px;
        }
        nav a {
            color: white;
            text-decoration: none;
        }
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        article {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .date {
            color: #666;
            font-size: 0.9em;
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #f5f5f5;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <header>
        <h1>EntreLinhas</h1>
        <nav>
            <ul>
                <li><a href="/">Início</a></li>
                <li><a href="/PAGES/artigos.php">Artigos</a></li>
                <li><a href="/PAGES/contato.php">Contato</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <?php if (!$dbConnected): ?>
            <?php showMessage("Não foi possível conectar ao banco de dados: " . $errorMessage, 'error'); ?>
            <p>O site está em manutenção. Por favor, volte mais tarde.</p>
        <?php else: ?>
            <section class="featured">
                <h2>Artigos Recentes</h2>
                <?php
                    try {
                        $stmt = $db->query('SELECT id, titulo, resumo, data_publicacao FROM artigos ORDER BY data_publicacao DESC LIMIT 5');
                        
                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<article>
                                    <h3>' . htmlspecialchars($row["titulo"]) . '</h3>
                                    <p class="date">' . date('d/m/Y', strtotime($row["data_publicacao"])) . '</p>
                                    <p>' . htmlspecialchars($row["resumo"]) . '</p>
                                    <a href="/PAGES/artigo.php?id=' . $row["id"] . '">Ler mais</a>
                                </article>';
                            }
                        } else {
                            showMessage('Nenhum artigo encontrado no momento.');
                        }
                    } catch (PDOException $e) {
                        showMessage('Erro ao buscar artigos: ' . $e->getMessage(), 'error');
                    }
                ?>
            </section>
        <?php endif; ?>
    </main>
    
    <footer>
        <p>&copy; <?php echo date("Y"); ?> EntreLinhas - Jornal Digital. Todos os direitos reservados.</p>
        <p>Rua Israel, 100 - São Paulo/SP - (11) 4029-8635</p>
    </footer>
</body>
</html>
