<?php
/**
 * EntreLinhas - Sistema de E-mail Universal
 * 
 * Esta classe tenta enviar e-mails usando diferentes métodos disponíveis,
 * priorizando na seguinte ordem:
 * 1. SendGrid API (se configurado)
 * 2. PHPMailer (se instalado)
 * 3. mail() nativo do PHP
 */
class EmailUniversal {
    // Métodos disponíveis
    const METODO_SENDGRID = 'sendgrid';
    const METODO_PHPMAILER = 'phpmailer';
    const METODO_MAIL = 'mail';
    
    // Configurações
    private $metodo_preferido = null;
    public $configuracoes = [];
    private $log_file = '';
    
    /**
     * Construtor
     * 
     * @param string $metodo_preferido Método preferido (sendgrid, phpmailer, mail)
     * @param array $configuracoes Configurações específicas para cada método
     */
    public function __construct($metodo_preferido = null, $configuracoes = []) {
        $this->log_file = __DIR__ . '/logs/email_universal.log';
        
        // Criar diretório de logs se não existir
        if (!is_dir(dirname($this->log_file))) {
            mkdir(dirname($this->log_file), 0755, true);
        }
        
        // Configurar método preferido
        $this->metodo_preferido = $metodo_preferido;
        
        // Configurações padrão
        $this->configuracoes = array_merge([
            'sendgrid' => [
                'api_key' => '',
                'from_email' => 'jimmycastilho555@gmail.com',
                'from_name' => 'EntreLinhas'
            ],
            'phpmailer' => [
                'smtp_host' => '',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_secure' => 'tls',
                'from_email' => 'jimmycastilho555@gmail.com',
                'from_name' => 'EntreLinhas'
            ],
            'mail' => [
                'from_email' => 'jimmycastilho555@gmail.com',
                'from_name' => 'EntreLinhas'
            ]
        ], $configuracoes);
        
        // Tentar carregar configurações do arquivo
        $this->carregarConfiguracoes();
        
        $this->log('EmailUniversal inicializado');
    }
    
    /**
     * Carregar configurações de arquivo
     */
    private function carregarConfiguracoes() {
        // Tentar carregar configuração do SendGrid
        if (file_exists(__DIR__ . '/backend/config.php')) {
            include_once __DIR__ . '/backend/config.php';
            if (isset($sendgrid_api_key)) {
                $this->configuracoes['sendgrid']['api_key'] = $sendgrid_api_key;
                $this->log('Configuração do SendGrid carregada do arquivo config.php');
            }
        }
        
        // Tentar carregar arquivo específico de configuração de e-mail
        if (file_exists(__DIR__ . '/backend/email_config.php')) {
            include_once __DIR__ . '/backend/email_config.php';
            if (isset($email_config) && is_array($email_config)) {
                foreach ($email_config as $metodo => $config) {
                    if (isset($this->configuracoes[$metodo])) {
                        $this->configuracoes[$metodo] = array_merge(
                            $this->configuracoes[$metodo],
                            $config
                        );
                    }
                }
                $this->log('Configurações carregadas de email_config.php');
            }
        }
    }
    
    /**
     * Verifica disponibilidade dos métodos de envio
     * 
     * @return array Lista de métodos disponíveis
     */
    public function metodosDisponiveis() {
        $metodos = [];
        
        // Verificar SendGrid
        if ($this->verificarSendGrid()) {
            $metodos[] = self::METODO_SENDGRID;
        }
        
        // Verificar PHPMailer
        if ($this->verificarPhpMailer()) {
            $metodos[] = self::METODO_PHPMAILER;
        }
        
        // Verificar mail() nativo
        if ($this->verificarMail()) {
            $metodos[] = self::METODO_MAIL;
        }
        
        return $metodos;
    }
    
    /**
     * Verifica se o SendGrid está disponível
     */
    private function verificarSendGrid() {
        if (empty($this->configuracoes['sendgrid']['api_key'])) {
            return false;
        }
        
        if (!function_exists('curl_init')) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Verifica se o PHPMailer está disponível
     */
    private function verificarPhpMailer() {
        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
            return false;
        }
        
        // Verificar se as classes do PHPMailer estão disponíveis
        @include_once __DIR__ . '/vendor/autoload.php';
        
        return class_exists('PHPMailer\\PHPMailer\\PHPMailer');
    }
    
    /**
     * Verifica se a função mail() está disponível
     */
    private function verificarMail() {
        return function_exists('mail');
    }
    
