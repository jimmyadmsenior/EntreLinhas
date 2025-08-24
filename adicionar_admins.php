<?php
// Adicionar novos administradores
// Este script adiciona novos usuários como administradores ou altera o tipo de usuários existentes

// Incluir arquivo de configuração
require_once "backend/config.php";

// Lista de e-mails para adicionar como administradores
$admin_emails = [
    'bianca.blanco@aluno.senai.br',
    'miguel.zacharias@aluno.senai.br'
];

echo "<h2>Adicionando novos administradores</h2>";

foreach ($admin_emails as $email) {
    // Verificar se o usuário já existe
    $sql_check = "SELECT id, nome, tipo FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql_check)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                // Usuário existe, atualizar para tipo admin
                mysqli_stmt_bind_result($stmt, $user_id, $user_nome, $user_tipo);
                mysqli_stmt_fetch($stmt);
                
                if ($user_tipo === 'admin') {
                    echo "<p>O usuário {$email} já é administrador.</p>";
                } else {
                    $update_sql = "UPDATE usuarios SET tipo = 'admin' WHERE id = ?";
                    
                    if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                        mysqli_stmt_bind_param($update_stmt, "i", $user_id);
                        
                        if (mysqli_stmt_execute($update_stmt)) {
                            echo "<p>Usuário {$email} ({$user_nome}) atualizado para administrador com sucesso!</p>";
                        } else {
                            echo "<p>Erro ao atualizar usuário {$email}: " . mysqli_error($conn) . "</p>";
                        }
                        
                        mysqli_stmt_close($update_stmt);
                    }
                }
            } else {
                // Usuário não existe, criar novo administrador com senha temporária
                $temp_password = bin2hex(random_bytes(8)); // Senha temporária
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                $nome = explode('@', $email)[0]; // Nome temporário baseado no e-mail
                $tipo = 'admin';
                
                $insert_sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
                
                if ($insert_stmt = mysqli_prepare($conn, $insert_sql)) {
                    mysqli_stmt_bind_param($insert_stmt, "ssss", $nome, $email, $hashed_password, $tipo);
                    
                    if (mysqli_stmt_execute($insert_stmt)) {
                        echo "<p>Novo administrador criado para {$email} com sucesso!</p>";
                        echo "<p>Senha temporária: {$temp_password} (anote esta senha, ela não será exibida novamente)</p>";
                    } else {
                        echo "<p>Erro ao criar administrador para {$email}: " . mysqli_error($conn) . "</p>";
                    }
                    
                    mysqli_stmt_close($insert_stmt);
                }
            }
        } else {
            echo "<p>Erro ao verificar usuário {$email}: " . mysqli_error($conn) . "</p>";
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Adicionar o seu e-mail também, caso não esteja na lista
$seu_email = 'jimmycastilho555@gmail.com';
$sql_check_you = "SELECT id, nome, tipo FROM usuarios WHERE email = ?";

if ($stmt = mysqli_prepare($conn, $sql_check_you)) {
    mysqli_stmt_bind_param($stmt, "s", $seu_email);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            // Seu usuário existe, garantir que é admin
            mysqli_stmt_bind_result($stmt, $user_id, $user_nome, $user_tipo);
            mysqli_stmt_fetch($stmt);
            
            if ($user_tipo !== 'admin') {
                $update_sql = "UPDATE usuarios SET tipo = 'admin' WHERE id = ?";
                
                if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                    mysqli_stmt_bind_param($update_stmt, "i", $user_id);
                    
                    if (mysqli_stmt_execute($update_stmt)) {
                        echo "<p>Seu usuário ({$user_nome}) atualizado para administrador com sucesso!</p>";
                    }
                    
                    mysqli_stmt_close($update_stmt);
                }
            } else {
                echo "<p>Seu usuário já é administrador.</p>";
            }
        }
    }
    
    mysqli_stmt_close($stmt);
}

echo "<p>Processo finalizado. <a href='PAGES/admin_dashboard.php'>Ir para o Painel Admin</a></p>";

// Fechar conexão
mysqli_close($conn);
?>
