<?php
// Iniciar a sessão
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - EntreLinhas</title>
    <meta name="description" content="Entre em contato com a equipe do jornal EntreLinhas.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user-menu.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="artigos.php">Artigos</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="escola.php">A Escola</a></li>
                <li><a href="contato.php" class="active">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <div class="user-menu">
                        <div class="user-name">
                            <span class="avatar-container">
                                <i class="fas fa-user"></i>
                            </span>
                            <?php echo htmlspecialchars($_SESSION["nome"]); ?> <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu" id="user-dropdown-menu">
                            <a href="perfil.php" class="dropdown-link"><i class="fas fa-id-card"></i> Meu Perfil</a>
                            <a href="meus-artigos.php" class="dropdown-link"><i class="fas fa-newspaper"></i> Meus Artigos</a>
                            <a href="enviar-artigo.php" class="dropdown-link"><i class="fas fa-edit"></i> Enviar Artigo</a>
                            <?php if (isset($_SESSION["tipo"]) && $_SESSION["tipo"] === 'admin'): ?>
                                <a href="admin_dashboard.php" class="dropdown-link"><i class="fas fa-cogs"></i> Painel de Admin</a>
                            <?php endif; ?>
                            <a href="logout.php" class="dropdown-link"><i class="fas fa-sign-out-alt"></i> Sair</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Entrar</a>
                    <a href="registro.php" class="btn btn-primary">Cadastrar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container">
        <section class="contact-section">
            <h1>Entre em Contato</h1>
            
            <div class="contact-info">
                <div class="contact-form">
                    <h2>Envie uma Mensagem</h2>
                    <p>Utilize o formulário abaixo para entrar em contato conosco.</p>
                    
                    <form action="#" method="POST">
                        <div class="form-group">
                            <label for="name">Nome</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Assunto</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Mensagem</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                    </form>
                </div>
                
                <div class="contact-details">
                    <h2>Informações de Contato</h2>
                    <p>Entre em contato conosco por outros meios:</p>
                    
                    <ul>
                        <li><strong>Email:</strong> contato@entrelinhas.com.br</li>
                        <li><strong>Telefone:</strong> (11) 1234-5678</li>
                        <li><strong>Endereço:</strong> Av. Paulista, 1000 - São Paulo, SP</li>
                    </ul>
                    
                    <h3>Horário de Funcionamento</h3>
                    <p>Segunda a Sexta: 9h às 18h</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>EntreLinhas</h2>
                    <p>Um jornal digital colaborativo</p>
                </div>
                
                <div class="footer-links">
                    <h3>Links Rápidos</h3>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="artigos.php">Artigos</a></li>
                        <li><a href="sobre.php">Sobre</a></li>
                        <li><a href="contato.php">Contato</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> EntreLinhas. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/user-menu.js"></script>
</body>
</html>
