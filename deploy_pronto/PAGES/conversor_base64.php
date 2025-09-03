<?php
// Incluir funções Base64
require_once '../backend/funcoes_base64.php';

// Verificar se há um envio de arquivo
$resposta = [
    'sucesso' => false,
    'mensagem' => '',
    'imagem_base64' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagem'])) {
    $arquivo = $_FILES['imagem'];
    
    // Verificar se é uma imagem válida
    $tipo_permitido = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if ($arquivo['error'] !== 0) {
        $resposta['mensagem'] = 'Erro no upload do arquivo: ' . $arquivo['error'];
    } elseif (!in_array($arquivo['type'], $tipo_permitido)) {
        $resposta['mensagem'] = 'Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WebP.';
    } elseif ($arquivo['size'] > 2 * 1024 * 1024) { // Limitar a 2MB
        $resposta['mensagem'] = 'O arquivo é muito grande. Tamanho máximo: 2MB';
    } else {
        // Converter para Base64
        $base64 = base64_encode(file_get_contents($arquivo['tmp_name']));
        $data_uri = 'data:' . $arquivo['type'] . ';base64,' . $base64;
        
        $resposta['sucesso'] = true;
        $resposta['mensagem'] = 'Imagem convertida com sucesso!';
        $resposta['imagem_base64'] = $data_uri;
    }
    
    // Se for uma requisição AJAX, retornar JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($resposta);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversor de Imagens para Base64 - EntreLinhas</title>
    <meta name="description" content="Ferramenta para converter imagens para Base64">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .preview-container {
            margin-top: 20px;
            text-align: center;
        }
        .preview-container img {
            max-width: 100%;
            max-height: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        textarea {
            width: 100%;
            height: 100px;
            margin-top: 10px;
            font-family: monospace;
            padding: 10px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="../PAGES/index.html">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="../PAGES/index.html">Início</a></li>
                <li><a href="../PAGES/artigos.html">Artigos</a></li>
                <li><a href="../PAGES/escola.html">A Escola</a></li>
                <li><a href="../PAGES/contato.html">Contato</a></li>
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
        <div class="form-container fade-in">
            <h1 class="form-title">Conversor de Imagens para Base64</h1>
            
            <div id="alert-container" class="alert hidden"></div>
            
            <form id="converter-form" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="imagem">Selecione uma imagem (máx. 2MB)</label>
                    <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">Converter para Base64</button>
            </form>
            
            <div id="preview-container" class="preview-container hidden">
                <h2>Pré-visualização</h2>
                <img id="preview-image" src="" alt="Pré-visualização da imagem">
                <h3>Código Base64</h3>
                <textarea id="base64-code" readonly></textarea>
                <p>Para usar esta imagem em HTML: <code>&lt;img src="[código acima]"&gt;</code></p>
                <p>Para usar como background CSS: <code>background-image: url('[código acima]');</code></p>
                <button id="copy-button" class="btn btn-secondary">Copiar código Base64</button>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('converter-form');
        const fileInput = document.getElementById('imagem');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const base64Code = document.getElementById('base64-code');
        const copyButton = document.getElementById('copy-button');
        const alertContainer = document.getElementById('alert-container');
        
        // Preview imagem ao selecionar arquivo
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Verificar tamanho (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('O arquivo é muito grande. Tamanho máximo: 2MB', 'error');
                    this.value = '';
                    return;
                }
                
                // Verificar tipo
                if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
                    showAlert('Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WebP.', 'error');
                    this.value = '';
                    return;
                }
                
                // Preview com FileReader
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.classList.remove('hidden');
                    previewImage.src = e.target.result;
                    base64Code.value = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Copiar Base64 para clipboard
        copyButton.addEventListener('click', function() {
            base64Code.select();
            document.execCommand('copy');
            showAlert('Código copiado para a área de transferência!', 'success');
        });
        
        // Exibir alertas
        function showAlert(message, type) {
            alertContainer.textContent = message;
            alertContainer.className = `alert ${type}`;
            alertContainer.classList.remove('hidden');
            setTimeout(() => {
                alertContainer.classList.add('hidden');
            }, 3000);
        }
        
        // Enviar formulário via AJAX
        form.addEventListener('submit', function(e) {
            if (form.getAttribute('data-ajax') !== 'false') {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        showAlert(data.mensagem, 'success');
                        previewContainer.classList.remove('hidden');
                        previewImage.src = data.imagem_base64;
                        base64Code.value = data.imagem_base64;
                    } else {
                        showAlert(data.mensagem, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showAlert('Ocorreu um erro ao processar sua solicitação.', 'error');
                });
            }
        });
    });
    </script>
</body>
</html>
