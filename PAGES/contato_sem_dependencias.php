<?php
// Versão simplificada do contato.php sem dependências
session_start();

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$page_title = "Contato - EntreLinhas";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="Entre em contato com a equipe do EntreLinhas para dúvidas, sugestões ou parcerias.">
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <li><a href="contato.php" class="active">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <?php if ($usuario_logado): ?>
                    <div>Usuário logado: <?php echo htmlspecialchars($_SESSION["nome"]); ?></div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Entrar</a>
                    <a href="registro.php" class="btn btn-primary">Cadastrar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container">
        <h1>Entre em Contato</h1>
        <p>Esta é uma versão simplificada do formulário de contato.</p>
        
        <form method="post" action="">
            <div>
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="mensagem">Mensagem:</label>
                <textarea id="mensagem" name="mensagem" required></textarea>
            </div>
            <div>
                <button type="submit">Enviar</button>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
