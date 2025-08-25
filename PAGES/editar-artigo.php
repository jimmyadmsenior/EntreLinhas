<?php
// Página para editar artigo
session_start();

// Verificar se o usuário está logado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once "../backend/config.php";
require_once "../backend/artigos.php";
require_once "../backend/usuarios.php";

// Verificar se o ID do artigo foi fornecido
if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: perfil.php");
    exit;
}

$artigo_id = (int)$_GET["id"];

// Obter dados do artigo
$artigo = obterArtigo($conn, $artigo_id);

// Verificar se o artigo existe e se o usuário é o autor ou um administrador
if(!$artigo || ($artigo['usuario_id'] != $_SESSION["id"] && !isAdmin($conn, $_SESSION["id"]))){
    header("location: perfil.php");
    exit;
}

// Verificar se o artigo pode ser editado (apenas se estiver pendente)
$pode_editar = ($artigo['status'] == 'pendente' || isAdmin($conn, $_SESSION["id"]));

// Definir variáveis e inicializar com valores
$titulo = $artigo['titulo'];
$conteudo = $artigo['conteudo'];
$categoria = $artigo['categoria'];
$imagem_atual = $artigo['imagem'];

$titulo_err = $conteudo_err = $categoria_err = $imagem_err = "";
$message = "";

// Processar dados do formulário quando o formulário é enviado
if($_SERVER["REQUEST_METHOD"] == "POST" && $pode_editar){
 
    // Validar título
    if(empty(trim($_POST["titulo"]))){
        $titulo_err = "Por favor, informe um título para o artigo.";
    } else{
        $titulo = trim($_POST["titulo"]);
    }
    
    // Validar conteúdo
    if(empty(trim($_POST["conteudo"]))){
        $conteudo_err = "Por favor, escreva o conteúdo do artigo.";
    } else{
        $conteudo = trim($_POST["conteudo"]);
    }
    
    // Validar categoria
    if(empty(trim($_POST["categoria"]))){
        $categoria_err = "Por favor, selecione uma categoria.";
    } else{
        $categoria = trim($_POST["categoria"]);
    }
    
    // Processar upload de imagem (se houver)
    $imagem = $imagem_atual; // Manter a imagem atual por padrão
    
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK){
        $resultado_upload = processarUploadImagem($_FILES['imagem']);
        
        if($resultado_upload['status']){
            $imagem = $resultado_upload['caminho_relativo'];
            
            // Se havia uma imagem anterior, podemos excluí-la
            if(!empty($imagem_atual) && file_exists('../' . $imagem_atual)){
                unlink('../' . $imagem_atual);
            }
        } else {
            $imagem_err = $resultado_upload['mensagem'];
        }
    }
    
    // Verificar erros de entrada antes de atualizar no banco de dados
    if(empty($titulo_err) && empty($conteudo_err) && empty($categoria_err) && empty($imagem_err)){
        
        // Preparar os dados do artigo
        $artigo_atualizado = [
            'id' => $artigo_id,
            'titulo' => $titulo,
            'conteudo' => $conteudo,
            'categoria' => $categoria,
            'imagem' => $imagem
        ];
        
        // Editar o artigo
        $resultado = editarArtigo($conn, $artigo_atualizado, $_SESSION["id"]);
        
        if($resultado['status']){
            $message = $resultado['mensagem'];
            
            // Atualizar dados do artigo após edição
            $artigo = obterArtigo($conn, $artigo_id);
            $titulo = $artigo['titulo'];
            $conteudo = $artigo['conteudo'];
            $categoria = $artigo['categoria'];
            $imagem_atual = $artigo['imagem'];
        } else {
            $message = $resultado['mensagem'];
        }
    }
}

