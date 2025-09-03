<?php
// Página de exibição de artigo
session_start();

// Incluir arquivo de configuração e auxiliares
require_once "../backend/config_pdo.php";
require_once "../backend/artigos_pdo.php";
require_once "../backend/comentarios_pdo.php";
require_once "../backend/usuario_helper_pdo.php";

// Obter a foto de perfil do usuário logado (se existir)
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $foto_perfil = obter_foto_perfil_pdo($pdo, $_SESSION["id"]);
}

// Verificar se o ID do artigo foi fornecido
if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: ../index.php");
    exit;
}

$artigo_id = (int)$_GET["id"];

// Obter dados do artigo
$artigo = obterArtigo_pdo($pdo, $artigo_id);

// Verificar se o artigo existe e está aprovado (ou se o usuário é o autor ou admin)
$usuario_pode_ver = false;

// Garantir que o artigo foi encontrado
if($artigo) {
    // Se o usuário estiver logado, verificar permissões
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        // Verificar se é o autor do artigo
        if($artigo['id_usuario'] == $_SESSION["id"]) {
            $usuario_pode_ver = true;
        } 
        // Ou se é um administrador
        else if(isAdmin_pdo($pdo, $_SESSION["id"])) {
            $usuario_pode_ver = true;
        }
    }
    
    // Se o artigo estiver aprovado, qualquer pessoa pode ver
    if($artigo['status'] === 'aprovado') {
        $usuario_pode_ver = true;
    }
}

// Se não pode ver o artigo, redirecionar
if(!$artigo || !$usuario_pode_ver) {
    header("location: ../index.php");
    exit;
}

// Obter comentários do artigo
$comentarios = listarComentarios_pdo($pdo, $artigo_id);

// Processar envio de comentário
$comment_message = "";
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["comentario"]) && isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    $conteudo = trim($_POST["comentario"]);
    
    if(!empty($conteudo)){
        $comentario = [
            'usuario_id' => $_SESSION["id"],
            'artigo_id' => $artigo_id,
            'conteudo' => $conteudo
        ];
        
        // Desabilitado: $resultado = adicionarComentario_pdo($pdo, $comentario);
        $resultado['status'] = false;
        
        if($resultado['status']){
            // Comentário adicionado com sucesso
            header("location: artigo.php?id=" . $artigo_id . "#comentarios");
            exit;
        } else {
            $comment_message = $resultado['mensagem'];
        }
    } else {
        $comment_message = "O comentário não pode estar vazio.";
    }
}

// Formatar a data
$data_formatada = date('d/m/Y', strtotime($artigo['data_criacao']));

