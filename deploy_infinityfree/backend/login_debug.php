<?php
// Script para depuração do login AJAX
// Este script mostra exatamente o que está acontecendo durante o processo de login

// Definir cabeçalho para JSON
header('Content-Type: application/json');

// Permitir requisições de origens diferentes (para desenvolvimento)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Ativar relatório de erros PHP para depuração
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão
session_start();

// Registrar o início do processo
$debug_log = ["Iniciando processo de login - " . date('Y-m-d H:i:s')];
$debug_log[] = "Método da requisição: " . $_SERVER['REQUEST_METHOD'];

// Variável para armazenar a resposta
$response = [
    'success' => false,
    'message' => '',
    'redirect' => '',
    'user_type' => '',
    'debug' => []
];

// Verificar se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $debug_log[] = "Método POST detectado";
    
    // Registrar dados recebidos (cuidado com segurança em produção)
    $debug_log[] = "Dados recebidos: " . json_encode($_POST);
    
    // Incluir arquivo de configuração
    try {
        require_once "../backend/config.php";
        $debug_log[] = "Arquivo de configuração carregado com sucesso";
    } catch (Exception $e) {
        $debug_log[] = "ERRO ao carregar arquivo de configuração: " . $e->getMessage();
        $response['message'] = "Erro ao carregar configurações.";
        $response['debug'] = $debug_log;
        echo json_encode($response);
        exit;
    }
    
    // Verificar conexão com o banco
    if (!$conn) {
        $debug_log[] = "ERRO: Conexão com banco de dados falhou: " . mysqli_connect_error();
        $response['message'] = "Não foi possível conectar ao banco de dados.";
        $response['debug'] = $debug_log;
        echo json_encode($response);
        exit;
    }
    
    $debug_log[] = "Conexão com banco de dados estabelecida com sucesso";
    
    // Obter e limpar os dados da requisição
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    
    $debug_log[] = "Email fornecido: " . $email;
    $debug_log[] = "Senha fornecida: [REDACTED]"; // Não logar senhas
    
    // Validar os dados
    if (empty($email)) {
        $debug_log[] = "Email não fornecido";
        $response['message'] = "Por favor, insira seu e-mail.";
    } elseif (empty($senha)) {
        $debug_log[] = "Senha não fornecida";
        $response['message'] = "Por favor, insira sua senha.";
    } else {
        $debug_log[] = "Dados de formulário validados";
        
        // Consultar o banco de dados
        $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
        $debug_log[] = "SQL preparado: " . $sql;
        
        try {
            if ($stmt = mysqli_prepare($conn, $sql)) {
                $debug_log[] = "Statement SQL preparado com sucesso";
                
                // Vincular o parâmetro email
                mysqli_stmt_bind_param($stmt, "s", $email);
                $debug_log[] = "Parâmetro email vinculado: " . $email;
                
                // Executar a consulta
                if (mysqli_stmt_execute($stmt)) {
                    $debug_log[] = "Consulta SQL executada com sucesso";
                    
                    // Armazenar o resultado
                    mysqli_stmt_store_result($stmt);
                    $num_rows = mysqli_stmt_num_rows($stmt);
                    $debug_log[] = "Registros encontrados: " . $num_rows;
                    
                    // Verificar se o usuário existe
                    if ($num_rows === 1) {
                        $debug_log[] = "Usuário encontrado";
                        
                        // Vincular as variáveis de resultado
                        mysqli_stmt_bind_result($stmt, $id, $nome, $email_db, $hashed_password, $tipo);
                        $debug_log[] = "Variáveis de resultado vinculadas";
                        
                        // Obter os resultados
                        if (mysqli_stmt_fetch($stmt)) {
                            $debug_log[] = "Dados do usuário obtidos:";
                            $debug_log[] = "- ID: " . $id;
                            $debug_log[] = "- Nome: " . $nome;
                            $debug_log[] = "- Email: " . $email_db;
                            $debug_log[] = "- Tipo: " . $tipo;
                            $debug_log[] = "- Hash da senha: " . substr($hashed_password, 0, 13) . "..."; // Mostrar apenas o começo do hash
                            
                            // Verificar a senha
                            $senha_ok = password_verify($senha, $hashed_password);
                            $debug_log[] = "Verificação de senha: " . ($senha_ok ? "SUCESSO" : "FALHA");
                            
                            if ($senha_ok) {
                                // Criar a sessão
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["nome"] = $nome;
                                $_SESSION["email"] = $email_db;
                                $_SESSION["tipo"] = $tipo;
                                
                                $debug_log[] = "Sessão iniciada com sucesso";
                                
                                // Definir cookies para integração com JavaScript
                                setcookie("userLoggedIn", "true", time() + 86400, "/");
                                setcookie("userName", $nome, time() + 86400, "/");
                                setcookie("userEmail", $email_db, time() + 86400, "/");
                                setcookie("userType", $tipo, time() + 86400, "/");
                                setcookie("userId", $id, time() + 86400, "/");
                                setcookie("php_auth", "true", time() + 86400, "/");
                                
                                $debug_log[] = "Cookies definidos com sucesso";
                                
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
                                
                                $debug_log[] = "Redirecionamento definido para: " . $response['redirect'];
                            } else {
                                $debug_log[] = "Senha incorreta";
                                $response['message'] = "E-mail ou senha inválidos.";
                            }
                        } else {
                            $debug_log[] = "ERRO: Não foi possível buscar os dados do usuário";
                            $response['message'] = "Erro ao recuperar dados do usuário.";
                        }
                    } else {
                        $debug_log[] = "Usuário não encontrado com o email: " . $email;
                        $response['message'] = "E-mail ou senha inválidos.";
                    }
                } else {
                    $debug_log[] = "ERRO ao executar consulta: " . mysqli_error($conn);
                    $response['message'] = "Erro ao executar a consulta. Tente novamente mais tarde.";
                }
                
                // Fechar a declaração
                mysqli_stmt_close($stmt);
                $debug_log[] = "Statement SQL fechado";
            } else {
                $debug_log[] = "ERRO ao preparar consulta: " . mysqli_error($conn);
                $response['message'] = "Erro ao preparar a consulta. Tente novamente mais tarde.";
            }
        } catch (Exception $e) {
            $debug_log[] = "Exceção capturada: " . $e->getMessage();
            $response['message'] = "Erro interno do servidor. Por favor, tente novamente mais tarde.";
        }
    }
    
    // Fechar a conexão
    mysqli_close($conn);
    $debug_log[] = "Conexão com o banco de dados fechada";
} else {
    $debug_log[] = "Método de requisição inválido: " . $_SERVER["REQUEST_METHOD"];
    $response['message'] = "Método de requisição inválido. Use POST.";
}

// Finalizar o log
$debug_log[] = "Processo de login finalizado - " . date('Y-m-d H:i:s');

// Adicionar logs de depuração à resposta
$response['debug'] = $debug_log;

// Enviar a resposta
echo json_encode($response, JSON_PRETTY_PRINT);
?>
