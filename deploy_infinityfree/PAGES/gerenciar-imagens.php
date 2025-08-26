<?php
session_start();
require_once '../backend/config.php';
require_once '../backend/artigos.php';
require_once '../backend/imagens_artigos.php';

// Verificar se o usuário está logado e é administrador ou autor
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Verificar se o ID do artigo foi fornecido
if (!isset($_GET['artigo_id']) || empty($_GET['artigo_id'])) {
    header("Location: index.php");
    exit;
}

$artigo_id = intval($_GET['artigo_id']);
$artigo = obterArtigo($conn, $artigo_id);

// Verificar se o artigo existe e se o usuário tem permissão
if (!$artigo) {
    header("Location: index.php");
    exit;
}

// Verificar se é um administrador ou o autor do artigo
$is_admin = isset($_SESSION['email']) && $_SESSION['email'] === ADMIN_EMAIL;
$is_autor = $_SESSION['id'] === $artigo['id_usuario'];

if (!$is_admin && !$is_autor) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Imagens - EntreLinhas</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .image-container {
            position: relative;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .image-container img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            padding: 15px;
            color: white;
        }
        .image-actions {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .image-actions button {
            margin-left: 5px;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        .image-actions button:hover {
            opacity: 1;
        }
        .drag-area {
            border: 2px dashed #ccc;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            cursor: pointer;
        }
        .drag-area.active {
            border-color: #0d6efd;
            background-color: #e6f0ff;
        }
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview {
            position: relative;
            width: 150px;
            height: 150px;
            border: 1px solid #ddd;
            overflow: hidden;
            margin-bottom: 15px;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-preview {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255,0,0,0.7);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            text-align: center;
            line-height: 25px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <h1>Gerenciar Imagens</h1>
        <p>Artigo: <strong><?php echo htmlspecialchars($artigo['titulo']); ?></strong></p>
        
        <?php if (isset($_SESSION['mensagem']) && isset($_SESSION['tipo_mensagem'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensagem']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['mensagem']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Adicionar Novas Imagens</h5>
            </div>
            <div class="card-body">
                <form action="../backend/processar_imagem.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="artigo_id" value="<?php echo $artigo_id; ?>">
                    <input type="hidden" name="acao" value="adicionar">
                    
                    <div class="drag-area" id="dragArea">
                        <div class="icon"><i class="fas fa-cloud-upload-alt fa-3x"></i></div>
                        <span>Arraste e solte as imagens aqui</span>
                        <span>ou</span>
                        <button type="button" class="btn btn-primary btn-sm mt-2">Selecionar Arquivos</button>
                        <input type="file" id="fileInput" name="imagens[]" multiple accept="image/*" hidden>
                    </div>
                    
                    <div class="preview-container" id="previewContainer"></div>
                    <div class="error-message text-danger" id="imageError"></div>
                    
                    <button type="submit" class="btn btn-primary">Adicionar Imagens</button>
                </form>
            </div>
        </div>
        
        <h2 class="mt-4 mb-3">Imagens Atuais</h2>
        
        <?php if (empty($artigo['imagens'])): ?>
        <div class="alert alert-info">
            Este artigo não possui imagens.
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($artigo['imagens'] as $imagem): ?>
            <div class="col-md-4">
                <div class="image-container">
                    <img src="../<?php echo htmlspecialchars($imagem['caminho']); ?>" alt="Imagem do artigo">
                    
                    <div class="image-overlay">
                        <form action="../backend/processar_imagem.php" method="post" class="mb-2">
                            <input type="hidden" name="imagem_id" value="<?php echo $imagem['id']; ?>">
                            <input type="hidden" name="artigo_id" value="<?php echo $artigo_id; ?>">
                            <input type="hidden" name="acao" value="atualizar_descricao">
                            <div class="input-group">
                                <input type="text" name="descricao" class="form-control" placeholder="Descrição da imagem" value="<?php echo htmlspecialchars($imagem['descricao']); ?>">
                                <button type="submit" class="btn btn-outline-light"><i class="fas fa-save"></i></button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="image-actions">
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $imagem['id']; ?>" data-artigo-id="<?php echo $artigo_id; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="<?php echo $is_admin ? 'admin.php' : 'meus-artigos.php'; ?>" class="btn btn-secondary">Voltar</a>
            <a href="artigo.php?id=<?php echo $artigo_id; ?>" class="btn btn-primary">Ver Artigo</a>
        </div>
    </div>
    
    <!-- Modal de confirmação de exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir esta imagem?</p>
                    <p class="text-danger"><strong>Atenção:</strong> Esta ação não pode ser desfeita!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="../backend/processar_imagem.php" method="post">
                        <input type="hidden" name="imagem_id" id="deleteImageId">
                        <input type="hidden" name="artigo_id" id="deleteArtigoId">
                        <input type="hidden" name="acao" value="remover">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dragArea = document.getElementById('dragArea');
            const fileInput = document.getElementById('fileInput');
            const previewContainer = document.getElementById('previewContainer');
            const imageError = document.getElementById('imageError');
            
            // Clique no botão de seleção de arquivos
            dragArea.querySelector('button').addEventListener('click', () => {
                fileInput.click();
            });
            
            // Quando os arquivos são selecionados
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });
            
            // Eventos de arrastar e soltar
            dragArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                dragArea.classList.add('active');
            });
            
            dragArea.addEventListener('dragleave', () => {
                dragArea.classList.remove('active');
            });
            
            dragArea.addEventListener('drop', (e) => {
                e.preventDefault();
                dragArea.classList.remove('active');
                handleFiles(e.dataTransfer.files);
            });
            
            // Processar arquivos de imagem
            function handleFiles(files) {
                imageError.textContent = '';
                
                // Verificar se já existem muitas imagens
                if (previewContainer.children.length + files.length > 10) {
                    imageError.textContent = 'Você pode adicionar no máximo 10 imagens por vez.';
                    return;
                }
                
                for (let file of files) {
                    // Verificar se é uma imagem
                    if (!file.type.match('image.*')) {
                        imageError.textContent = 'Por favor, selecione apenas arquivos de imagem.';
                        continue;
                    }
                    
                    // Verificar tamanho (máximo de 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        imageError.textContent = 'O tamanho máximo permitido por imagem é 2MB.';
                        continue;
                    }
                    
                    // Criar container de preview
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview';
                    
                    // Criar botão de remover
                    const removeButton = document.createElement('div');
                    removeButton.className = 'remove-preview';
                    removeButton.innerHTML = '&times;';
                    removeButton.addEventListener('click', function() {
                        previewContainer.removeChild(previewDiv);
                        updateFileInput();
                    });
                    
                    // Criar imagem de preview
                    const img = document.createElement('img');
                    
                    // Criar FileReader para ler a imagem
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                    
                    // Adicionar elementos ao DOM
                    previewDiv.appendChild(img);
                    previewDiv.appendChild(removeButton);
                    previewDiv.file = file;
                    previewContainer.appendChild(previewDiv);
                }
                
                // Atualizar o input de arquivo
                updateFileInput();
            }
            
            // Atualizar o input de arquivo com as imagens atuais
            function updateFileInput() {
                // Verificar se há imagens selecionadas
                if (previewContainer.children.length === 0) {
                    fileInput.value = '';
                    return;
                }
                
                // Criar um novo objeto DataTransfer
                const dataTransfer = new DataTransfer();
                
                // Adicionar cada arquivo
                for (let preview of previewContainer.children) {
                    if (preview.file) {
                        dataTransfer.items.add(preview.file);
                    }
                }
                
                // Atualizar o input
                fileInput.files = dataTransfer.files;
            }
            
            // Manipuladores para o modal de exclusão
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const imageId = this.getAttribute('data-id');
                    const artigoId = this.getAttribute('data-artigo-id');
                    
                    document.getElementById('deleteImageId').value = imageId;
                    document.getElementById('deleteArtigoId').value = artigoId;
                    
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
                });
            });
            
            // Auto-hide para as mensagens de alerta
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
