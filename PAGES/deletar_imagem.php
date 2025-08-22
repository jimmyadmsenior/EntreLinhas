<?php
// Incluir configuração do banco de dados
require_once "../backend/config.php";

// Verificar se está logado
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirecionar para a página de login
    header("Location: login.html");
    exit;
}

// Verificar se há um ID na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gerenciar_imagens.php");
    exit;
}

$id = intval($_GET['id']);
$usuario_id = $_SESSION['user_id'];
$mensagem = '';
$erro = '';

// Verificar se a imagem existe e pertence ao usuário
$sql = "SELECT id, usuario_id FROM imagens_artigos WHERE id = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $resultado = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($resultado)) {
            // Verificar se o usuário é o dono ou um admin
            $is_owner = ($row['usuario_id'] == $usuario_id);
            $is_admin = (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin');
            
            if ($is_owner || $is_admin) {
                // Excluir a imagem
                $sql_delete = "DELETE FROM imagens_artigos WHERE id = ?";
                
                if ($stmt_delete = mysqli_prepare($conn, $sql_delete)) {
                    mysqli_stmt_bind_param($stmt_delete, "i", $id);
                    
                    if (mysqli_stmt_execute($stmt_delete)) {
                        $mensagem = "Imagem excluída com sucesso!";
                    } else {
                        $erro = "Erro ao excluir a imagem: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt_delete);
                } else {
                    $erro = "Erro na preparação da consulta de exclusão: " . mysqli_error($conn);
                }
            } else {
                $erro = "Você não tem permissão para excluir esta imagem.";
            }
        } else {
            $erro = "Imagem não encontrada.";
        }
    } else {
        $erro = "Erro ao buscar a imagem: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
} else {
    $erro = "Erro na preparação da consulta: " . mysqli_error($conn);
}

// Fechar conexão
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Imagem - EntreLinhas</title>
    <meta name="description" content="Excluir imagem do EntreLinhas">
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
    <meta http-equiv="refresh" content="3;url=gerenciar_imagens.php">
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.html">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.html">Início</a></li>
                <li><a href="artigos.html">Artigos</a></li>
                <li><a href="escola.html">A Escola</a></li>
                <li><a href="contato.html">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar modo escuro">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="card fade-in">
            <div class="text-center">
                <?php if (!empty($mensagem)): ?>
                    <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--success);"></i>
                    <h1 class="mt-3"><?php echo $mensagem; ?></h1>
                    <p>Você será redirecionado para a página de gerenciamento de imagens em 3 segundos...</p>
                <?php elseif (!empty($erro)): ?>
                    <i class="fas fa-times-circle" style="font-size: 4rem; color: var(--error);"></i>
                    <h1 class="mt-3">Erro</h1>
                    <p><?php echo $erro; ?></p>
                    <p>Você será redirecionado para a página de gerenciamento de imagens em 3 segundos...</p>
                <?php endif; ?>
                
                <a href="gerenciar_imagens.php" class="btn btn-primary mt-4">Voltar agora</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-section">
            <h3>EntreLinhas</h3>
            <p>Um jornal digital colaborativo onde vozes diversas se encontram para compartilhar conhecimento, histórias e experiências.</p>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
