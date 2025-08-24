<?php
// Arquivo de diagnóstico para verificar se as páginas PHP estão processando sessões corretamente

// Iniciar a sessão
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Função para verificar se um arquivo existe
function arquivo_existe($caminho) {
    $existe = file_exists($caminho);
    return $existe ? "✅ Existe" : "❌ Não existe";
}

// Função para verificar se um arquivo é incluível
function arquivo_incluivel($caminho) {
    $incluivel = @include_once($caminho);
    return $incluivel ? "✅ Incluível" : "❌ Não incluível";
}

// Verificar se o helper de usuários está disponível
$helper_path = realpath("../backend/usuario_helper.php");
$helper_incluido = false;

// Tentar incluir o helper
if (file_exists($helper_path)) {
    require_once $helper_path;
    $helper_incluido = function_exists('obter_foto_perfil');
}

// Obter informações do usuário se estiver logado
$usuario_logado = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$foto_perfil = null;

if ($usuario_logado && $helper_incluido && isset($conn)) {
    $foto_perfil = obter_foto_perfil($conn, $_SESSION["id"]);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico do Sistema - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .warning {
            color: orange;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico do Sistema EntreLinhas</h1>
        
        <div class="card">
            <h2>Informações da Sessão</h2>
            <?php if ($usuario_logado): ?>
                <p class="success">✅ Usuário está logado</p>
                <table>
                    <tr>
                        <th>Propriedade</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td><?php echo $_SESSION["id"]; ?></td>
                    </tr>
                    <tr>
                        <td>Nome</td>
                        <td><?php echo htmlspecialchars($_SESSION["nome"]); ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><?php echo htmlspecialchars($_SESSION["email"]); ?></td>
                    </tr>
                    <tr>
                        <td>Tipo</td>
                        <td><?php echo isset($_SESSION["tipo"]) ? htmlspecialchars($_SESSION["tipo"]) : "Não definido"; ?></td>
                    </tr>
                    <tr>
                        <td>Foto de Perfil</td>
                        <td>
                            <?php if ($foto_perfil): ?>
                                <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <span class="warning">⚠️ Não disponível</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            <?php else: ?>
                <p class="error">❌ Nenhum usuário está logado</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Arquivos Importantes</h2>
            <table>
                <tr>
                    <th>Arquivo</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>../backend/config.php</td>
                    <td><?php echo arquivo_existe("../backend/config.php"); ?></td>
                </tr>
                <tr>
                    <td>../backend/usuario_helper.php</td>
                    <td><?php echo arquivo_existe("../backend/usuario_helper.php"); ?></td>
                </tr>
                <tr>
                    <td>includes/header.php</td>
                    <td><?php echo arquivo_existe("includes/header.php"); ?></td>
                </tr>
                <tr>
                    <td>includes/footer.php</td>
                    <td><?php echo arquivo_existe("includes/footer.php"); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h2>Banco de Dados</h2>
            <?php if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error): ?>
                <p class="success">✅ Conexão com o banco de dados estabelecida</p>
                
                <?php
                // Verificar tabelas
                $tabelas = array("usuarios", "artigos", "comentarios");
                echo "<table><tr><th>Tabela</th><th>Status</th><th>Registros</th></tr>";
                
                foreach ($tabelas as $tabela) {
                    $result = $conn->query("SHOW TABLES LIKE '$tabela'");
                    $existe = $result->num_rows > 0;
                    
                    $registros = "N/A";
                    if ($existe) {
                        $count = $conn->query("SELECT COUNT(*) as total FROM $tabela");
                        if ($count) {
                            $registros = $count->fetch_assoc()['total'];
                        }
                    }
                    
                    echo "<tr><td>$tabela</td><td>" . ($existe ? "<span class='success'>✅ Existe</span>" : "<span class='error'>❌ Não existe</span>") . "</td><td>$registros</td></tr>";
                }
                
                echo "</table>";
                
            else: ?>
                <p class="error">❌ Falha na conexão com o banco de dados</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Versões e Redirecionamentos</h2>
            <p>Verificação de páginas HTML vs PHP</p>
            <table>
                <tr>
                    <th>Página</th>
                    <th>HTML</th>
                    <th>PHP</th>
                </tr>
                <tr>
                    <td>Página Inicial</td>
                    <td><?php echo arquivo_existe("index.html") ? "✅ Existe (com redirecionamento)" : "❌ Não existe"; ?></td>
                    <td><?php echo arquivo_existe("index.php") ? "✅ Existe" : "❌ Não existe"; ?></td>
                </tr>
                <tr>
                    <td>Artigos</td>
                    <td><?php echo arquivo_existe("artigos.html") ? "✅ Existe (com redirecionamento)" : "❌ Não existe"; ?></td>
                    <td><?php echo arquivo_existe("artigos.php") ? "✅ Existe" : "❌ Não existe"; ?></td>
                </tr>
                <tr>
                    <td>Escola</td>
                    <td><?php echo arquivo_existe("escola.html") ? "✅ Existe (com redirecionamento)" : "❌ Não existe"; ?></td>
                    <td><?php echo arquivo_existe("escola.php") ? "✅ Existe" : "❌ Não existe"; ?></td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h2>Sessão Completa (Debug)</h2>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
        
        <div class="card">
            <h2>Ações</h2>
            <a href="../backend/logout.php" class="btn">Sair</a>
            <a href="login.php" class="btn">Login</a>
            <a href="index.php" class="btn">Página Inicial</a>
        </div>
    </div>
</body>
</html>
