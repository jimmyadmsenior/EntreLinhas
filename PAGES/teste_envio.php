<?php
// Versão simplificada para identificar o problema
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Envio - EntreLinhas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>EntreLinhas</h1>
        <p>Teste de formulário de envio de artigos</p>
    </header>

    <main class="container">
        <h2>Enviar Novo Artigo</h2>
        
        <form action="../backend/processar_artigo.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="enviar">
            
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria" class="form-control">
                    <option value="">Selecione uma categoria</option>
                    <option value="Educação">Educação</option>
                    <option value="Cultura">Cultura</option>
                    <option value="Esporte">Esporte</option>
                    <option value="Tecnologia">Tecnologia</option>
                    <option value="Comunidade">Comunidade</option>
                    <option value="Eventos">Eventos</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="conteudo">Conteúdo</label>
                <textarea id="conteudo" name="conteudo" rows="10" class="form-control"></textarea>
            </div>
            
            <div class="form-group">
                <label for="imagem">Imagem (opcional)</label>
                <input type="file" id="imagem" name="imagem" class="form-control">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Enviar Artigo</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> EntreLinhas - Todos os direitos reservados</p>
    </footer>
</body>
</html>
