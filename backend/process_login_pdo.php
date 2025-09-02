<?php
// Este é um endpoint JSON para processar login via AJAX
header('Content-Type: application/json');

// Iniciar sessão
session_start();

// Incluir arquivo de configuração PDO
require_once "../config_pdo.php";
// Incluir funções auxiliares PDO
require_once "../pdo_helper.php";

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
        try {
            // Consultar o banco de dados
            $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            
            // Verificar se o usuário existe
            if ($stmt->rowCount() === 1) {
                // Obter os resultados
                $row = $stmt->fetch();
                $id = $row['id'];
                $nome = $row['nome'];
                $email_db = $row['email'];
                $hashed_password = $row['senha'];
                $tipo = $row['tipo'];
                
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
                    setcookie("userName", $nome, time() + 86400, "/");
                    setcookie("userEmail", $email_db, time() + 86400, "/");
                    setcookie("userType", $tipo, time() + 86400, "/");
                    setcookie("userId", $id, time() + 86400, "/");
                    
                    // Definir a resposta de sucesso
                    $response['success'] = true;
                    $response['message'] = "Login bem-sucedido!";
                    $response['user_type'] = $tipo;
                    
                    // Definir o redirecionamento com base no tipo de usuário
                    if ($tipo === "admin") {
                        $response['redirect'] = "admin_dashboard.php";
                    } else {
                        $response['redirect'] = "../index.php";
                    }
                    
                    // Registrar o login bem-sucedido
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $data_hora = date('Y-m-d H:i:s');
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    
                    // Registrar log de login (opcional)
                    try {
                        $log_sql = "INSERT INTO login_logs (user_id, email, ip_address, user_agent, login_time, status) 
                                   VALUES (?, ?, ?, ?, ?, 'success')";
                        
                        $log_stmt = $pdo->prepare($log_sql);
                        $log_stmt->execute([$id, $email_db, $ip, $user_agent, $data_hora]);
                    } catch (PDOException $e) {
                        // Apenas registre o erro, não interrompa o login
                        error_log("Erro ao registrar log de login: " . $e->getMessage());
                    }
                } else {
                    // Senha incorreta
                    $response['message'] = "E-mail ou senha inválidos.";
                    
                    // Registrar tentativa de login com falha (opcional)
                    try {
                        $log_sql = "INSERT INTO login_logs (email, ip_address, user_agent, login_time, status) 
                                   VALUES (?, ?, ?, ?, 'failed')";
                        
                        $log_stmt = $pdo->prepare($log_sql);
                        $log_stmt->execute([$email, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], date('Y-m-d H:i:s')]);
                    } catch (PDOException $e) {
                        error_log("Erro ao registrar tentativa de login: " . $e->getMessage());
                    }
                }
            } else {
                // Usuário não encontrado
                $response['message'] = "E-mail ou senha inválidos.";
            }
        } catch (PDOException $e) {
            $response['message'] = "Erro ao processar o login. Por favor, tente novamente mais tarde.";
            error_log("Erro no login: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = "Método de requisição inválido.";
}

// Enviar a resposta como JSON
echo json_encode($response);
?>