    /**
     * Envia e-mail pelo método disponível
     * 
     * @param string $para E-mail do destinatário
     * @param string $assunto Assunto do e-mail
     * @param string $mensagem Conteúdo do e-mail (HTML)
     * @param array $opcoes Opções adicionais
     * @return array Resultado do envio
     */
    public function enviar($para, $assunto, $mensagem, $opcoes = []) {
        $this->log("Iniciando envio de e-mail para {$para}");
        
        // Definir ordem dos métodos de envio
        $metodos_disponiveis = $this->metodosDisponiveis();
        
        if (empty($metodos_disponiveis)) {
            $this->log("Erro: Nenhum método de envio disponível");
            return [
                'sucesso' => false,
                'erro' => 'Nenhum método de envio disponível',
                'metodo' => null
            ];
        }
        
        // Se um método preferido foi especificado e está disponível, usá-lo primeiro
        if ($this->metodo_preferido && in_array($this->metodo_preferido, $metodos_disponiveis)) {
            // Reorganizar para que o método preferido seja o primeiro
            $metodos_disponiveis = array_diff($metodos_disponiveis, [$this->metodo_preferido]);
            array_unshift($metodos_disponiveis, $this->metodo_preferido);
        }
        
        $this->log("Métodos disponíveis: " . implode(', ', $metodos_disponiveis));
        
        // Tentar cada método até que um funcione
        foreach ($metodos_disponiveis as $metodo) {
            $this->log("Tentando enviar usando {$metodo}");
            
            $resultado = null;
            
            switch ($metodo) {
                case self::METODO_SENDGRID:
                    $resultado = $this->enviarViaSendGrid($para, $assunto, $mensagem, $opcoes);
                    break;
                case self::METODO_PHPMAILER:
                    $resultado = $this->enviarViaPhpMailer($para, $assunto, $mensagem, $opcoes);
                    break;
                case self::METODO_MAIL:
                    $resultado = $this->enviarViaMail($para, $assunto, $mensagem, $opcoes);
                    break;
            }
            
            if ($resultado && $resultado['sucesso']) {
                $this->log("E-mail enviado com sucesso usando {$metodo}");
                return $resultado;
            } else {
                $erro = $resultado['erro'] ?? 'Erro desconhecido';
                $this->log("Falha ao enviar usando {$metodo}: {$erro}");
            }
        }
        
        $this->log("Todos os métodos de envio falharam");
        
        return [
            'sucesso' => false,
            'erro' => 'Todos os métodos de envio falharam',
            'metodo' => null,
            'detalhes' => 'Verifique o log para mais informações'
        ];
    }
    
    /**
     * Envia e-mail via SendGrid
     */
    private function enviarViaSendGrid($para, $assunto, $mensagem, $opcoes = []) {
        try {
            $api_key = $this->configuracoes['sendgrid']['api_key'];
            $de_email = $opcoes['de_email'] ?? $this->configuracoes['sendgrid']['from_email'];
            $de_nome = $opcoes['de_nome'] ?? $this->configuracoes['sendgrid']['from_name'];
            
            $data = [
                'personalizations' => [
                    [
                        'to' => [
                            [
                                'email' => $para
                            ]
                        ],
                        'subject' => $assunto
                    ]
                ],
                'from' => [
                    'email' => $de_email,
                    'name' => $de_nome
                ],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $mensagem
                    ],
                    [
                        'type' => 'text/plain',
                        'value' => strip_tags($mensagem)
                    ]
                ]
            ];
            
            // Adicionar CC se fornecido
            if (!empty($opcoes['cc'])) {
                $cc = is_array($opcoes['cc']) ? $opcoes['cc'] : [$opcoes['cc']];
                $cc_array = [];
                foreach ($cc as $cc_email) {
                    $cc_array[] = ['email' => $cc_email];
                }
                $data['personalizations'][0]['cc'] = $cc_array;
            }
            
            // Adicionar BCC se fornecido
            if (!empty($opcoes['bcc'])) {
                $bcc = is_array($opcoes['bcc']) ? $opcoes['bcc'] : [$opcoes['bcc']];
                $bcc_array = [];
                foreach ($bcc as $bcc_email) {
                    $bcc_array[] = ['email' => $bcc_email];
                }
                $data['personalizations'][0]['bcc'] = $bcc_array;
            }
            
            $json_data = json_encode($data);
            
