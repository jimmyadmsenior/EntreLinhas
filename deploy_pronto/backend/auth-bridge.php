<?php
/**
 * auth-bridge.php - Ponte de autenticação entre localStorage e sessões PHP
 * 
 * Este arquivo sincroniza o estado de autenticação entre o armazenamento local do JavaScript
 * e as sessões do PHP, garantindo uma experiência consistente de usuário em todo o site.
 */

// Iniciar ou retomar a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de configuração
require_once __DIR__ . "/config.php";

// Verificar se existe um cookie de autenticação (definido pelo JavaScript)
if (isset($_COOKIE['userLoggedIn']) && $_COOKIE['userLoggedIn'] === 'true' && isset($_COOKIE['userId'])) {
    
    // Se já temos uma sessão PHP ativa com o mesmo ID de usuário, não precisamos fazer nada
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['id']) && $_SESSION['id'] == $_COOKIE['userId']) {
        // Sessão já está sincronizada, nada a fazer
        return;
    }
    
    // Buscar informações do usuário no banco de dados
    $user_id = filter_var($_COOKIE['userId'], FILTER_SANITIZE_NUMBER_INT);
    
    $sql = "SELECT id, nome, email, tipo FROM usuarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                // Vincula variáveis aos resultados
                mysqli_stmt_bind_result($stmt, $id, $nome, $email, $tipo);
                
                if (mysqli_stmt_fetch($stmt)) {
                    // Armazena os dados na sessão
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["nome"] = $nome;
                    $_SESSION["email"] = $email; 
                    $_SESSION["tipo"] = $tipo;
                    
                    // Atualiza os cookies para garantir consistência
                    setcookie("userLoggedIn", "true", time() + 86400, "/");
                    setcookie("userName", $nome, time() + 86400, "/");
                    setcookie("userEmail", $email, time() + 86400, "/");
                    setcookie("userType", $tipo, time() + 86400, "/");
                    setcookie("userId", $id, time() + 86400, "/");
                }
            }
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Se alguém estiver tentando forçar o login com o parâmetro diretamente=true (para testes)
$forceLogin = isset($_GET['diretamente']) && $_GET['diretamente'] === 'true';

if ($forceLogin) {
    // Usar ID e nome de usuário padrão para bypass
    $_SESSION["loggedin"] = true;
    $_SESSION["id"] = 2; // ID de usuário padrão
    $_SESSION["nome"] = "Jimmy Castilho"; // Nome correto do administrador
    $_SESSION["email"] = "admin@entreprojetos.com.br"; // E-mail do administrador
    $_SESSION["tipo"] = "admin";
    
    // Define cookies para JavaScript
    setcookie("userLoggedIn", "true", time() + 86400, "/");
    setcookie("userName", $_SESSION["nome"], time() + 86400, "/");
    setcookie("userEmail", $_SESSION["email"], time() + 86400, "/");
    setcookie("userType", $_SESSION["tipo"], time() + 86400, "/");
    setcookie("userId", $_SESSION["id"], time() + 86400, "/");
}

// Se o usuário estiver logado no PHP mas não no JavaScript, configurar cookies
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (!isset($_COOKIE['userLoggedIn']) || $_COOKIE['userLoggedIn'] !== 'true') {
        // Configurar cookies para JavaScript
        setcookie('userLoggedIn', 'true', time() + 86400, '/');
        setcookie('userName', $_SESSION['nome'], time() + 86400, '/');
        setcookie('userEmail', $_SESSION['email'], time() + 86400, '/');
        setcookie('userType', $_SESSION['tipo'], time() + 86400, '/');
        setcookie('userId', $_SESSION['id'], time() + 86400, '/');
    }
}

// Nota: a função is_logged_in() agora é definida apenas no session.php
