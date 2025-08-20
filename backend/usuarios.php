<?php
// Funções para gerenciamento de usuários

/**
 * Registra um novo usuário
 * @param mysqli $conn Conexão com o banco de dados
 * @param array $usuario Dados do usuário (nome, email, senha)
 * @return array Resultado do registro com status e mensagem
 */
function registrarUsuario($conn, $usuario) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'usuario_id' => 0
    ];
    
    // Validar campos obrigatórios
    if (empty($usuario['nome']) || empty($usuario['email']) || empty($usuario['senha'])) {
        $resultado['mensagem'] = "Todos os campos são obrigatórios.";
        return $resultado;
    }
    
    // Validar formato de email
    if (!filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) {
        $resultado['mensagem'] = "Formato de e-mail inválido.";
        return $resultado;
    }
    
    // Verificar se o email já existe
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $usuario['email']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $resultado['mensagem'] = "Este e-mail já está em uso.";
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Criptografar a senha
    $senha_hash = password_hash($usuario['senha'], PASSWORD_DEFAULT);
    
    // Inserir o novo usuário no banco de dados
    $sql = "INSERT INTO usuarios (nome, email, senha, data_cadastro) VALUES (?, ?, ?, NOW())";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $usuario['nome'], $usuario['email'], $senha_hash);
        
        if (mysqli_stmt_execute($stmt)) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Cadastro realizado com sucesso!";
            $resultado['usuario_id'] = mysqli_insert_id($conn);
            
            // Enviar e-mail de boas-vindas
            enviarEmailBoasVindas($usuario['nome'], $usuario['email']);
        } else {
            $resultado['mensagem'] = "Erro ao registrar usuário. Por favor, tente novamente.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Envia um e-mail de boas-vindas ao novo usuário
 * @param string $nome Nome do usuário
 * @param string $email E-mail do usuário
 * @return bool Resultado do envio de email
 */
function enviarEmailBoasVindas($nome, $email) {
    // Assunto do e-mail
    $assunto = "Bem-vindo(a) ao EntreLinhas!";
    
    // Corpo do e-mail em HTML
    $mensagem = "
    <html>
    <head>
        <title>Bem-vindo ao EntreLinhas</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #000; color: #fff; padding: 15px; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
            .btn { display: inline-block; padding: 10px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>EntreLinhas</h1>
            </div>
            <div class='content'>
                <h2>Olá, $nome!</h2>
                <p>Bem-vindo(a) ao EntreLinhas, o jornal digital colaborativo da SESI Salto.</p>
                <p>Agora você pode compartilhar seus artigos, histórias e conhecimentos com toda a comunidade.</p>
                <p>Para começar:</p>
                <ol>
                    <li>Acesse sua conta com seu e-mail e senha</li>
                    <li>Explore os artigos já publicados</li>
                    <li>Escreva e envie seu primeiro artigo</li>
                </ol>
                <p><a href='http://seusite.com.br/PAGES/login.php' class='btn'>Acessar Minha Conta</a></p>
            </div>
            <div class='footer'>
                <p>Este é um e-mail automático. Por favor, não responda.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Cabeçalhos para envio de e-mail em HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: EntreLinhas <noreply@entrelinhas.com.br>\r\n";
    
    // Tentar enviar e-mail
    return mail($email, $assunto, $mensagem, $headers);
}

/**
 * Autentica um usuário
 * @param mysqli $conn Conexão com o banco de dados
 * @param string $email Email do usuário
 * @param string $senha Senha do usuário
 * @return array Resultado da autenticação com status e dados do usuário
 */
function autenticarUsuario($conn, $email, $senha) {
    $resultado = [
        'status' => false,
        'mensagem' => '',
        'usuario' => null
    ];
    
    // Verificar se email e senha foram fornecidos
    if (empty($email) || empty($senha)) {
        $resultado['mensagem'] = "Por favor, preencha todos os campos.";
        return $resultado;
    }
    
    // Buscar usuário pelo email
    $sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($usuario = mysqli_fetch_assoc($result)) {
            // Verificar a senha
            if (password_verify($senha, $usuario['senha'])) {
                // Senha correta
                $resultado['status'] = true;
                $resultado['mensagem'] = "Login realizado com sucesso!";
                
                // Remover a senha do array de retorno
                unset($usuario['senha']);
                $resultado['usuario'] = $usuario;
                
                // Iniciar a sessão e armazenar dados do usuário
                session_start();
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $usuario['id'];
                $_SESSION["nome"] = $usuario['nome'];
                $_SESSION["email"] = $usuario['email'];
            } else {
                // Senha incorreta
                $resultado['mensagem'] = "Email ou senha inválidos.";
            }
        } else {
            // Usuário não encontrado
            $resultado['mensagem'] = "Email ou senha inválidos.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Atualiza os dados de um usuário
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $usuario_id ID do usuário
 * @param array $dados Novos dados do usuário
 * @return array Resultado da atualização com status e mensagem
 */
function atualizarUsuario($conn, $usuario_id, $dados) {
    $resultado = [
        'status' => false,
        'mensagem' => ''
    ];
    
    // Validar campos obrigatórios
    if (empty($dados['nome']) || empty($dados['email'])) {
        $resultado['mensagem'] = "Nome e e-mail são campos obrigatórios.";
        return $resultado;
    }
    
    // Verificar se o email já está em uso por outro usuário
    $sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $dados['email'], $usuario_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $resultado['mensagem'] = "Este e-mail já está em uso por outro usuário.";
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Atualizar os dados do usuário
    $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $dados['nome'], $dados['email'], $usuario_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Dados atualizados com sucesso!";
            
            // Atualizar dados da sessão
            $_SESSION["nome"] = $dados['nome'];
            $_SESSION["email"] = $dados['email'];
        } else {
            $resultado['mensagem'] = "Erro ao atualizar dados. Por favor, tente novamente.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Altera a senha de um usuário
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $usuario_id ID do usuário
 * @param string $senha_atual Senha atual
 * @param string $nova_senha Nova senha
 * @return array Resultado da alteração com status e mensagem
 */
function alterarSenha($conn, $usuario_id, $senha_atual, $nova_senha) {
    $resultado = [
        'status' => false,
        'mensagem' => ''
    ];
    
    // Verificar se as senhas foram fornecidas
    if (empty($senha_atual) || empty($nova_senha)) {
        $resultado['mensagem'] = "Por favor, preencha todos os campos.";
        return $resultado;
    }
    
    // Verificar se a senha atual está correta
    $sql = "SELECT senha FROM usuarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($usuario = mysqli_fetch_assoc($result)) {
            if (!password_verify($senha_atual, $usuario['senha'])) {
                $resultado['mensagem'] = "Senha atual incorreta.";
                mysqli_stmt_close($stmt);
                return $resultado;
            }
        } else {
            $resultado['mensagem'] = "Usuário não encontrado.";
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Criptografar a nova senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    // Atualizar a senha
    $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $senha_hash, $usuario_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Senha alterada com sucesso!";
        } else {
            $resultado['mensagem'] = "Erro ao alterar senha. Por favor, tente novamente.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Gera um token para recuperação de senha
 * @param mysqli $conn Conexão com o banco de dados
 * @param string $email Email do usuário
 * @return array Resultado da operação com status e mensagem
 */
function gerarTokenRecuperacao($conn, $email) {
    $resultado = [
        'status' => false,
        'mensagem' => ''
    ];
    
    // Verificar se o email foi fornecido
    if (empty($email)) {
        $resultado['mensagem'] = "Por favor, informe o e-mail.";
        return $resultado;
    }
    
    // Verificar se o email existe no banco de dados
    $sql = "SELECT id, nome FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) == 0) {
            $resultado['mensagem'] = "Não encontramos uma conta com este e-mail.";
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        
        // Obter o nome do usuário
        mysqli_stmt_bind_result($stmt, $usuario_id, $nome);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        // Gerar token aleatório
        $token = bin2hex(random_bytes(32));
        
        // Calcular data de expiração (1 hora)
        $expiracao = date('Y-m-d H:i:s', time() + 3600);
        
        // Salvar o token no banco de dados
        $sql = "UPDATE usuarios SET reset_token = ?, reset_expiry = ? WHERE email = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $token, $expiracao, $email);
            
            if (mysqli_stmt_execute($stmt)) {
                // Enviar e-mail com link de recuperação
                $link = "http://seusite.com.br/PAGES/redefinir-senha.php?email=" . urlencode($email) . "&token=" . $token;
                
                // Assunto do e-mail
                $assunto = "Recuperação de Senha - EntreLinhas";
                
                // Corpo do e-mail em HTML
                $mensagem = "
                <html>
                <head>
                    <title>Recuperação de Senha</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #000; color: #fff; padding: 15px; text-align: center; }
                        .content { padding: 20px; border: 1px solid #ddd; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                        .btn { display: inline-block; padding: 10px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>EntreLinhas</h1>
                        </div>
                        <div class='content'>
                            <h2>Recuperação de Senha</h2>
                            <p>Olá, $nome!</p>
                            <p>Recebemos uma solicitação para redefinir a senha da sua conta no EntreLinhas.</p>
                            <p>Se você não solicitou uma redefinição de senha, ignore este e-mail.</p>
                            <p>Para redefinir sua senha, clique no botão abaixo:</p>
                            <p style='text-align: center;'><a href='$link' class='btn'>Redefinir Minha Senha</a></p>
                            <p>Ou copie e cole o seguinte link no seu navegador:</p>
                            <p>$link</p>
                            <p>Este link expirará em 1 hora.</p>
                        </div>
                        <div class='footer'>
                            <p>Este é um e-mail automático. Por favor, não responda.</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                // Cabeçalhos para envio de e-mail em HTML
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: EntreLinhas <noreply@entrelinhas.com.br>\r\n";
                
                // Tentar enviar e-mail
                if (mail($email, $assunto, $mensagem, $headers)) {
                    $resultado['status'] = true;
                    $resultado['mensagem'] = "E-mail de recuperação enviado com sucesso! Verifique sua caixa de entrada.";
                } else {
                    $resultado['mensagem'] = "Erro ao enviar o e-mail de recuperação. Por favor, tente novamente.";
                }
            } else {
                $resultado['mensagem'] = "Erro ao gerar token de recuperação. Por favor, tente novamente.";
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
        }
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Valida um token de recuperação de senha
 * @param mysqli $conn Conexão com o banco de dados
 * @param string $email Email do usuário
 * @param string $token Token de recuperação
 * @return bool True se o token for válido, false caso contrário
 */
function validarTokenRecuperacao($conn, $email, $token) {
    $sql = "SELECT reset_expiry FROM usuarios WHERE email = ? AND reset_token = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Verificar se o token expirou
            $expiracao = strtotime($row['reset_expiry']);
            $agora = time();
            
            mysqli_stmt_close($stmt);
            return $expiracao > $agora;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Redefine a senha de um usuário
 * @param mysqli $conn Conexão com o banco de dados
 * @param string $email Email do usuário
 * @param string $token Token de recuperação
 * @param string $nova_senha Nova senha
 * @return array Resultado da operação com status e mensagem
 */
function redefinirSenha($conn, $email, $token, $nova_senha) {
    $resultado = [
        'status' => false,
        'mensagem' => ''
    ];
    
    // Validar token
    if (!validarTokenRecuperacao($conn, $email, $token)) {
        $resultado['mensagem'] = "Token inválido ou expirado. Por favor, solicite uma nova recuperação de senha.";
        return $resultado;
    }
    
    // Criptografar a nova senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    // Atualizar a senha e limpar o token
    $sql = "UPDATE usuarios SET senha = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $senha_hash, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            $resultado['status'] = true;
            $resultado['mensagem'] = "Senha redefinida com sucesso! Agora você pode fazer login com sua nova senha.";
        } else {
            $resultado['mensagem'] = "Erro ao redefinir senha. Por favor, tente novamente.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $resultado['mensagem'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
    }
    
    return $resultado;
}

/**
 * Obter informações de um usuário
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $usuario_id ID do usuário
 * @return array|bool Dados do usuário ou false se não encontrado
 */
function obterUsuario($conn, $usuario_id) {
    $sql = "SELECT id, nome, email, data_cadastro FROM usuarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($usuario = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $usuario;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Listar todos os usuários (para administradores)
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $limite Quantidade máxima de resultados
 * @param int $pagina Número da página para paginação
 * @return array Lista de usuários
 */
function listarUsuarios($conn, $limite = 10, $pagina = 1) {
    $offset = ($pagina - 1) * $limite;
    
    $sql = "SELECT id, nome, email, data_cadastro FROM usuarios ORDER BY data_cadastro DESC LIMIT ? OFFSET ?";
    
    $usuarios = [];
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $limite, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $usuarios[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $usuarios;
}

/**
 * Função para verificar se o usuário é um administrador
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $usuario_id ID do usuário a verificar
 * @return bool True se for administrador, false caso contrário
 */
function isAdmin($conn, $usuario_id) {
    $sql = "SELECT email FROM usuarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($usuario = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            // Verifica se o email do usuário é o email do administrador definido no config
            return $usuario['email'] === ADMIN_EMAIL;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}
?>
