<?php
// Versão de diagnóstico para processar_artigo.php
// Este arquivo exibe erros detalhados ao processar o envio de artigos

// Exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Processamento de Artigo - Diagnóstico</h1>";

// Iniciar sessão
session_start();
echo "<p>Sessão iniciada.</p>";

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    echo "<p style='color:red'>ERRO: Usuário não está logado. Redirecionando...</p>";
    echo "<p>Variáveis de sessão disponíveis:</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    echo "<p><a href='login.php'>Ir para página de login</a></p>";
    exit;
} else {
    echo "<p>Usuário logado: ID {$_SESSION['id']}, Nome: {$_SESSION['nome']}</p>";
}

// Incluir arquivos necessários
try {
    echo "<h2>Carregando dependências:</h2>";
    
    echo "<p>Carregando config.php... ";
    require_once '../backend/config.php';
    echo "✅ OK</p>";
    
    echo "<p>Carregando artigos.php... ";
    require_once '../backend/artigos.php';
    echo "✅ OK</p>";
    
    echo "<p>Carregando email_notification.php... ";
    require_once '../backend/email_notification.php';
    echo "✅ OK</p>";
    
    echo "<p>Carregando email_integration.php... ";
    if (file_exists('../backend/email_integration.php')) {
        require_once '../backend/email_integration.php';
        echo "✅ OK</p>";
    } else {
        echo "❌ Arquivo não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>ERRO ao carregar dependências: {$e->getMessage()}</p>";
    exit;
}

// Verificar o tipo de ação (enviar ou editar)
$acao = isset($_POST['acao']) ? $_POST['acao'] : 'enviar';
echo "<p>Ação: <strong>{$acao}</strong></p>";

$mensagem = '';
$tipo_mensagem = '';

// Verificar se é um POST
echo "<h2>Dados do formulário:</h2>";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<p>Método POST detectado.</p>";
    
    // Exibir dados recebidos
    echo "<p>Campos recebidos:</p>";
    echo "<ul>";
    foreach ($_POST as $key => $value) {
        if ($key == 'conteudo') {
            echo "<li>{$key}: [Conteúdo longo - " . strlen($value) . " caracteres]</li>";
        } else {
            echo "<li>{$key}: {$value}</li>";
        }
    }
    echo "</ul>";
    
    // Validar os campos
    $titulo = !empty($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $conteudo = !empty($_POST['conteudo']) ? trim($_POST['conteudo']) : '';
    $categoria = !empty($_POST['categoria']) ? trim($_POST['categoria']) : '';
    
    // Validar dados
    echo "<h2>Validação:</h2>";
    if (empty($titulo)) {
        echo "<p style='color:red'>ERRO: Título vazio</p>";
        $mensagem = "Por favor, insira um título para o artigo.";
        $tipo_mensagem = 'danger';
    } elseif (empty($conteudo)) {
        echo "<p style='color:red'>ERRO: Conteúdo vazio</p>";
        $mensagem = "Por favor, insira o conteúdo do artigo.";
        $tipo_mensagem = 'danger';
    } elseif (empty($categoria)) {
        echo "<p style='color:red'>ERRO: Categoria não selecionada</p>";
        $mensagem = "Por favor, selecione uma categoria.";
        $tipo_mensagem = 'danger';
    } else {
        echo "<p>✅ Validação básica passou</p>";
        
        // Processar upload de imagem se existir
        echo "<h2>Upload de imagem:</h2>";
        $imagem_path = "";
        
        echo "<pre>";
        if (isset($_FILES['imagem'])) {
            echo "Dados do arquivo enviado:\n";
            print_r($_FILES['imagem']);
        } else {
            echo "Nenhuma imagem enviada.";
        }
        echo "</pre>";
        
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagem']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Verificar extensão do arquivo
            if (!in_array(strtolower($filetype), $allowed)) {
                echo "<p style='color:red'>ERRO: Tipo de arquivo não permitido</p>";
                $mensagem = "Tipo de arquivo não permitido. Apenas JPG, JPEG, PNG e GIF são aceitos.";
                $tipo_mensagem = 'danger';
            } else {
                echo "<p>✅ Tipo de arquivo válido: {$filetype}</p>";
                
                // Gerar nome único para o arquivo
                $new_filename = uniqid() . '.' . $filetype;
                $upload_dir = "../uploads/artigos/";
                
                // Criar diretório se não existir
                if (!file_exists($upload_dir)) {
                    echo "<p>Criando diretório de uploads...</p>";
                    if (mkdir($upload_dir, 0755, true)) {
                        echo "<p>✅ Diretório criado com sucesso</p>";
                    } else {
                        echo "<p style='color:red'>ERRO: Não foi possível criar o diretório</p>";
                    }
                }
                
                $upload_path = $upload_dir . $new_filename;
                
                echo "<p>Tentando fazer upload para: {$upload_path}</p>";
                
                // Mover arquivo para o diretório de uploads
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                    echo "<p>✅ Imagem enviada com sucesso</p>";
                    $imagem_path = $upload_path;
                } else {
                    echo "<p style='color:red'>ERRO: Falha no upload</p>";
                    echo "<p>Permissões do diretório: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "</p>";
                    $mensagem = "Erro ao fazer upload da imagem.";
                    $tipo_mensagem = 'danger';
                }
            }
        } else {
            echo "<p>Nenhuma imagem para processar ou erro no upload.</p>";
            if (isset($_FILES['imagem'])) {
                echo "<p>Código de erro: " . $_FILES['imagem']['error'] . "</p>";
            }
        }
        
        // Se não houver erro, preparar dados do artigo
        if (empty($mensagem)) {
            echo "<h2>Enviando artigo para o banco de dados:</h2>";
            
            // Preparar dados do artigo
            $artigo = [
                'titulo' => $titulo,
                'conteudo' => $conteudo,
                'categoria' => $categoria,
                'id_usuario' => $_SESSION['id'],
                'imagem' => $imagem_path
            ];
            
            echo "<p>Dados do artigo preparados:</p>";
            echo "<ul>";
            echo "<li>Título: {$artigo['titulo']}</li>";
            echo "<li>Categoria: {$artigo['categoria']}</li>";
            echo "<li>ID do Usuário: {$artigo['id_usuario']}</li>";
            echo "<li>Imagem: {$artigo['imagem']}</li>";
            echo "<li>Conteúdo: [" . strlen($artigo['conteudo']) . " caracteres]</li>";
            echo "</ul>";
            
            // Verificar imagens enviadas
            echo "<h3>Verificando imagens adicionais:</h3>";
            $imagens = isset($_FILES['imagens']) ? $_FILES['imagens'] : null;
            
            if ($imagens) {
                echo "<pre>";
                print_r($imagens);
                echo "</pre>";
            } else {
                echo "<p>Nenhuma imagem adicional.</p>";
            }
            
            if ($acao == 'enviar') {
                echo "<h3>Executando função enviarArtigo():</h3>";
                
                try {
                    // Enviar novo artigo
                    $resultado = enviarArtigo($conn, $artigo, $imagens);
                    
                    echo "<p>Resultado:</p>";
                    echo "<pre>";
                    print_r($resultado);
                    echo "</pre>";
                    
                    if ($resultado['status']) {
                        echo "<p>✅ Artigo enviado com sucesso. ID: " . $resultado['artigo_id'] . "</p>";
                        
                        // Obter nome do autor para o e-mail
                        $autor_nome = $_SESSION['nome'];
                        
                        // Enviar notificação por e-mail para os administradores
                        $artigo['id'] = $resultado['artigo_id'];
                        
                        echo "<h3>Enviando notificações por e-mail:</h3>";
                        
                        try {
                            // Criar diretório de logs se não existir
                            if (!is_dir('../logs')) {
                                echo "<p>Criando diretório de logs...</p>";
                                mkdir('../logs', 0777, true);
                            }
                            
                            // Registrar a tentativa de envio
                            $log_message = "[" . date('Y-m-d H:i:s') . "] Tentando enviar notificação sobre artigo ID: " . $artigo['id'];
                            error_log($log_message, 3, "../logs/email_notify.log");
                            echo "<p>Log: {$log_message}</p>";
                            
                            // Usar a nova integração de e-mail
                            $artigo['status'] = 'pendente';
                            
                            // Verificar se a função existe
                            if (function_exists('notificar_admins_novo_artigo')) {
                                echo "<p>Função notificar_admins_novo_artigo existe. Executando...</p>";
                                
                                // Enviar notificação diretamente
                                $notificacao_enviada = notificar_admins_novo_artigo($artigo, $autor_nome);
                                
                                echo "<p>Resultado da notificação: " . ($notificacao_enviada ? "✅ Sucesso" : "❌ Falha") . "</p>";
                                
                                // Registrar no log se a notificação foi enviada
                                if ($notificacao_enviada) {
                                    $log_message = "[" . date('Y-m-d H:i:s') . "] E-mail de notificação enviado para administradores sobre o artigo ID: " . $artigo['id'];
                                } else {
                                    $log_message = "[" . date('Y-m-d H:i:s') . "] Falha ao enviar e-mail de notificação para administradores sobre o artigo ID: " . $artigo['id'];
                                }
                                
                                error_log($log_message, 3, "../logs/email_notify.log");
                                echo "<p>Log: {$log_message}</p>";
                            } else {
                                echo "<p style='color:red'>Função notificar_admins_novo_artigo NÃO existe!</p>";
                                $notificacao_enviada = false;
                            }
                        } catch (Exception $e) {
                            echo "<p style='color:red'>Exceção ao enviar notificação: " . $e->getMessage() . "</p>";
                            error_log("[" . date('Y-m-d H:i:s') . "] Exceção ao enviar notificação: " . $e->getMessage(), 3, "../logs/email_notify.log");
                            $notificacao_enviada = false;
                        }
                        
                        // Redirecionar para página de sucesso
                        $_SESSION['mensagem'] = $resultado['mensagem'];
                        $_SESSION['tipo_mensagem'] = 'success';
                        $_SESSION['artigo_enviado'] = true; // Marcar que um artigo foi enviado com sucesso
                        
                        echo "<h3>Finalização:</h3>";
                        echo "<p>Variáveis de sessão definidas:</p>";
                        echo "<ul>";
                        echo "<li>mensagem: {$_SESSION['mensagem']}</li>";
                        echo "<li>tipo_mensagem: {$_SESSION['tipo_mensagem']}</li>";
                        echo "<li>artigo_enviado: {$_SESSION['artigo_enviado']}</li>";
                        echo "</ul>";
                        
                        echo "<p>O processo foi concluído com sucesso!</p>";
                        echo "<p>Em uma execução normal, você seria redirecionado para: <code>../PAGES/envio-sucesso.php</code></p>";
                        echo "<p><a href='../PAGES/envio-sucesso.php' class='btn btn-success'>Ir para página de sucesso</a></p>";
                    } else {
                        // Exibir mensagem de erro
                        echo "<p style='color:red'>ERRO: " . $resultado['mensagem'] . "</p>";
                        $mensagem = $resultado['mensagem'];
                        $tipo_mensagem = 'danger';
                    }
                } catch (Exception $e) {
                    echo "<p style='color:red'>Exceção: " . $e->getMessage() . "</p>";
                    echo "<pre>";
                    print_r($e->getTrace());
                    echo "</pre>";
                }
            } elseif ($acao == 'editar') {
                echo "<h3>Editando artigo existente:</h3>";
                // Código para edição...
            }
        }
    }
} else {
    echo "<p style='color:red'>Método inválido. Este script deve ser acessado via POST.</p>";
}

// Se chegou até aqui e há uma mensagem de erro, exibi-la
if (!empty($mensagem) && $tipo_mensagem == 'danger') {
    echo "<h2>Erro encontrado:</h2>";
    echo "<div style='padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-top: 20px;'>";
    echo "<strong>Erro:</strong> {$mensagem}";
    echo "</div>";
    
    echo "<p>Em uma execução normal, você seria redirecionado de volta para o formulário com esta mensagem de erro.</p>";
    echo "<p><a href='enviar-artigo.php'>Voltar ao formulário</a></p>";
}
?>
