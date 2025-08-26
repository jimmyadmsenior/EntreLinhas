<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado, senão redirecionar para a página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Incluir arquivo de configuração e helper de usuário
require_once "../backend/config.php";
require_once "../backend/db_connection_fix.php"; // Incluir o fix de conexão
require_once "../backend/usuario_helper.php";

// Obter a foto de perfil do usuário
$foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);

// Definir variáveis e inicializar com valores vazios
$titulo = $conteudo = $categoria = "";
$titulo_err = $conteudo_err = $categoria_err = $imagem_err = "";
$upload_status = "";

// Processar dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validar título
    if (empty(trim($_POST["titulo"]))) {
        $titulo_err = "Por favor, insira um título para o artigo.";
    } elseif (strlen(trim($_POST["titulo"])) > 255) {
        $titulo_err = "O título não pode ter mais de 255 caracteres.";
    } else {
        $titulo = trim($_POST["titulo"]);
    }
    
    // Validar conteúdo
    if (empty(trim($_POST["conteudo"]))) {
        $conteudo_err = "Por favor, insira o conteúdo do artigo.";
    } else {
        $conteudo = trim($_POST["conteudo"]);
    }
    
    // Validar categoria
    if (empty(trim($_POST["categoria"]))) {
        $categoria_err = "Por favor, selecione uma categoria.";
    } else {
        $categoria = trim($_POST["categoria"]);
    }
    
    // Processar upload de imagem se existir
    $imagem_path = "";
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['imagem']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verificar extensão do arquivo
        if (!in_array(strtolower($filetype), $allowed)) {
            $imagem_err = "Tipo de arquivo não permitido. Apenas JPG, JPEG, PNG e GIF são aceitos.";
        } else {
            // Gerar nome único para o arquivo
            $new_filename = uniqid() . '.' . $filetype;
            $upload_dir = "../uploads/artigos/";
            
            // Criar diretório se não existir
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $upload_path = $upload_dir . $new_filename;
            
            // Mover arquivo para o diretório de uploads
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                $imagem_path = $upload_path;
                $upload_status = "Imagem enviada com sucesso!";
            } else {
                $imagem_err = "Erro ao fazer upload da imagem.";
            }
        }
    }
    
    // Verificar erros antes de inserir no banco de dados
    if (empty($titulo_err) && empty($conteudo_err) && empty($categoria_err) && empty($imagem_err)) {
        
        // Preparar declaração de inserção
        $sql = "INSERT INTO artigos (titulo, conteudo, id_usuario, categoria, imagem, status) VALUES (?, ?, ?, ?, ?, 'pendente')";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "sssss", $param_titulo, $param_conteudo, $param_usuario_id, $param_categoria, $param_imagem);
            
            // Definir parâmetros
            $param_titulo = $titulo;
            $param_conteudo = $conteudo;
            $param_usuario_id = $_SESSION["id"];
            $param_categoria = $categoria;
            $param_imagem = $imagem_path;
            
            // Tentar executar a declaração preparada
            if (mysqli_stmt_execute($stmt)) {
                // Redirecionar para a página de sucesso
                header("location: envio-sucesso.html");
                exit();
            } else {
                echo "Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            
            // Fechar declaração
            mysqli_stmt_close($stmt);
        }
    }
    
    // Fechar conexão
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Artigo - EntreLinhas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user-menu.css">
    <link rel="stylesheet" href="../assets/css/alerts.css">
    <!-- Substituindo TinyMCE pelo CKEditor (sem necessidade de API key) -->
    <script src="https://cdn.ckeditor.com/4.16.2/standard-all/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            CKEDITOR.replace('editor', {
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
    </script>
</head>
<body>
    <?php include "includes/header.php"; ?>

    <main class="container my-5">
        <h1 class="mb-4">Enviar Novo Artigo</h1>
        
        <?php if (!empty($upload_status)): ?>
            <div class="alert alert-success"><?php echo $upload_status; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" class="form-control <?php echo (!empty($titulo_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $titulo; ?>">
                <span class="invalid-feedback"><?php echo $titulo_err; ?></span>
            </div>
            
            <div class="form-group mb-3">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria" class="form-control <?php echo (!empty($categoria_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Selecione uma categoria</option>
                    <option value="Educação" <?php echo ($categoria == "Educação") ? "selected" : ""; ?>>Educação</option>
                    <option value="Cultura" <?php echo ($categoria == "Cultura") ? "selected" : ""; ?>>Cultura</option>
                    <option value="Esporte" <?php echo ($categoria == "Esporte") ? "selected" : ""; ?>>Esporte</option>
                    <option value="Tecnologia" <?php echo ($categoria == "Tecnologia") ? "selected" : ""; ?>>Tecnologia</option>
                    <option value="Comunidade" <?php echo ($categoria == "Comunidade") ? "selected" : ""; ?>>Comunidade</option>
                    <option value="Eventos" <?php echo ($categoria == "Eventos") ? "selected" : ""; ?>>Eventos</option>
                </select>
                <span class="invalid-feedback"><?php echo $categoria_err; ?></span>
            </div>
            
            <div class="form-group mb-3">
                <label for="imagem">Imagem de Capa (opcional)</label>
                <input type="file" id="imagem" name="imagem" class="form-control <?php echo (!empty($imagem_err)) ? 'is-invalid' : ''; ?>">
                <small class="form-text text-muted">Formatos aceitos: JPG, PNG, GIF</small>
                <span class="invalid-feedback"><?php echo $imagem_err; ?></span>
            </div>
            
            <div class="form-group mb-3">
                <label for="editor">Conteúdo</label>
                <textarea id="editor" name="conteudo" class="form-control <?php echo (!empty($conteudo_err)) ? 'is-invalid' : ''; ?>"><?php echo $conteudo; ?></textarea>
                <span class="invalid-feedback"><?php echo $conteudo_err; ?></span>
            </div>
            
            <div class="form-group mb-3">
                <button type="submit" class="btn btn-primary">Enviar Artigo</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <?php include "includes/footer.php"; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