            $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($http_code == 202) {
                return [
                    'sucesso' => true,
                    'metodo' => self::METODO_SENDGRID,
                    'codigo' => $http_code
                ];
            } else {
                return [
                    'sucesso' => false,
                    'erro' => $curl_error ?: "Código HTTP: {$http_code}",
                    'resposta' => $response,
                    'metodo' => self::METODO_SENDGRID
                ];
            }
        } catch (Exception $e) {
            return [
                'sucesso' => false,
                'erro' => 'Exceção: ' . $e->getMessage(),
                'metodo' => self::METODO_SENDGRID
            ];
        }
    }
    
    /**
     * Envia e-mail via PHPMailer
     */
    private function enviarViaPhpMailer($para, $assunto, $mensagem, $opcoes = []) {
        try {
            // Importar PHPMailer
            if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                require_once __DIR__ . '/vendor/autoload.php';
            }
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configurar SMTP
            $mail->isSMTP();
            $mail->Host       = $this->configuracoes['phpmailer']['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->configuracoes['phpmailer']['smtp_username'];
            $mail->Password   = $this->configuracoes['phpmailer']['smtp_password'];
            $mail->SMTPSecure = $this->configuracoes['phpmailer']['smtp_secure'];
            $mail->Port       = $this->configuracoes['phpmailer']['smtp_port'];
            
            // Remetente e destinatário
            $de_email = $opcoes['de_email'] ?? $this->configuracoes['phpmailer']['from_email'];
            $de_nome = $opcoes['de_nome'] ?? $this->configuracoes['phpmailer']['from_name'];
            
            $mail->setFrom($de_email, $de_nome);
            $mail->addAddress($para);
            
            // Adicionar CC se fornecido
            if (!empty($opcoes['cc'])) {
                $cc = is_array($opcoes['cc']) ? $opcoes['cc'] : [$opcoes['cc']];
                foreach ($cc as $cc_email) {
                    $mail->addCC($cc_email);
                }
            }
            
            // Adicionar BCC se fornecido
            if (!empty($opcoes['bcc'])) {
                $bcc = is_array($opcoes['bcc']) ? $opcoes['bcc'] : [$opcoes['bcc']];
                foreach ($bcc as $bcc_email) {
                    $mail->addBCC($bcc_email);
                }
            }
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $mensagem;
            $mail->AltBody = strip_tags($mensagem);
            
            $mail->send();
            
            return [
                'sucesso' => true,
                'metodo' => self::METODO_PHPMAILER
            ];
        } catch (Exception $e) {
            return [
                'sucesso' => false,
                'erro' => 'Exceção PHPMailer: ' . $e->getMessage(),
                'metodo' => self::METODO_PHPMAILER
            ];
        }
    }
    
    /**
     * Envia e-mail via função mail() nativa do PHP
     */
    private function enviarViaMail($para, $assunto, $mensagem, $opcoes = []) {
        try {
            $de_email = $opcoes['de_email'] ?? $this->configuracoes['mail']['from_email'];
            $de_nome = $opcoes['de_nome'] ?? $this->configuracoes['mail']['from_name'];
            
            // Cabeçalhos
            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
            $headers[] = "From: {$de_nome} <{$de_email}>";
            $headers[] = "Reply-To: {$de_email}";
            $headers[] = 'X-Mailer: PHP/' . phpversion();
            
            // Adicionar CC se fornecido
            if (!empty($opcoes['cc'])) {
                $cc = is_array($opcoes['cc']) ? $opcoes['cc'] : [$opcoes['cc']];
                $headers[] = 'Cc: ' . implode(',', $cc);
            }
            
            // Adicionar BCC se fornecido
            if (!empty($opcoes['bcc'])) {
                $bcc = is_array($opcoes['bcc']) ? $opcoes['bcc'] : [$opcoes['bcc']];
                $headers[] = 'Bcc: ' . implode(',', $bcc);
            }
            
            // Enviar e-mail
            $resultado = mail($para, $assunto, $mensagem, implode("\r\n", $headers));
            
            if ($resultado) {
                return [
                    'sucesso' => true,
                    'metodo' => self::METODO_MAIL
                ];
            } else {
                return [
                    'sucesso' => false,
                    'erro' => 'Falha na função mail()',
                    'metodo' => self::METODO_MAIL
                ];
            }
        } catch (Exception $e) {
            return [
                'sucesso' => false,
                'erro' => 'Exceção: ' . $e->getMessage(),
                'metodo' => self::METODO_MAIL
            ];
        }
    }
    
    /**
     * Registra uma mensagem no arquivo de log
     */
    private function log($mensagem) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$mensagem}\n";
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Define o método preferido de envio
     */
    public function definirMetodoPreferido($metodo) {
        $this->metodo_preferido = $metodo;
    }
    
    /**
     * Define uma configuração específica
     */
    public function definirConfiguracao($metodo, $chave, $valor) {
        if (isset($this->configuracoes[$metodo])) {
            $this->configuracoes[$metodo][$chave] = $valor;
        }
    }
}


