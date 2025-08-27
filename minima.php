<?php
// Versão super simplificada apenas para teste
// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>EntreLinhas - Versão Mínima</h1>";
echo "<p>Esta é uma versão mínima da página para testar a configuração.</p>";

// Testar a conexão com o banco de dados
try {
    // Usar localhost para conexão no servidor
    $conn = new mysqli("localhost", "if0_39798697", "xKIcJzBS13BB50t", "if0_39798697_entrelinhas");
    
    // Verificar conexão
    if ($conn->connect_error) {
        echo "<p style='color:red'>Erro de conexão: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color:green'>Conexão com o banco de dados bem-sucedida!</p>";
        
        // Tenta buscar alguns artigos
        $sql = "SELECT COUNT(*) as total FROM artigos";
        $result = $conn->query($sql);
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Total de artigos no banco: " . $row['total'] . "</p>";
        } else {
            echo "<p>Erro ao consultar artigos: " . $conn->error . "</p>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exceção: " . $e->getMessage() . "</p>";
}
?>
