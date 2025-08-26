<?php
// Script para inicializar o banco de dados e verificar a conexão

// Incluir arquivo de configuração
require_once "config.php";

// Verificar se o banco de dados está configurado corretamente
if ($conn) {
    echo "<h2>Status da Conexão com o Banco de Dados</h2>";
    echo "<p style='color:green;'>✅ Conexão com o banco de dados estabelecida com sucesso!</p>";
    
    // Verificar se as tabelas existem
    $tabelas = ['usuarios', 'artigos', 'comentarios'];
    
    echo "<h3>Status das Tabelas</h3>";
    echo "<ul>";
    
    foreach ($tabelas as $tabela) {
        $check_query = "SHOW TABLES LIKE '$tabela'";
        $result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($result) > 0) {
            echo "<li style='color:green;'>✅ Tabela <strong>$tabela</strong> encontrada</li>";
            
            // Verificar a estrutura da tabela
            $columns_query = "SHOW COLUMNS FROM $tabela";
            $columns_result = mysqli_query($conn, $columns_query);
            
            echo "<ul>";
            while ($column = mysqli_fetch_assoc($columns_result)) {
                echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
            }
            echo "</ul>";
            
            // Contar registros na tabela
            $count_query = "SELECT COUNT(*) as total FROM $tabela";
            $count_result = mysqli_query($conn, $count_query);
            $count_data = mysqli_fetch_assoc($count_result);
            echo "<p>Total de registros: " . $count_data['total'] . "</p>";
            
        } else {
            echo "<li style='color:red;'>❌ Tabela <strong>$tabela</strong> não encontrada</li>";
        }
    }
    
    echo "</ul>";
    
    // Verificar se há um usuário administrador
    $admin_query = "SELECT * FROM usuarios WHERE email = '" . ADMIN_EMAIL . "'";
    $admin_result = mysqli_query($conn, $admin_query);
    
    echo "<h3>Status do Administrador</h3>";
    if (mysqli_num_rows($admin_result) > 0) {
        echo "<p style='color:green;'>✅ Conta de administrador encontrada</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Conta de administrador não encontrada</p>";
        echo "<p>Recomendação: Crie uma conta com o e-mail " . ADMIN_EMAIL . "</p>";
    }
    
    echo "<h3>Próximos Passos</h3>";
    echo "<ol>";
    echo "<li>Verifique as credenciais de acesso ao banco de dados no arquivo config.php</li>";
    echo "<li>Crie uma conta de usuário com o e-mail " . ADMIN_EMAIL . " para acesso administrativo</li>";
    echo "<li>Certifique-se de que as permissões de pasta estão configuradas corretamente para uploads de imagens</li>";
    echo "</ol>";
    
} else {
    echo "<h2>Erro de Conexão</h2>";
    echo "<p style='color:red;'>❌ Falha ao conectar ao banco de dados. Verifique as configurações no arquivo config.php</p>";
}

// Fechar a conexão
mysqli_close($conn);
?>