// Não fechar a conexão aqui, ela será usada no header.php
// A conexão será fechada no final do arquivo
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artigo['titulo']); ?> - EntreLinhas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/dark-mode-fix.js" defer></script>
    <script src="../assets/js/no-comments-fix.js" defer></script>
    <style>
        /* Estilos críticos para garantir visibilidade em modo escuro */
        .dark-mode .article-header,
        .dark-mode .article-content,
        .dark-mode .comments-section {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
        }
        
        /* Garantir que todo o conteúdo seja visível no modo escuro */
        .dark-mode .article-content * {
            color: #ffffff !important;
        }
        
        .dark-mode .article-content a {
            color: #add8e6 !important;
        }
        
        /* Estilos para os comentários */
        .dark-mode .comment-header,
        .dark-mode .comment-content,
        .dark-mode .comments-title {
            color: #ffffff !important;
        }
        
        /* Status do artigo */
        .dark-mode .article-title {
            color: #ffffff !important;
        }
        
        /* Estilos específicos para a seção de nenhum comentário */
        .dark-mode .no-comments {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border: 1px solid #444 !important;
        }
        
        .dark-mode .no-comments p {
            color: #ffffff !important;
        }
        
        /* Campo de texto para comentário */
        .dark-mode .comment-form textarea {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border-color: #444 !important;
        }
        
        /* Botão de enviar comentário */
        .dark-mode .btn-primary {
            background-color: #444 !important;
            color: #ffffff !important;
        }
        
        .dark-mode .btn-primary:hover {
            background-color: #666 !important;
        }
    </style>
    
    <!-- CSS Inline crítico para garantir visibilidade imediata no modo escuro -->
    <script>
        // Verificar se o modo escuro está ativo imediatamente
        if (localStorage.getItem('theme') === 'dark' || 
            (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && 
            localStorage.getItem('theme') !== 'light')) {
            
            // Criar e injetar estilo inline crítico
            const style = document.createElement('style');
            style.innerHTML = `
                .article-header, .article-content, .comments-section {
                    background-color: #1e1e1e !important;
                    color: #ffffff !important;
                }
                .article-content * {
                    color: #ffffff !important;
                }
                .article-content a {
                    color: #add8e6 !important;
                }
                .article-title, .comments-title, .comment-author, .comment-content {
                    color: #ffffff !important;
                }
                /* Estilo específico para área de "Nenhum comentário ainda" */
                .no-comments {
                    background-color: #2d2d2d !important;
                    color: #ffffff !important;
                }
                .no-comments p {
                    color: #ffffff !important;
                }
                /* Campo de texto e botão */
                .comment-form textarea {
                    background-color: #2d2d2d !important;
                    color: #ffffff !important;
                    border-color: #444 !important;
                }
                .btn-primary {
                    background-color: #444 !important;
                    color: #ffffff !important;
                }
            `;
            document.head.appendChild(style);

            // Função para aplicar estilos assim que os elementos estiverem disponíveis
            function ensureNoCommentsVisibility() {
                // Tentar selecionar o elemento .no-comments
                const noComments = document.querySelector('.no-comments');
                if (noComments) {
                    // Aplicar estilos diretamente
                    noComments.style.backgroundColor = '#2d2d2d';
                    noComments.style.color = '#ffffff';
                    
                    // Para todos os elementos dentro de .no-comments
                    const childElements = noComments.querySelectorAll('*');
                    childElements.forEach(function(el) {
                        el.style.color = '#ffffff';
                    });
                }
            }
            
            // Executar imediatamente
            ensureNoCommentsVisibility();
            
            // E também após um pequeno atraso para garantir que o DOM esteja carregado
            setTimeout(ensureNoCommentsVisibility, 100);
        }
    </script>
    <style>
        .container {
            max-width: 800px;
            margin: 30px auto;
        }
        
        .article-header {
            background: var(--card-bg-light);
            border-radius: 8px;
            box-shadow: var(--shadow-light);
            padding: 20px;
            margin-bottom: 20px;
            transition: background-color var(--transition-speed), color var(--transition-speed), box-shadow var(--transition-speed);
        }
        
        .article-title {
            font-size: 32px;
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--primary);
            transition: color var(--transition-speed);
        }
        
        .article-meta {
            display: flex;
            justify-content: space-between;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-light);
            transition: color var(--transition-speed), border-color var(--transition-speed);
        }
        
        .article-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .article-content {
            background: var(--card-bg-light);
            border-radius: 8px;
            box-shadow: var(--shadow-light);
            padding: 20px;
            line-height: 1.6;
            margin-bottom: 20px;
            transition: background-color var(--transition-speed), color var(--transition-speed), box-shadow var(--transition-speed);
        }
        
        body.dark-mode .article-content {
            background: var(--card-bg-dark);
            color: var(--text-light);
            box-shadow: var(--shadow-dark);
        }
        
        .article-content p {
            margin-bottom: 15px;
            transition: color var(--transition-speed);
        }
        
        .comments-section {
            background: var(--card-bg-light);
            border-radius: 8px;
            box-shadow: var(--shadow-light);
            padding: 20px;
            transition: background-color var(--transition-speed), color var(--transition-speed), box-shadow var(--transition-speed);
        }
        
        .comments-title {
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-light);
            transition: color var(--transition-speed), border-color var(--transition-speed);
        }
        
        .comment-form {
            margin-bottom: 30px;
        }
        
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-light);
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
            background-color: var(--bg-light);
            color: var(--text-dark);
            transition: background-color var(--transition-speed), color var(--transition-speed), border-color var(--transition-speed);
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--text-light);
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color var(--transition-speed);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
        }
        
        .login-message {
            background-color: var(--bg-light);
            border: 1px solid var(--border-light);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            transition: background-color var(--transition-speed), color var(--transition-speed), border-color var(--transition-speed);
        }
        
        .login-message a {
            color: var(--primary);
            font-weight: bold;
            text-decoration: none;
            transition: color var(--transition-speed);
        }
        
        .login-message a:hover {
            text-decoration: underline;
        }
        
        .comment-list {
            margin-top: 20px;
        }
        
        .comment-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-light);
            transition: border-color var(--transition-speed);
        }
        
        .comment-item:last-child {
            border-bottom: none;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .comment-author {
            font-weight: bold;
            color: var(--primary);
            transition: color var(--transition-speed);
        }
        
        .comment-date {
            color: #6c757d;
            font-size: 14px;
            transition: color var(--transition-speed);
        }
        
        .comment-content {
            line-height: 1.5;
            transition: color var(--transition-speed);
        }
        
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .no-comments {
            padding: 15px;
            background-color: var(--bg-light);
            border-radius: 4px;
            text-align: center;
            margin-top: 20px;
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }
        
        .article-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
            margin-bottom: 15px;
        }
        
        .status-pendente {
            background-color: #ffc107;
        }
        
        .status-rejeitado {
            background-color: #dc3545;
        }
        
        .rejection-reason {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
        </nav>
    </header>
    
    <main>
        <div class="container">
            <div class="article-header">
                <h1 class="article-title"><?php echo htmlspecialchars($artigo['titulo']); ?></h1>
                
                <?php if($artigo['status'] !== 'aprovado'): ?>
                    <div class="article-status status-<?php echo $artigo['status']; ?>">
                        Status: <?php echo ucfirst($artigo['status']); ?>
                    </div>
                    
                    <?php if($artigo['status'] === 'rejeitado' && !empty($artigo['motivo_rejeicao'])): ?>
                        <div class="rejection-reason">
                            <strong>Motivo da rejeição:</strong> <?php echo htmlspecialchars($artigo['motivo_rejeicao']); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="article-meta">
                    <div>
                        <span>Por: <?php echo htmlspecialchars($artigo['nome_autor'] ?? 'Autor desconhecido'); ?></span> |
                        <span>Categoria: <?php echo htmlspecialchars($artigo['categoria']); ?></span>
                    </div>
                    <div>
                        <span>Data: <?php echo $data_formatada; ?></span>
                    </div>
                </div>
                
                <?php if(!empty($artigo['imagem'])): ?>
                    <img src="<?php echo htmlspecialchars($artigo['imagem']); ?>" alt="<?php echo htmlspecialchars($artigo['titulo']); ?>" class="article-image">
                <?php endif; ?>
            </div>
            
            <div class="article-content">
                <?php echo $artigo['conteudo']; ?>
            </div>
            
            <div id="comentarios" class="comments-section">
                <h2 class="comments-title">Comentários (<?php echo count($comentarios); ?>)</h2>
                
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <div class="comment-form">
                        <?php if(!empty($comment_message)): ?>
                            <div class="alert alert-danger"><?php echo $comment_message; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $artigo_id . '#comentarios'; ?>">
                            <textarea name="comentario" placeholder="Escreva seu comentário..." required></textarea>
                            <button type="submit" class="btn-primary">Enviar Comentário</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-message">
                        <p>Para comentar, <a href="login.php">faça login</a> ou <a href="registro.php">crie uma conta</a>.</p>
                    </div>
                <?php endif; ?>
                
                <div class="comment-list">
                    <?php if(count($comentarios) > 0): ?>
                        <?php foreach($comentarios as $comentario): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <span class="comment-author"><?php echo htmlspecialchars($comentario['nome_usuario'] ?? 'Usuário'); ?></span>
                                    <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comentario['data_criacao'] ?? $comentario['data_comentario'] ?? date('Y-m-d H:i:s'))); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo htmlspecialchars($comentario['conteudo']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-comments" style="color: var(--text-dark); background-color: var(--bg-light);" data-dark-mode-color="#ffffff" data-dark-mode-bg="#2d2d2d">
                            <p style="color: inherit;">Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                        </div>
                        <script>
                            // Script inline para garantir visibilidade no modo escuro
                            if (document.body.classList.contains('dark-mode') || localStorage.getItem('theme') === 'dark') {
                                const noCommentsDiv = document.querySelector('.no-comments');
                                if (noCommentsDiv) {
                                    noCommentsDiv.style.backgroundColor = '#2d2d2d';
                                    noCommentsDiv.style.color = '#ffffff';
                                    noCommentsDiv.querySelector('p').style.color = '#ffffff';
                                }
                            }
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
// Não precisamos fechar a conexão PDO explicitamente, ela será fechada quando a variável sair de escopo
// $pdo = null;
?>