// Se este arquivo for chamado diretamente, mostrar interface de teste
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    
    // Verificar se há dados de formulário
    $mensagem_resultado = null;
    $resultado_detalhe = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar'])) {
        $para = $_POST['para'] ?? '';
        $assunto = $_POST['assunto'] ?? '';
        $mensagem = $_POST['mensagem'] ?? '';
        $metodo = $_POST['metodo'] ?? null;
        
        if (!empty($para) && !empty($mensagem)) {
            // Criar instância com o método selecionado
            $email = new EmailUniversal($metodo);
            
            // Definir configurações específicas se fornecidas
            if (isset($_POST['sendgrid_api_key']) && !empty($_POST['sendgrid_api_key'])) {
                $email->definirConfiguracao('sendgrid', 'api_key', $_POST['sendgrid_api_key']);
            }
            
            if (isset($_POST['smtp_host']) && !empty($_POST['smtp_host'])) {
                $email->definirConfiguracao('phpmailer', 'smtp_host', $_POST['smtp_host']);
                $email->definirConfiguracao('phpmailer', 'smtp_port', $_POST['smtp_port']);
                $email->definirConfiguracao('phpmailer', 'smtp_username', $_POST['smtp_username']);
                $email->definirConfiguracao('phpmailer', 'smtp_password', $_POST['smtp_password']);
            }
            
            // Enviar e-mail
            $resultado = $email->enviar($para, $assunto, $mensagem);
            
            if ($resultado['sucesso']) {
                $mensagem_resultado = "E-mail enviado com sucesso usando o método {$resultado['metodo']}.";
            } else {
                $mensagem_resultado = "Falha ao enviar e-mail: {$resultado['erro']}";
            }
            
            $resultado_detalhe = $resultado;
        }
    }
    
    // Criar instância para verificar métodos disponíveis
    $email = new EmailUniversal();
    $metodos_disponiveis = $email->metodosDisponiveis();
