<?php
// Script para criar contas de administrador
// Este arquivo deve ser executado uma única vez para criar os administradores principais

// Incluir arquivo de configuração
require_once "backend/config.php";

// Função para criar um administrador
function create_admin($conn, $nome, $email, $senha) {
    // Verificar se o email já existe
    $check_sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    // Se o email já existe, atualizar para admin
    if(mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        // Atualizar para administrador
        $update_sql = "UPDATE usuarios SET tipo = 'admin', nome = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "si", $nome, $id);
        $result = mysqli_stmt_execute($stmt);
        
        if($result) {
            return "Usuário $nome ($email) atualizado para administrador com ID: $id";
        } else {
            return "Erro ao atualizar usuário $email: " . mysqli_error($conn);
        }
    } 
    // Caso contrário, criar novo administrador
    else {
        mysqli_stmt_close($stmt);
        
        // Hash da senha
        $hashed_password = password_hash($senha, PASSWORD_DEFAULT);
        
        // Inserir novo usuário
        $insert_sql = "INSERT INTO usuarios (nome, email, senha, tipo, status, data_registro) 
                       VALUES (?, ?, ?, 'admin', 'ativo', NOW())";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "sss", $nome, $email, $hashed_password);
        $result = mysqli_stmt_execute($stmt);
        
        if($result) {
            $id = mysqli_insert_id($conn);
            return "Novo administrador $nome ($email) criado com ID: $id";
        } else {
            return "Erro ao criar administrador $email: " . mysqli_error($conn);
        }
    }
}

// Verificar se o script está sendo acessado via navegador ou linha de comando
$is_cli = (php_sapi_name() == 'cli');

// Definir senhas seguras para os admins
$admin1 = [
    'nome' => 'Jimmy Castilho',
    'email' => 'jimmycastilho555@gmail.com',
    'senha' => 'AdminJimmy@2025#' // Senha segura e única
];

$admin2 = [
    'nome' => 'Admin 2', // Atualizar quando tiver o nome
    'email' => 'admin2@exemplo.com', // Atualizar quando tiver o email
    'senha' => 'AdminEntreLinhas2#' // Senha segura e única
];

$admin3 = [
    'nome' => 'Admin 3', // Atualizar quando tiver o nome
    'email' => 'admin3@exemplo.com', // Atualizar quando tiver o email
    'senha' => 'AdminEntreLinhas3#' // Senha segura e única
];

// Array de resultados
$results = [];

// Criar os administradores
$results[] = create_admin($conn, $admin1['nome'], $admin1['email'], $admin1['senha']);
$results[] = "Senha para {$admin1['nome']}: {$admin1['senha']}";

$results[] = create_admin($conn, $admin2['nome'], $admin2['email'], $admin2['senha']);
$results[] = "Senha para {$admin2['nome']}: {$admin2['senha']}";

$results[] = create_admin($conn, $admin3['nome'], $admin3['email'], $admin3['senha']);
$results[] = "Senha para {$admin3['nome']}: {$admin3['senha']}";

// Fechar conexão
mysqli_close($conn);

// Exibir resultados
if($is_cli) {
    // Saída para terminal
    foreach($results as $result) {
        echo $result . PHP_EOL;
    }
} else {
    // Saída para navegador
    echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Criar Administradores - EntreLinhas</title>
    <link rel='stylesheet' href='assets/css/style.css'>
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .result {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #e9f7ef;
            border-left: 4px solid #28a745;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .password {
            font-family: monospace;
            padding: 5px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        h1 {
            color: #343a40;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Criação de Administradores</h1>
        
        <div class='warning'>
            <strong>Importante:</strong> Este script só deve ser executado uma vez. 
            Anote as senhas e depois delete este arquivo do servidor por questões de segurança.
        </div>
        
        <div class='results'>";
        
    foreach($results as $result) {
        echo "<div class='result'>" . htmlspecialchars($result) . "</div>";
    }
        
    echo "</div>
        
        <p><strong>Próximos passos:</strong></p>
        <ol>
            <li>Anote as senhas dos administradores</li>
            <li>Compartilhe as senhas de forma segura com os outros administradores</li>
            <li><strong>Delete este arquivo do servidor</strong></li>
            <li>Faça login no sistema com suas credenciais de administrador</li>
        </ol>
        
        <div style='margin-top: 20px;'>
            <a href='index.html' style='text-decoration: none; padding: 10px 15px; background-color: #007bff; color: white; border-radius: 5px;'>Voltar para o início</a>
        </div>
    </div>
</body>
</html>";
}
?>
