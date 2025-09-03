<?php
session_start();

// Incluir arquivos necessários
require_once '../backend/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Se não há mensagem de sucesso na sessão, redirecionar para a página inicial
if (!isset($_SESSION['artigo_enviado']) || $_SESSION['artigo_enviado'] !== true) {
    header("location: index.php");
    exit;
}

// Limpar a variável da sessão
$_SESSION['artigo_enviado'] = false;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigo Enviado - EntreLinhas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="form-container text-center fade-in">
            <i class="fas fa-paper-plane" style="font-size: 5rem; color: var(--primary);"></i>
            <h1 class="form-title mt-3">Artigo Enviado com Sucesso!</h1>
            
            <p>Seu artigo foi enviado para aprovação. Nossa equipe editorial irá revisar o conteúdo e, se aprovado, será publicado no EntreLinhas.</p>
            
            <div class="info-box mt-4">
                <h3><i class="fas fa-info-circle"></i> O que acontece agora?</h3>
                <ul class="text-left">
                    <li>Nossa equipe receberá seu artigo e fará uma revisão cuidadosa</li>
                    <li>Você receberá um e-mail quando houver uma decisão sobre seu artigo</li>
                    <li>Se aprovado, seu artigo será publicado e ficará disponível na seção de Artigos</li>
                </ul>
            </div>
            
            <div class="mt-4">
                <a href="meus-artigos.php" class="btn btn-primary">Meus Artigos</a>
                <a href="enviar-artigo.php" class="btn btn-secondary mt-2">Enviar Outro Artigo</a>
                <a href="../index.php" class="btn btn-outline mt-2">Voltar para o Início</a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
