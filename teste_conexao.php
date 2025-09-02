<?php
// Teste de conexão com detecção automática de ambiente
echo "<h2>Diagnóstico da Conexão</h2>";

// Verificar ambiente
$is_local = !isset($_SERVER['SERVER_NAME']) || 
            $_SERVER['SERVER_NAME'] == 'localhost' || 
            $_SERVER['SERVER_NAME'] == '127.0.0.1' ||
            strpos($_SERVER['SERVER_NAME'], '192.168.') === 0;

echo "<strong>Ambiente detectado:</strong> " . ($is_local ? "LOCAL" : "SERVIDOR") . "<br>";

// Mostrar informações do servidor
echo "<strong>Servidor:</strong> " . $_SERVER['SERVER_NAME'] . "<br>";
echo "<strong>Endereço IP:</strong> " . $_SERVER['REMOTE_ADDR'] . "<br>";

// Carregar configuração
require_once 'backend/config.php';

// Exibir configurações (SEM A SENHA por segurança)
echo "<h3>Configurações utilizadas:</h3>";
echo "Host: " . DB_SERVER . "<br>";
echo "Usuário: " . DB_USERNAME . "<br>";
echo "Banco: " . DB_NAME . "<br><br>";

try {
    // Teste simples para verificar a conexão
    $stmt = $conn->query("SELECT 1");
    echo "<div style='color:green; font-weight:bold;'>✅ Conexão PDO realizada com sucesso!</div>";
    
    // Exibir algumas informações do banco
    echo "<br>Versão do servidor: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "<br>Informações da conexão: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    
    // Verificar se as tabelas existem
    $tabelas = ['usuarios', 'artigos', 'comentarios', 'imagens_artigos'];
    echo "<h3>Verificação de tabelas:</h3>";
    echo "<ul>";
    
    foreach($tabelas as $tabela) {
        try {
            $result = $conn->query("SELECT 1 FROM $tabela LIMIT 1");
            echo "<li style='color:green;'>✅ Tabela '$tabela' existe</li>";
        } catch (PDOException $e) {
            echo "<li style='color:red;'>❌ Tabela '$tabela' não encontrada ou erro: " . $e->getMessage() . "</li>";
        }
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<div style='color:red; font-weight:bold;'>❌ Erro na conexão: " . $e->getMessage() . "</div>";
    echo "<h3>Sugestões:</h3>";
    echo "<ul>";
    if (strpos($e->getMessage(), "Access denied") !== false) {
        echo "<li>Verifique se o usuário e senha estão corretos</li>";
        echo "<li>Verifique se o usuário tem permissão para acessar o banco de dados</li>";
    }
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        echo "<li>O banco de dados não existe. Crie o banco de dados primeiro.</li>";
    }
    if (strpos($e->getMessage(), "Could not find driver") !== false) {
        echo "<li>O driver PDO para MySQL não está instalado ou habilitado.</li>";
    }
    echo "</ul>";
}
?>
