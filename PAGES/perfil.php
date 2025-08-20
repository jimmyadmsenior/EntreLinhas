<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado, senão redirecionar para a página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Definir variáveis
$nome = $email = "";
$nome_err = $email_err = $senha_atual_err = $nova_senha_err = $confirmar_senha_err = "";
$senha_atualizada = false;

// Processar dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar qual formulário foi submetido
    if (isset($_POST["atualizar_perfil"])) {
        
        // Validar nome
        if (empty(trim($_POST["nome"]))) {
            $nome_err = "Por favor, insira um nome.";
        } else {
            $nome = trim($_POST["nome"]);
        }
        
        // Validar email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Por favor, insira um e-mail.";
        } else {
            // Prepare uma declaração select
            $sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Vincular variáveis à declaração preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "si", $param_email, $param_id);
                
                // Definir parâmetros
                $param_email = trim($_POST["email"]);
                $param_id = $_SESSION["id"];
                
                // Tentar executar a declaração preparada
                if (mysqli_stmt_execute($stmt)) {
                    /* armazenar resultado */
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        $email_err = "Este e-mail já está em uso.";
                    } else {
                        $email = trim($_POST["email"]);
                    }
                } else {
                    echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                }

                // Fechar declaração
                mysqli_stmt_close($stmt);
            }
        }
        
        // Verificar erros de entrada antes de atualizar o banco de dados
        if (empty($nome_err) && empty($email_err)) {
            
            // Prepare uma declaração de atualização
            $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Vincular variáveis à declaração preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "ssi", $param_nome, $param_email, $param_id);
                
                // Definir parâmetros
                $param_nome = $nome;
                $param_email = $email;
                $param_id = $_SESSION["id"];
                
                // Tentar executar a declaração preparada
                if (mysqli_stmt_execute($stmt)) {
                    // Atualizar as variáveis de sessão
                    $_SESSION["nome"] = $nome;
                    $_SESSION["email"] = $email;
                } else {
                    echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                }

                // Fechar declaração
                mysqli_stmt_close($stmt);
            }
        }
    } elseif (isset($_POST["alterar_senha"])) {
        
        // Validar senha atual
        if (empty(trim($_POST["senha_atual"]))) {
            $senha_atual_err = "Por favor, insira sua senha atual.";
        } else {
            // Verificar se a senha atual está correta
            $sql = "SELECT senha FROM usuarios WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Vincular variáveis à declaração preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "i", $param_id);
                
                // Definir parâmetros
                $param_id = $_SESSION["id"];
                
                // Tentar executar a declaração preparada
                if (mysqli_stmt_execute($stmt)) {
                    // Armazenar resultado
                    mysqli_stmt_store_result($stmt);
                    
                    // Verificar se o usuário existe
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        // Vincular variáveis de resultado
                        mysqli_stmt_bind_result($stmt, $hashed_password);
                        
                        if (mysqli_stmt_fetch($stmt)) {
                            if (!password_verify($_POST["senha_atual"], $hashed_password)) {
                                $senha_atual_err = "A senha atual está incorreta.";
                            }
                        }
                    }
                } else {
                    echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                }

                // Fechar declaração
                mysqli_stmt_close($stmt);
            }
        }
        
        // Validar nova senha
        if (empty(trim($_POST["nova_senha"]))) {
            $nova_senha_err = "Por favor, insira a nova senha.";
        } elseif (strlen(trim($_POST["nova_senha"])) < 6) {
            $nova_senha_err = "A senha deve ter pelo menos 6 caracteres.";
        } else {
            $nova_senha = trim($_POST["nova_senha"]);
        }
        
        // Validar confirmação de senha
        if (empty(trim($_POST["confirmar_senha"]))) {
            $confirmar_senha_err = "Por favor, confirme a senha.";
        } else {
            $confirmar_senha = trim($_POST["confirmar_senha"]);
            if (empty($nova_senha_err) && ($nova_senha != $confirmar_senha)) {
                $confirmar_senha_err = "As senhas não coincidem.";
            }
        }
        
        // Verificar erros de entrada antes de atualizar o banco de dados
        if (empty($senha_atual_err) && empty($nova_senha_err) && empty($confirmar_senha_err)) {
            
            // Prepare uma declaração de atualização
            $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Vincular variáveis à declaração preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "si", $param_senha, $param_id);
                
                // Definir parâmetros
                $param_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
                $param_id = $_SESSION["id"];
                
                // Tentar executar a declaração preparada
                if (mysqli_stmt_execute($stmt)) {
                    // Senha atualizada com sucesso
                    $senha_atualizada = true;
                } else {
                    echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                }

                // Fechar declaração
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Buscar informações do usuário
$sql = "SELECT nome, email FROM usuarios WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    // Vincular variáveis à declaração preparada como parâmetros
    mysqli_stmt_bind_param($stmt, "i", $param_id);
    
    // Definir parâmetros
    $param_id = $_SESSION["id"];
    
    // Tentar executar a declaração preparada
    if (mysqli_stmt_execute($stmt)) {
        // Armazenar resultado
        mysqli_stmt_bind_result($stmt, $db_nome, $db_email);
        
        if (mysqli_stmt_fetch($stmt)) {
            $nome = $db_nome;
            $email = $db_email;
        }
    } else {
        echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
    }
    
    // Fechar declaração
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - EntreLinhas</title>
    <meta name="description" content="Gerencie seu perfil no EntreLinhas.">
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
    <style>
        .error {
            border-color: var(--error) !important;
        }
        .error-message {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }
        .profile-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }
        .profile-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.html">EntreLinhas</a>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.html">Início</a></li>
                <li><a href="artigos.html">Artigos</a></li>
                <li><a href="sobre.html">Sobre</a></li>
                <li><a href="escola.html">A Escola</a></li>
                <li><a href="contato.html">Contato</a></li>
            </ul>
            
            <div class="nav-buttons">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                    </button>
                    <div class="dropdown-menu">
                        <a href="perfil.php" class="active">Meu Perfil</a>
                        <a href="meus-artigos.php">Meus Artigos</a>
                        <a href="enviar-artigo.php">Enviar Artigo</a>
                        <a href="../backend/logout.php">Sair</a>
                    </div>
                </div>
                
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
        <div class="page-header fade-in">
            <h1>Meu Perfil</h1>
            <p>Gerencie suas informações pessoais e configurações de conta.</p>
        </div>
        
        <div class="content-container fade-in">
            <?php if (isset($_POST["atualizar_perfil"]) && empty($nome_err) && empty($email_err)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Suas informações foram atualizadas com sucesso!
                </div>
            <?php endif; ?>
            
            <?php if ($senha_atualizada): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Sua senha foi alterada com sucesso!
                </div>
            <?php endif; ?>
            
            <div class="profile-section">
                <h2><i class="fas fa-user-circle"></i> Informações Pessoais</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="profile-form">
                    <div class="form-group">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>" class="<?php echo (!empty($nome_err)) ? 'error' : ''; ?>">
                        <?php if (!empty($nome_err)) { ?>
                            <div class="error-message"><?php echo $nome_err; ?></div>
                        <?php } ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="<?php echo (!empty($email_err)) ? 'error' : ''; ?>">
                        <?php if (!empty($email_err)) { ?>
                            <div class="error-message"><?php echo $email_err; ?></div>
                        <?php } ?>
                    </div>
                    
                    <button type="submit" name="atualizar_perfil" class="btn btn-primary">Atualizar Informações</button>
                </form>
            </div>
            
            <div class="profile-section">
                <h2><i class="fas fa-lock"></i> Alterar Senha</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="password-form">
                    <div class="form-group">
                        <label for="senha_atual">Senha atual</label>
                        <input type="password" id="senha_atual" name="senha_atual" class="<?php echo (!empty($senha_atual_err)) ? 'error' : ''; ?>">
                        <?php if (!empty($senha_atual_err)) { ?>
                            <div class="error-message"><?php echo $senha_atual_err; ?></div>
                        <?php } ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="nova_senha">Nova senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" class="<?php echo (!empty($nova_senha_err)) ? 'error' : ''; ?>">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.3rem;">A senha deve ter pelo menos 6 caracteres</small>
                        <?php if (!empty($nova_senha_err)) { ?>
                            <div class="error-message"><?php echo $nova_senha_err; ?></div>
                        <?php } ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar nova senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" class="<?php echo (!empty($confirmar_senha_err)) ? 'error' : ''; ?>">
                        <?php if (!empty($confirmar_senha_err)) { ?>
                            <div class="error-message"><?php echo $confirmar_senha_err; ?></div>
                        <?php } ?>
                    </div>
                    
                    <button type="submit" name="alterar_senha" class="btn btn-primary">Alterar Senha</button>
                </form>
            </div>
            
            <div class="profile-section">
                <h2><i class="fas fa-newspaper"></i> Meus Artigos</h2>
                <p>Gerencie todos os artigos que você enviou para o EntreLinhas.</p>
                <a href="meus-artigos.php" class="btn btn-secondary">Ver Meus Artigos</a>
            </div>
        </div>
    </main>

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
                    <li><a href="index.html">Início</a></li>
                    <li><a href="artigos.html">Artigos</a></li>
                    <li><a href="sobre.html">Sobre</a></li>
                    <li><a href="escola.html">A Escola</a></li>
                    <li><a href="contato.html">Contato</a></li>
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
    <script src="../assets/js/main.js"></script>
</body>
</html>

<?php
// Fechar conexão
mysqli_close($conn);
?>
