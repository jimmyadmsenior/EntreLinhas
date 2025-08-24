<?php
// Arquivo de teste PHP simples
echo "<h1>Teste PHP</h1>";
echo "<p>Este é um arquivo PHP de teste. Se você está vendo esta mensagem, o PHP está funcionando corretamente.</p>";
echo "<p>Hora atual: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";

// Teste de conexão com o banco de dados
echo "<h2>Teste de Conexão com o Banco de Dados</h2>";
require_once "backend/config.php";

if (isset($conn) && $conn) {
    echo "<p style='color:green'>Conexão com o banco de dados bem-sucedida!</p>";
    
    // Testar consulta simples
    $sql = "SHOW TABLES";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        echo "<p>Tabelas no banco de dados:</p>";
        echo "<ul>";
        while ($row = mysqli_fetch_row($result)) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>Erro na consulta: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:red'>Falha na conexão com o banco de dados.</p>";
}

// Verificar sessão
echo "<h2>Teste de Sessão</h2>";
session_start();
echo "<p>ID da sessão: " . session_id() . "</p>";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo "<p>Usuário logado: " . $_SESSION["nome"] . " (ID: " . $_SESSION["id"] . ")</p>";
} else {
    echo "<p>Nenhum usuário logado.</p>";
}

// Lista de links para testar
echo "<h2>Links para Testar</h2>";
echo "<ul>";
echo "<li><a href='PAGES/index.php'>Página Inicial</a></li>";
echo "<li><a href='PAGES/contato.php'>Página de Contato</a></li>";
echo "<li><a href='PAGES/auth-bridge.php'>Auth Bridge</a></li>";
echo "<li><a href='PAGES/login.php'>Login</a></li>";
echo "<li><a href='adicionar_resumo.php'>Adicionar Coluna Resumo</a></li>";
echo "<li><a href='verificar_tabelas.php'>Verificar Tabelas</a></li>";
echo "</ul>";
?>