?>
<!DOCTYPE html>
<html>
<head>
    <title>E-mail Universal - EntreLinhas</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { color: #444; margin-top: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], textarea, select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 200px; }
        .buttons { margin-top: 20px; }
        button, input[type="submit"] { background: #4285f4; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover, input[type="submit"]:hover { background: #3367d6; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .message.success { background: #e6f4ea; border-left: 5px solid #34a853; }
        .message.error { background: #fce8e6; border-left: 5px solid #ea4335; }
        .method-settings { display: none; padding: 15px; margin: 15px 0; background: #f8f9fa; border-radius: 5px; }
        .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px; }
        .card-header { background: #f2f2f2; margin: -15px -15px 15px; padding: 10px 15px; border-bottom: 1px solid #ddd; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
    <script>
        function mostrarConfiguracao() {
            // Esconder todas as configurações
            document.querySelectorAll('.method-settings').forEach(function(el) {
                el.style.display = 'none';
            });
            
            // Mostrar configuração do método selecionado
            var metodo = document.getElementById('metodo').value;
            var configuracao = document.getElementById(metodo + '-settings');
            if (configuracao) {
                configuracao.style.display = 'block';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Sistema de E-mail Universal - EntreLinhas</h1>
        
        <?php if ($mensagem_resultado): ?>
            <div class="message <?php echo strpos($mensagem_resultado, 'sucesso') !== false ? 'success' : 'error'; ?>">
                <p><?php echo $mensagem_resultado; ?></p>
                
                <?php if ($resultado_detalhe): ?>
                    <pre><?php print_r($resultado_detalhe); ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">Métodos de Envio Disponíveis</h3>
            </div>
            <ul>
                <?php foreach ($metodos_disponiveis as $metodo): ?>
                    <li>
                        <strong><?php echo $metodo; ?></strong>
                        <?php if ($metodo === EmailUniversal::METODO_SENDGRID): ?>
                            - API de envio de e-mails da Twilio
                        <?php elseif ($metodo === EmailUniversal::METODO_PHPMAILER): ?>
                            - Biblioteca PHP para envio via SMTP
                        <?php elseif ($metodo === EmailUniversal::METODO_MAIL): ?>
                            - Função nativa do PHP
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                
                <?php if (empty($metodos_disponiveis)): ?>
                    <li><strong class="message error">Nenhum método de envio disponível. Verifique as configurações.</strong></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <h2>Enviar E-mail de Teste</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="metodo">Método de Envio:</label>
                <select id="metodo" name="metodo" onchange="mostrarConfiguracao()">
                    <option value="">Automático (usar o primeiro disponível)</option>
                    <?php foreach ($metodos_disponiveis as $metodo): ?>
                        <option value="<?php echo $metodo; ?>"><?php echo $metodo; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="sendgrid-settings" class="method-settings">
                <h3>Configurações do SendGrid</h3>
                <div class="form-group">
                    <label for="sendgrid_api_key">API Key:</label>
                    <input type="text" id="sendgrid_api_key" name="sendgrid_api_key" placeholder="SG.xxxxx" value="<?php echo $email->configuracoes['sendgrid']['api_key'] ?? 'CHAVE_SENDGRID_REMOVIDA'; ?>">
                </div>
            </div>
            
            <div id="phpmailer-settings" class="method-settings">
                <h3>Configurações do PHPMailer</h3>
                <div class="form-group">
                    <label for="smtp_host">Servidor SMTP:</label>
                    <input type="text" id="smtp_host" name="smtp_host" placeholder="smtp.exemplo.com" value="<?php echo $email->configuracoes['phpmailer']['smtp_host'] ?? 'smtp.gmail.com'; ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_port">Porta SMTP:</label>
                    <input type="text" id="smtp_port" name="smtp_port" value="<?php echo $email->configuracoes['phpmailer']['smtp_port'] ?? '587'; ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_username">Usuário SMTP:</label>
                    <input type="text" id="smtp_username" name="smtp_username" placeholder="seu@email.com">
                </div>
                <div class="form-group">
                    <label for="smtp_password">Senha SMTP:</label>
                    <input type="password" id="smtp_password" name="smtp_password" placeholder="sua_senha">
                </div>
            </div>
            
            <div id="mail-settings" class="method-settings">
                <h3>Configurações do mail()</h3>
                <p>A função mail() do PHP usa a configuração do servidor. Não são necessárias configurações adicionais.</p>
            </div>
            
            <h3>Detalhes do E-mail</h3>
            <div class="form-group">
                <label for="para">E-mail do Destinatário:</label>
                <input type="email" id="para" name="para" required placeholder="destinatario@exemplo.com">
            </div>
            
            <div class="form-group">
                <label for="assunto">Assunto:</label>
                <input type="text" id="assunto" name="assunto" value="Teste de E-mail Universal do EntreLinhas">
            </div>
            
            <div class="form-group">
                <label for="mensagem">Mensagem (HTML permitido):</label>
                <textarea id="mensagem" name="mensagem"><h2>Teste de E-mail Universal</h2><p>Olá,</p><p>Esta é uma mensagem de teste enviada pelo sistema EntreLinhas usando o módulo de E-mail Universal.</p><p>Data e hora do envio: <?php echo date('Y-m-d H:i:s'); ?></p><p>Atenciosamente,<br>Sistema EntreLinhas</p></textarea>
            </div>
            
            <div class="buttons">
                <input type="submit" name="enviar" value="Enviar E-mail">
            </div>
        </form>
        
        <h2>Como Usar no Código</h2>
        <pre>
// Importar a classe
require_once 'email_universal.php';

// Criar instância (método automático)
$email = new EmailUniversal();

// OU especificar um método preferido
// $email = new EmailUniversal(EmailUniversal::METODO_SENDGRID);

// Enviar e-mail
$resultado = $email->enviar(
    'destinatario@exemplo.com',
    'Assunto do E-mail',
    '&lt;h2&gt;Conteúdo HTML do e-mail&lt;/h2&gt;&lt;p&gt;Olá!&lt;/p&gt;',
    [
        'cc' => ['copia@exemplo.com'],
        'bcc' => ['copia.oculta@exemplo.com']
    ]
);

// Verificar resultado
if ($resultado['sucesso']) {
    echo "E-mail enviado com sucesso usando o método: " . $resultado['metodo'];
} else {
    echo "Erro ao enviar e-mail: " . $resultado['erro'];
}
</pre>
        
        <div style="margin-top: 30px; text-align: center;">
            <p>
                <a href="index_email_teste.php">Voltar para Central de E-mail</a> | 
                <a href="index.php">Voltar ao Site Principal</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php
}
?>
