<?php
// Versão de diagnóstico do formulário de envio de artigos
// Esta página usa o script de diagnóstico para processar o formulário

// Exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once '../backend/config.php';

// Verificar se há mensagens na sessão
$mensagem = '';
$tipo_mensagem = '';

if (isset($_SESSION['mensagem']) && isset($_SESSION['tipo_mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo_mensagem = $_SESSION['tipo_mensagem'];
    
    // Limpar mensagens da sessão
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Artigo (Diagnóstico) - EntreLinhas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css">
    <style>
        .artigo-form {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .debug-info {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="debug-info">
            <h2>Modo de Diagnóstico - Envio de Artigo</h2>
            <p>Esta é uma versão de diagnóstico do formulário de envio de artigos. Ela usa um script especial para identificar problemas no processamento.</p>
            <p><strong>Informações da Sessão:</strong></p>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
        
        <div class="artigo-form">
            <h1 class="mb-4">Enviar Artigo</h1>
            
            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="../backend/debug_processar_artigo.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título do Artigo</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                </div>
                
                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoria</label>
                    <select class="form-select" id="categoria" name="categoria" required>
                        <option value="">Selecione uma categoria</option>
                        <option value="noticia">Notícia</option>
                        <option value="opiniao">Opinião</option>
                        <option value="entrevista">Entrevista</option>
                        <option value="cultural">Cultural</option>
                        <option value="educacao">Educação</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="conteudo" class="form-label">Conteúdo do Artigo</label>
                    <textarea class="form-control" id="conteudo" name="conteudo" rows="10" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="imagem" class="form-label">Imagem Principal</label>
                    <input type="file" class="form-control" id="imagem" name="imagem">
                    <div class="form-text">Formatos aceitos: JPG, JPEG, PNG, GIF</div>
                </div>
                
                <div class="mb-3">
                    <label for="imagens" class="form-label">Imagens Adicionais (opcional)</label>
                    <input type="file" class="form-control" id="imagens" name="imagens[]" multiple>
                    <div class="form-text">Você pode selecionar múltiplas imagens</div>
                </div>
                
                <input type="hidden" name="acao" value="enviar">
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="javascript:history.back()" class="btn btn-secondary me-md-2">Voltar</a>
                    <button type="submit" class="btn btn-primary">Enviar Artigo</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#conteudo').summernote({
                placeholder: 'Escreva seu artigo aqui...',
                tabsize: 2,
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html>
