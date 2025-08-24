<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$page_title = "Contato - EntreLinhas";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="Entre em contato com a equipe do EntreLinhas para dúvidas, sugestões ou parcerias.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/jornal.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user-menu.css">
    <link rel="stylesheet" href="../assets/css/alerts.css">
    <style>
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 2rem;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .contact-method {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .contact-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary);
            color: var(--text-light);
            border-radius: 50%;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="../index.php">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="../index.php">Início</a></li>
                <li><a href="artigos.php">Artigos</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="escola.php">A Escola</a></li>
                <li><a href="contato.php" class="active">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <?php if ($usuario_logado): ?>
                    <!-- Menu dropdown do usuário -->
                    <div class="user-menu">
                        <div class="user-name">
                            <span class="avatar-container">
                                <?php 
                                // Tentar obter a foto de perfil
                                $foto_perfil = null;
                                if (isset($conn)) {
                                    // Carregar helper se ainda não estiver carregado
                                    if (!function_exists('obter_foto_perfil')) {
                                        require_once dirname(__FILE__) . "/../backend/usuario_helper.php";
                                    }
                                    
                                    // Obter foto de perfil
                                    if (function_exists('obter_foto_perfil')) {
                                        $foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);
                                    }
                                }
                                
                                if ($foto_perfil): 
                                ?>
                                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" class="user-avatar">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
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
                    <!-- Links de login e cadastro -->
                    <a href="login.php" class="btn btn-secondary">Entrar</a>
                    <a href="registro.php" class="btn btn-primary">Cadastrar</a>
                <?php endif; ?>
                
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar modo escuro">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <main class="container my-5">
        <h1>Entre em Contato</h1>
        <p class="lead">Tem dúvidas, sugestões ou gostaria de compartilhar sua opinião conosco? Ficaremos felizes em ouvir você!</p>
        
        <div class="contact-container">
            <div class="contact-form">
                <h2>Envie uma Mensagem</h2>
                <form id="contact-form" action="#" method="post">
                    <div class="form-group mb-3">
                        <label for="name">Nome</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="subject">Assunto</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="message">Mensagem</label>
                        <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                </form>
            </div>
            
            <div class="contact-info">
                <h2>Informações de Contato</h2>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h3>E-mail</h3>
                        <p>jimmycastilho555@gmail.com</p>
                        <p>entrelinhas@sesisp.org.br</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <h3>Telefone</h3>
                        <p>(11) 4029-1234</p>
                        <p>(11) 98765-4321</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h3>Endereço</h3>
                        <p>Av. Marechal Rondon, 3000</p>
                        <p>Jardim Bandeirantes, Salto - SP</p>
                        <p>CEP: 13324-000</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h3>Horário de Atendimento</h3>
                        <p>Segunda a Sexta: 8h às 17h</p>
                        <p>Sábados: 8h às 12h</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="map-container my-5">
            <h2>Como Chegar</h2>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3670.9259775887546!2d-47.29529778503663!3d-23.05589204904281!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94cf4dff1b31a70b%3A0x86b61f14ca3c84a5!2sAv.%20Mal.%20Rondon%2C%203000%20-%20Jardim%20Bandeirantes%2C%20Salto%20-%20SP%2C%2013324-065!5e0!3m2!1spt-BR!2sbr!4v1679876543210!5m2!1spt-BR!2sbr" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/auth-cookies.js"></script>
    <script src="../assets/js/verificar-sincronizar-login.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/user-menu.js"></script>
    <script src="../assets/js/dropdown-menu.js"></script>
    <script src="../assets/js/header-nav.js"></script>
    <script>
        // Validação do formulário
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Aqui você pode adicionar validação personalizada
            
            // Exemplo de mensagem de sucesso
            alert('Sua mensagem foi enviada com sucesso! Em breve entraremos em contato.');
            this.reset();
        });
    </script>
</body>
</html>
