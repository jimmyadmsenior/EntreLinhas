<?php
/**
 * Dashboard de Notificações
 * 
 * Esta página mostra as notificações de email que foram enviadas ou simuladas
 * e permite gerenciar o sistema de emails do EntreLinhas
 */

// Verificar se o usuário está logado como administrador
session_start();
require_once 'backend/config.php';
require_once 'backend/auth-bridge.php';

// Verificar se o usuário tem permissão de administrador
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: PAGES/login.php?redirect=notificacoes_dashboard.php');
    exit;
}

// Conectar ao banco de dados
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Erro de conexão com o banco de dados: " . $conn->connect_error);
}

// Função para verificar se a tabela de logs de emails existe
function tabela_logs_existe($conn) {
    $sql = "SHOW TABLES LIKE 'email_log'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Criar tabela de logs de emails se não existir
function criar_tabela_logs($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS email_log (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        artigo_id INT(11) NULL,
        destinatario VARCHAR(255) NOT NULL,
        assunto VARCHAR(255) NOT NULL,
        conteudo TEXT,
        status_envio ENUM('sucesso', 'falha', 'simulado') NOT NULL,
        metodo_envio VARCHAR(20) DEFAULT 'sendgrid',
        data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        detalhes TEXT NULL
    )";
    
    if ($conn->query($sql) === TRUE) {
        return true;
    }
    return false;
}

// Verificar se a tabela existe, senão criar
if (!tabela_logs_existe($conn)) {
    criar_tabela_logs($conn);
}

// Consultar os logs de email
$sql = "SELECT * FROM email_log ORDER BY data_envio DESC LIMIT 100";
$logs = [];

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

// Contar estatísticas
$total_enviados = 0;
$total_falhas = 0;
$total_simulados = 0;

if (count($logs) > 0) {
    foreach ($logs as $log) {
        switch ($log['status_envio']) {
            case 'sucesso':
                $total_enviados++;
                break;
            case 'falha':
                $total_falhas++;
                break;
            case 'simulado':
                $total_simulados++;
                break;
        }
    }
}

// Cabeçalho HTML
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Notificações - EntreLinhas</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            font-weight: bold;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }
        
        .status-sucesso {
            background-color: #28a745;
            color: white;
        }
        
        .status-falha {
            background-color: #dc3545;
            color: white;
        }
        
        .status-simulado {
            background-color: #ffc107;
            color: #212529;
        }
        
        .notification-detail {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        
        .notification-content {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 10px;
            background-color: white;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .stats-card {
            text-align: center;
            padding: 15px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .banner-warning {
            background-color: #ffeeba;
            color: #856404;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4"><i class="fas fa-bell me-2"></i> Dashboard de Notificações</h1>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $_GET['success']; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_GET['error']; ?>
        </div>
        <?php endif; ?>

        <?php
        // Verificar se está em ambiente de desenvolvimento
        $host = $_SERVER['SERVER_NAME'];
        $is_dev = ($host == 'localhost' || strpos($host, '127.0.0.1') !== false);
        if ($is_dev):
        ?>
        <div class="banner-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Ambiente de Desenvolvimento:</strong> Os emails estão sendo simulados. Confira as notificações abaixo para ver quais emails seriam enviados.
        </div>
        <?php endif; ?>
        
        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="stats-number text-success"><?php echo $total_enviados; ?></div>
                    <div>E-mails Enviados</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="stats-number text-danger"><?php echo $total_falhas; ?></div>
                    <div>Falhas de Envio</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="stats-number text-warning"><?php echo $total_simulados; ?></div>
                    <div>E-mails Simulados</div>
                </div>
            </div>
        </div>
        
        <!-- Ações -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-cog me-2"></i> Ações
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="teste_sendgrid.php" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-paper-plane me-2"></i> Testar Envio de E-mail
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="verificar_logs_email.php" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-search me-2"></i> Verificar Logs
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="diagnostico_email.php" class="btn btn-secondary w-100 mb-2">
                            <i class="fas fa-stethoscope me-2"></i> Diagnóstico de E-mail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Histórico de Notificações -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-history me-2"></i> Histórico de Notificações
            </div>
            <div class="card-body">
                <?php if (count($logs) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Destinatário</th>
                                    <th>Assunto</th>
                                    <th>Status</th>
                                    <th>Método</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($log['data_envio'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['destinatario']); ?></td>
                                    <td><?php echo htmlspecialchars($log['assunto']); ?></td>
                                    <td>
                                        <span class="badge status-badge status-<?php echo $log['status_envio']; ?>">
                                            <?php 
                                            switch($log['status_envio']) {
                                                case 'sucesso': echo 'Enviado'; break;
                                                case 'falha': echo 'Falha'; break;
                                                case 'simulado': echo 'Simulado'; break;
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['metodo_envio']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary toggle-details" data-id="<?php echo $log['id']; ?>">
                                            <i class="fas fa-eye"></i> Detalhes
                                        </button>
                                        
                                        <?php if ($log['status_envio'] != 'sucesso'): ?>
                                        <a href="reenviar_email.php?id=<?php echo $log['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-redo"></i> Reenviar
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr class="notification-detail" id="details-<?php echo $log['id']; ?>">
                                    <td colspan="6">
                                        <div class="mb-2">
                                            <strong>ID do Artigo:</strong> <?php echo $log['artigo_id'] ? $log['artigo_id'] : 'N/A'; ?>
                                        </div>
                                        
                                        <?php if (!empty($log['detalhes'])): ?>
                                        <div class="mb-2">
                                            <strong>Detalhes:</strong> <?php echo htmlspecialchars($log['detalhes']); ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($log['conteudo'])): ?>
                                        <div>
                                            <strong>Conteúdo:</strong>
                                            <div class="notification-content">
                                                <?php echo $log['conteudo']; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center py-3">Nenhum registro de notificação encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4 mb-4">
            <a href="PAGES/admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Voltar ao Painel de Administração
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar/ocultar detalhes da notificação
            const toggleButtons = document.querySelectorAll('.toggle-details');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const detailsRow = document.getElementById('details-' + id);
                    
                    if (detailsRow.style.display === 'table-row') {
                        detailsRow.style.display = 'none';
                        this.innerHTML = '<i class="fas fa-eye"></i> Detalhes';
                    } else {
                        detailsRow.style.display = 'table-row';
                        this.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar';
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
// Fechar conexão
$conn->close();
?>
