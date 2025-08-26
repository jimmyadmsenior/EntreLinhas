<?php
// diagnostico_sistema.php
// Diagnóstico abrangente do sistema PHP e extensões
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// Funções de diagnóstico
function verificar_extensao($extensao) {
    return extension_loaded($extensao);
}

function verificar_funcao($funcao) {
    return function_exists($funcao);
}

// Lista de extensões necessárias para o sistema
$extensoes_necessarias = [
    'curl' => 'Necessário para integração com APIs externas como SendGrid',
    'mysqli' => 'Necessário para conexão com banco de dados MySQL',
    'pdo' => 'Necessário para conexões de banco de dados seguras',
    'pdo_mysql' => 'Driver MySQL para PDO',
    'json' => 'Necessário para processamento de dados JSON',
    'mbstring' => 'Necessário para manipulação de strings multibyte',
    'openssl' => 'Necessário para comunicações seguras SSL/TLS',
    'session' => 'Necessário para gerenciamento de sessões',
    'gd' => 'Útil para processamento de imagens',
    'zip' => 'Útil para manipulação de arquivos compactados'
];

// Lista de funções críticas
$funcoes_criticas = [
    'curl_init' => 'Inicialização de requisições cURL',
    'curl_exec' => 'Execução de requisições cURL',
    'json_encode' => 'Codificação de arrays/objetos para JSON',
    'json_decode' => 'Decodificação de JSON para arrays/objetos PHP',
    'file_get_contents' => 'Leitura de arquivos',
    'file_put_contents' => 'Escrita de arquivos',
    'mysqli_connect' => 'Conexão com banco de dados MySQL',
    'password_hash' => 'Hashing de senhas',
    'password_verify' => 'Verificação de senhas',
    'session_start' => 'Inicialização de sessões'
];

// Verificação de diretórios e permissões
$diretorios_verificar = [
    'uploads' => 'Diretório para upload de arquivos',
    'assets' => 'Diretório de assets estáticos',
    'backend' => 'Diretório com scripts de backend'
];

