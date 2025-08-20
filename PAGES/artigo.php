<?php
// Página de exibição de artigo
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";
require_once "../backend/artigos.php";
require_once "../backend/comentarios.php";

// Verificar se o ID do artigo foi fornecido
if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: ../index.php");
    exit;
}

$artigo_id = (int)$_GET["id"];

// Obter dados do artigo
$artigo = obterArtigo($conn, $artigo_id);

// Verificar se o artigo existe e está aprovado (ou se o usuário é o autor ou admin)
$usuario_pode_ver = false;

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    // Verificar se é o autor do artigo ou um administrador
    if(isset($artigo['usuario_id']) && ($artigo['usuario_id'] == $_SESSION["id"] || isAdmin($conn, $_SESSION["id"]))){
        $usuario_pode_ver = true;
    }
}

if(!$artigo || ($artigo['status'] !== 'aprovado' && !$usuario_pode_ver)){
    header("location: ../index.php");
    exit;
}

// Obter comentários do artigo
$comentarios = listarComentarios($conn, $artigo_id);

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
        
        $resultado = adicionarComentario($conn, $comentario);
        
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

// Fechar conexão
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artigo['titulo']); ?> - EntreLinhas</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 30px auto;
        }
        
        .article-header {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .article-title {
            font-size: 32px;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .article-meta {
            display: flex;
            justify-content: space-between;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .article-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .article-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .article-content p {
            margin-bottom: 15px;
        }
        
        .comments-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .comments-title {
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .comment-form {
            margin-bottom: 30px;
        }
        
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }
        
        .btn-primary {
            background-color: #000;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #333;
        }
        
        .login-message {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .login-message a {
            color: #000;
            font-weight: bold;
            text-decoration: none;
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
            border-bottom: 1px solid #eee;
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
        }
        
        .comment-date {
            color: #6c757d;
            font-size: 14px;
        }
        
        .comment-content {
            line-height: 1.5;
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
            background-color: #f9f9f9;
            border-radius: 4px;
            text-align: center;
            margin-top: 20px;
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
    <header>
        <div class="logo">
            <h1>EntreLinhas</h1>
            <p>O jornal da SESI Salto</p>
        </div>
        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="escola.html">Nossa Escola</a></li>
                <li><a href="conhecimentos.html">Conhecimentos</a></li>
                <li><a href="conselho-classe.html">Conselho de Classe</a></li>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <li><a href="perfil.php">Meu Perfil</a></li>
                    <li><a href="enviar-artigo.php">Enviar Artigo</a></li>
                    <?php if(isAdmin($conn, $_SESSION["id"])): ?>
                        <li><a href="admin.php">Administração</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="registro.php">Cadastre-se</a></li>
                <?php endif; ?>
            </ul>
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
                        <span>Por: <?php echo htmlspecialchars($artigo['nome_usuario']); ?></span> |
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
                                    <span class="comment-author"><?php echo htmlspecialchars($comentario['nome_usuario']); ?></span>
                                    <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comentario['data_comentario'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo htmlspecialchars($comentario['conteudo']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-comments">
                            <p>Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?php echo date("Y"); ?> EntreLinhas - O jornal da SESI Salto. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
