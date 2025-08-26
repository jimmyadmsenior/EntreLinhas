<?php
// Este é um endpoint JSON para processar login via AJAX
header('Content-Type: application/json');

// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Variável para armazenar a resposta
$response = [
    'success' => false,
    'message' => '',
    'redirect' => '',
    'user_type' => ''
];

// Verificar se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obter e limpar os dados da requisição
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    
    // Validar os dados
    if (empty($email)) {
        $response['message'] = "Por favor, insira seu e-mail.";
    } elseif (empty($senha)) {
        $response['message'] = "Por favor, insira sua senha.";
    } else {
        // Consultar o banco de dados
        $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular o parâmetro email
            mysqli_stmt_bind_param($stmt, "s", $email);
            
            // Executar a consulta
            if (mysqli_stmt_execute($stmt)) {
                // Armazenar o resultado
                mysqli_stmt_store_result($stmt);
                
                // Verificar se o usuário existe
                if (mysqli_stmt_num_rows($stmt) === 1) {
                    // Vincular as variáveis de resultado
                    mysqli_stmt_bind_result($stmt, $id, $nome, $email_db, $hashed_password, $tipo);
                    
                    // Obter os resultados
                    if (mysqli_stmt_fetch($stmt)) {
                        // Verificar a senha
                        if (password_verify($senha, $hashed_password)) {
                            // Criar a sessão
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nome"] = $nome;
                            $_SESSION["email"] = $email_db;
                            $_SESSION["tipo"] = $tipo;
                            
                            // Definir cookies para integração com JavaScript
                            // Não usar URL encoding para evitar problemas de exibição
                            setcookie("userLoggedIn", "true", time() + 86400, "/");
                            setcookie("userName", $nome, time() + 86400, "/", "", false, false);
                            setcookie("userEmail", $email_db, time() + 86400, "/");
                            setcookie("userType", $tipo, time() + 86400, "/");
                            setcookie("userId", $id, time() + 86400, "/");
                            setcookie("php_auth", "true", time() + 86400, "/");
                            
                            // Registrar log de login bem-sucedido
                            error_log("Login bem-sucedido: $nome ($email_db) - Tipo: $tipo");
                            
                            // Configurar a resposta
                            $response['success'] = true;
                            $response['message'] = "Login bem-sucedido!";
                            $response['user_type'] = $tipo;
                            $response['user_name'] = $nome;
                            $response['user_email'] = $email_db;
                            $response['user_id'] = $id;
                            
                            // Definir o redirecionamento com base no tipo de usuário
                            if ($tipo === 'admin') {
                                $response['redirect'] = 'admin_dashboard.php';
                            } else {
                                $response['redirect'] = '../index.php';
                            }
                        } else {
                            $response['message'] = "E-mail ou senha inválidos.";
                        }
                    }
                } else {
                    $response['message'] = "E-mail ou senha inválidos.";
                }
            } else {
                $response['message'] = "Erro ao executar a consulta. Tente novamente mais tarde.";
            }
            
            // Fechar a declaração
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = "Erro ao preparar a consulta. Tente novamente mais tarde.";
        }
    }
    
    // Fechar a conexão
    mysqli_close($conn);
} else {
    $response['message'] = "Método de requisição inválido.";
}

// Enviar a resposta
echo json_encode($response);
?>
