<?php
// Arquivo de rodapé comum para todas as páginas

// Determinar a localização relativa para os assets
$root_path = isset($root_path) ? $root_path : "..";
?>

<!-- Footer -->
<footer>
    <div class="footer-container">
        <div class="footer-section">
            <h3>EntreLinhas</h3>
            <p>Um jornal digital colaborativo onde vozes diversas se encontram para compartilhar conhecimento, histórias e experiências.</p>
        </div>
        
        <div class="footer-section">
            <h3>Links Rápidos</h3>
            <ul class="footer-links">
<<<<<<< Updated upstream
                <li><a href="../index.php">Início</a></li>
=======
                <li><a href="index.php">Início</a></li>
>>>>>>> Stashed changes
                <li><a href="artigos.php">Artigos</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="escola.php">A Escola</a></li>
                <li><a href="contato.php">Contato</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Contato</h3>
            <ul class="footer-links">
                <li><i class="fas fa-envelope"></i> jimmycastilho555@gmail.com</li>
                <li><i class="fas fa-map-marker-alt"></i> Av. Marechal Rondon, 3000 - Jardim Bandeirantes, Salto - SP</li>
                <li><i class="fas fa-phone"></i> (11) 4029-1234</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 EntreLinhas - SESI Salto. Todos os direitos reservados.</p>
    </div>
</footer>

<!-- JavaScript -->
<script src="<?php echo $root_path; ?>/assets/js/main.js"></script>
<script src="<?php echo $root_path; ?>/assets/js/header-nav.js"></script>
