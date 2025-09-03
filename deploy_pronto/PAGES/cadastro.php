<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração PDO
require_once "../backend/config_pdo.php";

// Definir variáveis e inicializar com valores vazios
$nome = $email = $senha = $confirmar_senha = "";
$nome_err = $email_err = $senha_err = $confirmar_senha_err = "";

// Processar dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
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
        try {
            // Preparar uma consulta select
            $sql = "SELECT id FROM usuarios WHERE email = ?";
            
            $stmt = $pdo->prepare($sql);
            
            // Definir parâmetros e executar
            $param_email = trim($_POST["email"]);
            $stmt->execute([$param_email]);
            
            // Verificar se o email já existe
            if ($stmt->rowCount() == 1) {
                $email_err = "Este e-mail já está em uso.";
            } else {
                $email = trim($_POST["email"]);
            }
        } catch(PDOException $e) {
            echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            error_log("Erro no cadastro (verificação de email): " . $e->getMessage());
        }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validar senha
    if (empty(trim($_POST["senha"]))) {
        $senha_err = "Por favor, insira uma senha.";     
    } elseif (strlen(trim($_POST["senha"])) < 6) {
        $senha_err = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $senha = trim($_POST["senha"]);
    }
    
    // Validar confirmação de senha
    if (empty(trim($_POST["confirmar_senha"]))) {
        $confirmar_senha_err = "Por favor, confirme a senha.";     
    } else {
        $confirmar_senha = trim($_POST["confirmar_senha"]);
        if (empty($senha_err) && ($senha != $confirmar_senha)) {
            $confirmar_senha_err = "As senhas não coincidem.";
        }
    }
    
    // Verificar erros de entrada antes de inserir no banco de dados
    if (empty($nome_err) && empty($email_err) && empty($senha_err) && empty($confirmar_senha_err)) {
        
        try {
            // Preparar uma declaração de inserção
            $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            
            // Definir parâmetros
            $param_nome = $nome;
            $param_email = $email;
            $param_senha = password_hash($senha, PASSWORD_DEFAULT); // Cria um hash de senha
            
            // Executar a declaração preparada
            if ($stmt->execute([$param_nome, $param_email, $param_senha])) {
                // Redirecionar para a página de sucesso de cadastro
                header("location: cadastro-sucesso.html");
                exit();
            }
        } catch(PDOException $e) {
            echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            error_log("Erro no cadastro (inserção): " . $e->getMessage());
        }
    }
    
    // Conexão PDO é fechada automaticamente ao final do script
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - EntreLinhas</title>
    <meta name="description" content="Cadastre-se no EntreLinhas para começar a compartilhar seus textos e artigos.">
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
        .form-container {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="container">
        <div class="form-container fade-in">
            <h1 class="form-title">Cadastre-se no EntreLinhas</h1>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="register-form">
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
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="<?php echo (!empty($senha_err)) ? 'error' : ''; ?>">
                    <small style="color: var(--primary-light); display: block; margin-top: 0.3rem;">A senha deve ter pelo menos 6 caracteres</small>
                    <?php if (!empty($senha_err)) { ?>
                        <div class="error-message"><?php echo $senha_err; ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="<?php echo (!empty($confirmar_senha_err)) ? 'error' : ''; ?>">
                    <?php if (!empty($confirmar_senha_err)) { ?>
                        <div class="error-message"><?php echo $confirmar_senha_err; ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="terms" name="terms" required>
                        Li e concordo com os <a href="termos.html" target="_blank">termos de uso</a> e a <a href="privacidade.html" target="_blank">política de privacidade</a>.
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">Cadastrar</button>
            </form>
            
            <p class="text-center mt-3">
                Já possui uma conta? <a href="login.php">Entrar</a>
            </p>
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
                    <li><i class="fas fa-map-marker-alt"></i> Rua Israel, 100 - Jardim Panorama, Salto - SP</li>
                    <li><i class="fas fa-phone"></i> (11) 4029-8635</li>
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
