document.addEventListener('DOMContentLoaded', function() {
    // Função para criar o visualizador de imagens
    function createImageViewer() {
        // Criar os elementos do visualizador
        const viewer = document.createElement('div');
        viewer.className = 'image-viewer';
        viewer.style.display = 'none';
        
        const viewerContent = document.createElement('div');
        viewerContent.className = 'image-viewer-content';
        
        const closeBtn = document.createElement('span');
        closeBtn.className = 'close-btn';
        closeBtn.innerHTML = '&times;';
        closeBtn.title = 'Fechar';
        
        const img = document.createElement('img');
        img.className = 'viewer-img';
        
        // Montar a estrutura do visualizador
        viewerContent.appendChild(closeBtn);
        viewerContent.appendChild(img);
        viewer.appendChild(viewerContent);
        document.body.appendChild(viewer);
        
        // Adicionar eventos
        closeBtn.addEventListener('click', function() {
            viewer.style.display = 'none';
        });
        
        viewer.addEventListener('click', function(e) {
            if (e.target === viewer) {
                viewer.style.display = 'none';
            }
        });
        
        return {
            viewer: viewer,
            img: img
        };
    }
    
    // Criar o visualizador
    const imageViewer = createImageViewer();
    
    // Tornar as imagens das tirinhas clicáveis
    const tirinhasImages = document.querySelectorAll('.tirinhas .article-image');
    tirinhasImages.forEach(function(img) {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function() {
            imageViewer.img.src = this.src;
            imageViewer.viewer.style.display = 'flex';
        });
    });
    
    // Tornar a imagem do artigo clicável
    const articleImage = document.querySelector('.featured-article .article-image-full');
    if (articleImage) {
        articleImage.style.cursor = 'pointer';
        articleImage.addEventListener('click', function() {
            imageViewer.img.src = this.src;
            imageViewer.viewer.style.display = 'flex';
        });
    }
});
