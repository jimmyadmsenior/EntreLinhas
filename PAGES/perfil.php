<?php
// Iniciar sessão
session_start();

// Incluir arquivo de sincronização entre localStorage e sessões PHP
require_once "../backend/auth-bridge.php";

// Verificar se o usuário está logado, senão redirecionar para a página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Incluir arquivo de configuração e helper de usuário
require_once "../backend/config.php";
require_once "../backend/usuario_helper.php";

// Definir variáveis
$nome = $email = "";
$nome_err = $email_err = $senha_atual_err = $nova_senha_err = $confirmar_senha_err = "";
$senha_atualizada = false;

// Obter a foto de perfil do usuário
$foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);

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
    <script src="../assets/js/user-menu.js" defer></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

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
                <h2><i class="fas fa-image"></i> Foto de Perfil</h2>
                
                <?php 
                // Verificar se há mensagens de erro ou sucesso
                if (isset($_SESSION['foto_erro'])) {
                    echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['foto_erro'] . '</div>';
                    unset($_SESSION['foto_erro']);
                }
                if (isset($_SESSION['foto_sucesso'])) {
                    echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . $_SESSION['foto_sucesso'] . '</div>';
                    unset($_SESSION['foto_sucesso']);
                }
                
                // Verificar se o usuário já tem uma foto de perfil
                $foto_perfil = null;
                $sql_foto = "SELECT imagem_base64 FROM fotos_perfil WHERE id_usuario = ?";
                if ($stmt_foto = mysqli_prepare($conn, $sql_foto)) {
                    mysqli_stmt_bind_param($stmt_foto, "i", $_SESSION["id"]);
                    mysqli_stmt_execute($stmt_foto);
                    mysqli_stmt_bind_result($stmt_foto, $imagem_base64);
                    if (mysqli_stmt_fetch($stmt_foto)) {
                        $foto_perfil = $imagem_base64;
                    }
                    mysqli_stmt_close($stmt_foto);
                }
                ?>
                
                <div class="profile-image-container" style="display: flex; flex-direction: column; align-items: center; margin-bottom: 20px;">
                    <div class="profile-image" style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin-bottom: 15px; border: 3px solid var(--primary); background-color: #f0f0f0; display: flex; justify-content: center; align-items: center;">
                        <?php if ($foto_perfil): ?>
                            <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user" style="font-size: 4rem; color: #888;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <form action="../backend/processar_foto_perfil.php" method="post" enctype="multipart/form-data" style="text-align: center;">
                        <div style="margin-bottom: 15px;">
                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg, image/png, image/gif" style="display: none;">
                            <label for="foto_perfil" class="btn btn-secondary" style="cursor: pointer; display: inline-block;">
                                <i class="fas fa-upload"></i> Selecionar imagem
                            </label>
                            <small style="display: block; margin-top: 8px; color: var(--text-secondary);">JPG, PNG ou GIF (máx. 2MB)</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $foto_perfil ? 'Atualizar foto' : 'Enviar foto'; ?>
                        </button>
                    </form>
                </div>
                
                <script>
                    // Mostrar nome do arquivo selecionado
                    document.getElementById('foto_perfil').addEventListener('change', function() {
                        const fileName = this.files[0] ? this.files[0].name : 'Nenhum arquivo selecionado';
                        const label = this.nextElementSibling;
                        label.innerHTML = '<i class="fas fa-file-image"></i> ' + (fileName.length > 20 ? fileName.substring(0, 17) + '...' : fileName);
                    });
                </script>
            </div>
            
            <div class="profile-section">
                <h2><i class="fas fa-newspaper"></i> Meus Artigos</h2>
                <p>Gerencie todos os artigos que você enviou para o EntreLinhas.</p>
                <a href="meus-artigos.php" class="btn btn-secondary">Ver Meus Artigos</a>
            </div>
        </div>
    </main>

    <!-- Incluir o rodapé comum -->
    <?php 
    $root_path = "..";
    include 'includes/footer.php'; 
    ?>
</body>
</html>

<?php
// Fechar conexão
mysqli_close($conn);
?>