// Fechar conexão
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Artigo - EntreLinhas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user-menu.css">
    <link rel="stylesheet" href="../assets/css/alerts.css">
    <!-- Adicionando CKEditor (sem necessidade de API key) -->
    <script src="https://cdn.ckeditor.com/4.16.2/standard-all/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            CKEDITOR.replace('conteudo', {
                height: 400,
                removePlugins: 'elementspath',
                resize_enabled: true,
                extraPlugins: 'colorbutton,font,justify,uploadimage',
                toolbar: [
                    { name: 'document', items: [ 'Source' ] },
                    { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
                    { name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
                    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'TextColor', 'BGColor' ] },
                    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                    { name: 'links', items: [ 'Link', 'Unlink' ] },
                    { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] },
                    { name: 'tools', items: [ 'Maximize' ] }
                ]
            });
        });
        
        // Função para confirmar a exclusão do artigo
        function confirmarExclusao(artigo_id, titulo) {
            artigoIdParaExcluir = artigo_id;
            document.getElementById('artigo-titulo').textContent = titulo;
            document.getElementById('modal-exclusao').style.display = 'block';
        }
    </script>
    <style>
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            min-height: 300px;
            resize: vertical;
        }
        
        .form-group .help-block {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
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
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background-color: #e2f3fc;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box h3 {
            margin-top: 0;
            color: #0c5460;
        }
        
        .current-image {
            margin-bottom: 15px;
        }
        
        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
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
        
        .status-aprovado {
            background-color: #28a745;
        }
        
        .status-rejeitado {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
            .btn-secondary:hover {
            background-color: #444;
        }
        
        .btn-danger {
            display: inline-block;
            padding: 10px 15px;
            background-color: #d32f2f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-danger:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <h2>Editar Artigo</h2>
            
            <div class="article-status status-<?php echo $artigo['status']; ?>">
                Status: <?php echo ucfirst($artigo['status']); ?>
            </div>
            
            <?php 
            if(!empty($message)){
                if(strpos($message, "sucesso") !== false) {
                    echo '<div class="alert alert-success">' . $message . '</div>';
                } else {
                    echo '<div class="alert alert-danger">' . $message . '</div>';
                }
            }
            
            if(!$pode_editar): ?>
                <div class="alert alert-danger">
                    <p>Este artigo não pode ser editado pois já foi <?php echo $artigo['status']; ?>.</p>
                    <p>Apenas artigos com status "pendente" podem ser editados.</p>
                </div>
                <a href="perfil.php" class="btn-primary">Voltar ao Perfil</a>
                <a href="artigo.php?id=<?php echo $artigo_id; ?>" class="btn-secondary">Ver Artigo</a>
            <?php else: ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $artigo_id; ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Título *</label>
                    <input type="text" name="titulo" class="<?php echo (!empty($titulo_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $titulo; ?>" maxlength="100">
                    <span class="help-block"><?php echo $titulo_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Categoria *</label>
                    <select name="categoria" class="<?php echo (!empty($categoria_err)) ? 'is-invalid' : ''; ?>">
                        <option value="">Selecione uma categoria</option>
                        <option value="Notícia" <?php echo $categoria == "Notícia" ? 'selected' : ''; ?>>Notícia</option>
                        <option value="Esporte" <?php echo $categoria == "Esporte" ? 'selected' : ''; ?>>Esporte</option>
                        <option value="Cultura" <?php echo $categoria == "Cultura" ? 'selected' : ''; ?>>Cultura</option>
                        <option value="Tecnologia" <?php echo $categoria == "Tecnologia" ? 'selected' : ''; ?>>Tecnologia</option>
                        <option value="Educação" <?php echo $categoria == "Educação" ? 'selected' : ''; ?>>Educação</option>
                        <option value="Eventos" <?php echo $categoria == "Eventos" ? 'selected' : ''; ?>>Eventos</option>
                        <option value="Entrevista" <?php echo $categoria == "Entrevista" ? 'selected' : ''; ?>>Entrevista</option>
                        <option value="Opinião" <?php echo $categoria == "Opinião" ? 'selected' : ''; ?>>Opinião</option>
                    </select>
                    <span class="help-block"><?php echo $categoria_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Imagem de Capa</label>
                    <?php if(!empty($imagem_atual)): ?>
                        <div class="current-image">
                            <p>Imagem atual:</p>
                            <img src="<?php echo '../' . htmlspecialchars($imagem_atual); ?>" alt="Imagem atual">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="imagem" accept="image/jpeg, image/png, image/gif">
                    <small>Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB. Deixe em branco para manter a imagem atual.</small>
                    <span class="help-block"><?php echo $imagem_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Conteúdo *</label>
                    <textarea name="conteudo" id="conteudo" class="<?php echo (!empty($conteudo_err)) ? 'is-invalid' : ''; ?>"><?php echo $conteudo; ?></textarea>
                    <span class="help-block"><?php echo $conteudo_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn-primary" value="Salvar Alterações">
                    <a href="artigo.php?id=<?php echo $artigo_id; ?>" class="btn-secondary">Cancelar</a>
                    <button type="button" class="btn-danger" onclick="confirmarExclusao(<?php echo $artigo_id; ?>, '<?php echo addslashes(htmlspecialchars($titulo)); ?>')" style="margin-left: 10px;">Excluir Artigo</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div id="modal-exclusao" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color:var(--card-bg-light, #fff); margin:15% auto; padding:20px; border-radius:8px; width:90%; max-width:500px; box-shadow:0 4px 8px rgba(0,0,0,0.2); color:var(--text-dark, #333);">
            <h3 style="color: #000000;">Confirmar Exclusão</h3>
            <p>Você tem certeza que deseja excluir o artigo "<span id="artigo-titulo"></span>"?</p>
            <p>Esta ação não pode ser desfeita.</p>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button onclick="fecharModal()" class="btn-secondary">Cancelar</button>
                <button id="btn-confirmar-exclusao" onclick="excluirArtigo()" class="btn-danger">Excluir</button>
            </div>
        </div>
    </div>
    
    <script>
        let artigoIdParaExcluir = null;
        
        function fecharModal() {
            document.getElementById('modal-exclusao').style.display = 'none';
        }
        
        function excluirArtigo() {
            if (artigoIdParaExcluir) {
                window.location.href = '../backend/processar_exclusao.php?id=' + artigoIdParaExcluir;
            }
        }
        
        // Fechar o modal se o usuário clicar fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('modal-exclusao');
            if (event.target == modal) {
                fecharModal();
            }
        }
    </script>
</body>
</html>
