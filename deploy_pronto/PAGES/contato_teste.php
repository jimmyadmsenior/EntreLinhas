<?php
// Arquivo de contato simplificado para teste
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - Teste</title>
</head>
<body>
    <h1>Página de Contato - Teste</h1>
    <p>Esta é uma versão simplificada para teste.</p>
    
    <p>Status de sessão:
    <?php 
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        echo "Usuário logado como: " . htmlspecialchars($_SESSION["nome"]);
    } else {
        echo "Usuário não está logado";
    }
    ?>
    </p>
    
    <p><a href="index.php">Voltar para a página inicial</a></p>
</body>
</html>
