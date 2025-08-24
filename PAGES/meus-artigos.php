<?php
// Iniciar sessão
session_start();

// Incluir arquivo de sincronização entre localStorage e sessões PHP
require_once "../backend/sync_login.php";

// Verificar se o usuário está logado, senão redirecionar para a página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Incluir arquivo de configuração e helper de usuário
require_once "../backend/config.php";
require_once "../backend/usuario_helper.php";

// Obter a foto de perfil do usuário
$foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);

// Consultar os artigos do usuário
$sql = "SELECT id, titulo, categoria, data_criacao, status FROM artigos WHERE id_usuario = ? ORDER BY data_criacao DESC";
$result = null;
$erro_consulta = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    // Vincular variáveis à declaração preparada como parâmetros
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    
    // Tentar executar a declaração preparada
    if (mysqli_stmt_execute($stmt)) {
        // Armazenar resultado
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $erro_consulta = "Erro ao consultar artigos: " . mysqli_error($conn);
    }
    
    // Fechar declaração
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Artigos - EntreLinhas</title>
    <meta name="description" content="Gerencie seus artigos no EntreLinhas.">
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
    <style>
        .article-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pendente {
            background-color: #f0ad4e;
            color: #fff;
        }
        
        .status-aprovado {
            background-color: #5cb85c;
            color: #fff;
        }
        
        .status-rejeitado {
            background-color: #d9534f;
            color: #fff;
        }
        
        .article-list {
            margin-top: 2rem;
        }
        
        .article-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .article-item:last-child {
            border-bottom: none;
        }
        
        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .article-actions {
            margin-top: 1rem;
        }
        
        .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
                <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="page-header fade-in">
            <h1>Meus Artigos</h1>
            <p>Gerencie todos os seus artigos enviados para o EntreLinhas.</p>
        </div>
        
        <div class="content-container fade-in">
            <div class="action-bar">
                <a href="enviar-artigo.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Artigo
                </a>
            </div>
            
            <?php if (isset($erro_consulta)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $erro_consulta; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <div class="article-list">
                    <?php while ($row = mysqli_fetch_array($result)): ?>
                        <div class="article-item">
                            <h3><a href="artigo.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['titulo']); ?></a></h3>
                            
                            <div class="article-meta">
                                <span>
                                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($row['categoria']); ?> | 
                                    <i class="far fa-calendar"></i> <?php echo date("d/m/Y", strtotime($row['data_criacao'])); ?>
                                </span>
                                
                                <?php
                                $status_class = '';
                                $status_label = $row['status'];
                                
                                switch ($row['status']) {
                                    case 'pendente':
                                        $status_class = 'status-pendente';
                                        break;
                                    case 'aprovado':
                                        $status_class = 'status-aprovado';
                                        break;
                                    case 'rejeitado':
                                        $status_class = 'status-rejeitado';
                                        break;
                                }
                                ?>
                                
                                <span class="article-status <?php echo $status_class; ?>">
                                    <?php 
                                    switch ($row['status']) {
                                        case 'pendente':
                                            echo 'Pendente';
                                            break;
                                        case 'aprovado':
                                            echo 'Aprovado';
                                            break;
                                        case 'rejeitado':
                                            echo 'Rejeitado';
                                            break;
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="article-actions">
                                <a href="visualizar-artigo.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-eye"></i> Visualizar
                                </a>
                                
                                <?php if ($row['status'] == 'pendente' || $row['status'] == 'rejeitado'): ?>
                                    <a href="editar-artigo.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="far fa-newspaper"></i>
                    </div>
                    <h2>Você ainda não enviou nenhum artigo</h2>
                    <p>Comece a compartilhar suas ideias e conhecimentos com a comunidade EntreLinhas.</p>
                    <a href="enviar-artigo.php" class="btn btn-primary mt-3">Enviar Meu Primeiro Artigo</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Incluir o rodapé comum -->
    <?php 
    $root_path = "..";
    include 'includes/footer.php'; 
    ?>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/user-menu.js"></script>
    <script src="../assets/js/header-nav.js"></script>
</body>
</html>

<?php
// Fechar conexão
mysqli_close($conn);
?>