// Verifica limites do PHP
$limites_php = [
    'memory_limit' => 'Limite de memória',
    'upload_max_filesize' => 'Tamanho máximo de upload',
    'post_max_size' => 'Tamanho máximo de POST',
    'max_execution_time' => 'Tempo máximo de execução',
    'max_input_time' => 'Tempo máximo de input'
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico do Sistema - EntreLinhas</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { margin-top: 30px; color: #444; border-left: 4px solid #4285f4; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f2f2f2; }
        .ok { color: #34a853; font-weight: bold; }
        .falha { color: #ea4335; font-weight: bold; }
        .alerta { color: #fbbc05; font-weight: bold; }
        .test-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-weight: bold; margin-right: 8px; }
        .badge-success { background-color: #34a853; color: white; }
        .badge-warning { background-color: #fbbc05; color: white; }
        .badge-danger { background-color: #ea4335; color: white; }
        button { background: #4285f4; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #3367d6; }
        .details { background: #f8f9fa; padding: 10px; border-left: 4px solid #ddd; margin: 10px 0; }
        .card { border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 15px; }
        .card-header { background: #f8f9fa; margin: -15px -15px 15px; padding: 10px 15px; border-bottom: 1px solid #ddd; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico do Sistema - EntreLinhas</h1>
        
        <div class="test-section">
            <h2>Informações do PHP</h2>
            <table>
                <tr>
                    <th style="width: 30%;">Item</th>
                    <th style="width: 70%;">Valor</th>
                </tr>
                <tr>
                    <td>Versão do PHP</td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td>Sistema Operacional</td>
                    <td><?php echo PHP_OS; ?></td>
                </tr>
                <tr>
                    <td>Servidor Web</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></td>
                </tr>
                <tr>
                    <td>Interface SAPI</td>
                    <td><?php echo php_sapi_name(); ?></td>
                </tr>
                <tr>
                    <td>Diretório Raiz do Documento</td>
                    <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? getcwd(); ?></td>
                </tr>
                <tr>
                    <td>Arquivo de Configuração (php.ini)</td>
                    <td><?php echo php_ini_loaded_file(); ?></td>
                </tr>
                <tr>
                    <td>Timezone do PHP</td>
                    <td><?php echo date_default_timezone_get(); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="test-section">
            <h2>Extensões PHP</h2>
            <table>
                <tr>
                    <th style="width: 20%;">Extensão</th>
                    <th style="width: 15%;">Status</th>
                    <th style="width: 65%;">Descrição</th>
                </tr>
                <?php foreach ($extensoes_necessarias as $extensao => $descricao): ?>
                    <tr>
                        <td><?php echo $extensao; ?></td>
                        <td>
                            <?php if (verificar_extensao($extensao)): ?>
                                <span class="ok">✓ Instalada</span>
                            <?php else: ?>
                                <span class="falha">✗ Não Instalada</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $descricao; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="test-section">
            <h2>Funções Críticas</h2>
            <table>
                <tr>
                    <th style="width: 30%;">Função</th>
                    <th style="width: 15%;">Status</th>
                    <th style="width: 55%;">Propósito</th>
                </tr>
                <?php foreach ($funcoes_criticas as $funcao => $proposito): ?>
                    <tr>
                        <td><?php echo $funcao; ?>()</td>
                        <td>
                            <?php if (verificar_funcao($funcao)): ?>
                                <span class="ok">✓ Disponível</span>
                            <?php else: ?>
                                <span class="falha">✗ Indisponível</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $proposito; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="test-section">
            <h2>Diretórios do Sistema</h2>
            <table>
                <tr>
                    <th style="width: 20%;">Diretório</th>
                    <th style="width: 15%;">Existe</th>
                    <th style="width: 15%;">Permissões</th>
                    <th style="width: 50%;">Descrição</th>
                </tr>
                <?php foreach ($diretorios_verificar as $diretorio => $descricao): 
                    $caminho = __DIR__ . '/' . $diretorio;
                    $existe = is_dir($caminho);
                    $permissoes = $existe ? substr(sprintf('%o', fileperms($caminho)), -4) : 'N/A';
                    $gravavel = $existe && is_writable($caminho);
                ?>
                    <tr>
                        <td><?php echo $diretorio; ?></td>
                        <td>
                            <?php if ($existe): ?>
                                <span class="ok">✓ Sim</span>
                            <?php else: ?>
                                <span class="falha">✗ Não</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $permissoes; ?>
                            <?php if ($existe): ?>
                                <?php if ($gravavel): ?>
                                    <span class="ok">(Gravável)</span>
                                <?php else: ?>
                                    <span class="falha">(Não Gravável)</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $descricao; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="test-section">
            <h2>Limites do PHP</h2>
            <table>
                <tr>
                    <th style="width: 30%;">Configuração</th>
                    <th style="width: 20%;">Valor</th>
                    <th style="width: 50%;">Descrição</th>
                </tr>
                <?php foreach ($limites_php as $limite => $descricao): ?>
                    <tr>
                        <td><?php echo $limite; ?></td>
                        <td><?php echo ini_get($limite); ?></td>
                        <td><?php echo $descricao; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="test-section">
            <h2>Informações de Banco de Dados</h2>
            <?php
            $db_ok = false;
            $error_msg = "";
            
            // Tenta incluir o arquivo de configuração do banco de dados
            if (file_exists(__DIR__ . '/backend/config.php')) {
                try {
                    require_once __DIR__ . '/backend/config.php';
                    
                    if (isset($servername) && isset($username) && isset($password) && isset($dbname)) {
                        // Tenta conectar ao banco de dados
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        
                        if (!$conn->connect_error) {
                            $db_ok = true;
                            
                            // Obter versão do MySQL
                            $version = $conn->query("SELECT VERSION() AS version")->fetch_assoc()['version'];
                            
                            // Obter lista de tabelas
                            $tabelas_result = $conn->query("SHOW TABLES");
                            $tabelas = [];
                            if ($tabelas_result) {
                                while ($row = $tabelas_result->fetch_row()) {
                                    $tabelas[] = $row[0];
                                }
                            }
                            
                            $conn->close();
                        } else {
                            $error_msg = "Erro na conexão com o banco de dados: " . $conn->connect_error;
                        }
                    } else {
                        $error_msg = "Variáveis de configuração do banco de dados não definidas no arquivo config.php";
                    }
                } catch (Exception $e) {
                    $error_msg = "Exceção ao carregar configuração ou conectar ao banco de dados: " . $e->getMessage();
                }
            } else {
                $error_msg = "Arquivo de configuração do banco de dados não encontrado";
            }
            ?>
            
            <?php if ($db_ok): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="badge badge-success">Conectado</span> 
                        Conexão com banco de dados bem-sucedida
                    </div>
                    <p><strong>Servidor:</strong> <?php echo $servername; ?></p>
                    <p><strong>Nome do Banco:</strong> <?php echo $dbname; ?></p>
                    <p><strong>Versão MySQL:</strong> <?php echo $version; ?></p>
                    <p><strong>Tabelas Encontradas:</strong> <?php echo count($tabelas); ?></p>
                    
                    <details>
                        <summary>Lista de Tabelas</summary>
                        <div class="details">
                            <?php if (count($tabelas) > 0): ?>
                                <ul>
                                    <?php foreach($tabelas as $tabela): ?>
                                        <li><?php echo $tabela; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nenhuma tabela encontrada no banco de dados.</p>
                            <?php endif; ?>
                        </div>
                    </details>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="badge badge-danger">Erro</span>
                        Problema na conexão com o banco de dados
                    </div>
                    <p><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="test-section">
            <h2>Teste de cURL para SendGrid</h2>
            <?php
            if (function_exists('curl_init')) {
                $ch = curl_init('https://api.sendgrid.com');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para diagnóstico
                $response = curl_exec($ch);
                $curl_error = curl_error($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                
                $curl_ok = empty($curl_error);
                
                if ($curl_ok) {
                    echo '<div class="card">
                        <div class="card-header">
                            <span class="badge badge-success">Sucesso</span>
                            Teste de cURL para SendGrid bem-sucedido
                        </div>
                        <p><strong>HTTP Status:</strong> ' . $info['http_code'] . '</p>
                        <p><strong>Tempo de Resposta:</strong> ' . $info['total_time'] . ' segundos</p>
                        <p><strong>Tamanho da Resposta:</strong> ' . $info['size_download'] . ' bytes</p>
                    </div>';
                } else {
                    echo '<div class="card">
                        <div class="card-header">
                            <span class="badge badge-danger">Erro</span>
                            Erro no teste de cURL para SendGrid
                        </div>
                        <p><strong>Erro:</strong> ' . $curl_error . '</p>
                    </div>';
                }
            } else {
                echo '<div class="card">
                    <div class="card-header">
                        <span class="badge badge-danger">Indisponível</span>
                        cURL não está disponível no PHP
                    </div>
                    <p>A extensão cURL é necessária para comunicação com a API do SendGrid.</p>
                </div>';
            }
            ?>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <button onclick="location.reload();">Atualizar Diagnóstico</button>
            <p>
                <a href="diagnostico_sendgrid.php">Diagnóstico SendGrid</a> | 
                <a href="teste_sendgrid_direto.php">Teste de Envio</a> | 
                <a href="index.php">Voltar para o Site</a>
            </p>
        </div>
    </div>
</body>
</html>
