<?php
// Habilitar relatório de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Endpoint para processamento de cadastro via AJAX
header('Content-Type: application/json');

// Iniciar sessão
session_start();

// Verificar se o arquivo de configuração existe
if (!file_exists("../backend/config.php")) {
    echo json_encode(['success' => false, 'message' => 'Arquivo de configuração não encontrado']);
    exit;
}

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Variável para armazenar a resposta
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Verificar se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obter e limpar os dados da requisição
    $nome = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["password"] ?? "";
    
    $errors = [];
    
    // Validar nome
    if (empty($nome)) {
        $errors['name'] = "Por favor, insira um nome.";
    }
    
    // Validar email
    if (empty($email)) {
        $errors['email'] = "Por favor, insira um e-mail.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Por favor, insira um e-mail válido.";
    } else {
        // Verificar se o email já está em uso
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $errors['email'] = "Este e-mail já está em uso.";
                }
            } else {
                $response['message'] = "Erro ao executar a consulta. Tente novamente mais tarde.";
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = "Erro ao preparar a consulta. Tente novamente mais tarde.";
        }
    }
    
    // Validar senha
    if (empty($senha)) {
        $errors['password'] = "Por favor, insira uma senha.";
    } elseif (strlen($senha) < 6) {
        $errors['password'] = "A senha deve ter pelo menos 6 caracteres.";
    }
    
    // Se não houver erros, prosseguir com o cadastro
    if (empty($errors)) {
        // Preparar a inserção
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo, status) VALUES (?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Definir tipo e status padrão
            $tipo = "aluno"; // aluno, professor ou admin
            $status = "ativo"; // pendente, ativo ou inativo
            
            // Vincular parâmetros
            mysqli_stmt_bind_param($stmt, "sssss", $nome, $email, $senha_hash, $tipo, $status);
            
            // Criar hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Executar a inserção
            if (mysqli_stmt_execute($stmt)) {
                // Definir usuário na sessão também
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['user_name'] = $nome;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type'] = $tipo;
                
                // Registrar no log
                error_log("Usuário cadastrado com sucesso: $nome ($email)");
                
                $response['success'] = true;
                $response['message'] = "Cadastro realizado com sucesso!";
                $response['redirect'] = "cadastro-sucesso.html";
            } else {
                $response['message'] = "Erro ao realizar o cadastro. Tente novamente mais tarde.";
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = "Erro ao preparar a inserção. Tente novamente mais tarde.";
        }
    } else {
        $response['errors'] = $errors;
        $response['message'] = "Por favor, corrija os erros no formulário.";
    }
    
    // Fechar conexão
    mysqli_close($conn);
} else {
    $response['message'] = "Método de requisição inválido.";
}

// Enviar resposta
echo json_encode($response);
?>
