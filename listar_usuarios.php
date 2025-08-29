<?php
// Arquivo para listar os usuários cadastrados no sistema
// Apenas administradores têm acesso a esta página

// Iniciar sessão
session_start();

// Incluir arquivo de configuração do banco de dados
require_once 'backend/config.php';

// Verificar se o usuário está logado e é um administrador
$is_admin = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Verificar se o usuário é um administrador
    $user_id = $_SESSION['id'];
    $query = "SELECT is_admin FROM usuarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $admin_status);
        
        if (mysqli_stmt_fetch($stmt)) {
            $is_admin = (bool)$admin_status;
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Se não for administrador, redirecionar para a página inicial
if (!$is_admin) {
    header("Location: index.php");
    exit;
}

// Função para limpar os dados de entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Inicializar variáveis para filtros
$filtro_nome = $filtro_email = $filtro_status = '';
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Processar filtros se enviados
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['filtrar'])) {
    $filtro_nome = isset($_GET['nome']) ? clean_input($_GET['nome']) : '';
    $filtro_email = isset($_GET['email']) ? clean_input($_GET['email']) : '';
    $filtro_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
}

// Construir a consulta SQL com os filtros
$sql = "SELECT id, nome, email, data_cadastro, ativo FROM usuarios WHERE 1=1";
$params = [];
$types = "";

if (!empty($filtro_nome)) {
    $sql .= " AND nome LIKE ?";
    $filtro_nome = "%$filtro_nome%";
    $params[] = $filtro_nome;
    $types .= "s";
}

if (!empty($filtro_email)) {
    $sql .= " AND email LIKE ?";
    $filtro_email = "%$filtro_email%";
    $params[] = $filtro_email;
    $types .= "s";
}

if ($filtro_status !== '') {
    $sql .= " AND ativo = ?";
    $params[] = $filtro_status;
    $types .= "i";
}

// Contar o total de registros para a paginação
$sql_count = str_replace("SELECT id, nome, email, data_cadastro, ativo", "SELECT COUNT(*)", $sql);
$total_registros = 0;

if ($stmt_count = mysqli_prepare($conn, $sql_count)) {
    if (!empty($types)) {
        mysqli_stmt_bind_param($stmt_count, $types, ...$params);
    }
    mysqli_stmt_execute($stmt_count);
    mysqli_stmt_bind_result($stmt_count, $total_registros);
    mysqli_stmt_fetch($stmt_count);
    mysqli_stmt_close($stmt_count);
}

$total_paginas = ceil($total_registros / $registros_por_pagina);

// Adicionar ordenação e paginação
$sql .= " ORDER BY nome ASC LIMIT ? OFFSET ?";
$params[] = $registros_por_pagina;
$types .= "i";
$params[] = $offset;
$types .= "i";

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Usuários - EntreLinhas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: red;
            font-weight: bold;
        }
        .actions {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Listagem de Usuários</h1>
        
        <div class="filters">
            <h5>Filtros</h5>
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                <div class="col-md-4">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $filtro_nome; ?>">
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" value="<?php echo $filtro_email; ?>">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="" <?php echo $filtro_status === '' ? 'selected' : ''; ?>>Todos</option>
                        <option value="1" <?php echo $filtro_status === '1' ? 'selected' : ''; ?>>Ativos</option>
                        <option value="0" <?php echo $filtro_status === '0' ? 'selected' : ''; ?>>Inativos</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="filtrar" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Data de Cadastro</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Executar a consulta SQL para buscar os usuários
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        if (!empty($types)) {
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                        }
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        
                        // Verificar se existem registros
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . date('d/m/Y H:i', strtotime($row['data_cadastro'])) . "</td>";
                                echo "<td class='" . ($row['ativo'] ? 'status-active' : 'status-inactive') . "'>" 
                                    . ($row['ativo'] ? 'Ativo' : 'Inativo') . "</td>";
                                echo "<td class='actions'>";
                                echo "<a href='editar_usuario.php?id=" . $row['id'] . "' class='btn btn-sm btn-warning me-1'>Editar</a>";
                                
                                // Botão para alterar status (ativar/inativar)
                                echo "<a href='alterar_status_usuario.php?id=" . $row['id'] 
                                    . "&status=" . ($row['ativo'] ? '0' : '1') 
                                    . "' class='btn btn-sm " . ($row['ativo'] ? 'btn-danger' : 'btn-success') . " me-1' 
                                    onclick='return confirm(\"Tem certeza que deseja " . ($row['ativo'] ? 'inativar' : 'ativar') . " este usuário?\")'>"
                                    . ($row['ativo'] ? 'Inativar' : 'Ativar') . "</a>";
                                
                                // Não permitir excluir o próprio usuário administrador
                                if ($row['id'] != $_SESSION['id']) {
                                    echo "<a href='excluir_usuario.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' 
                                        onclick='return confirm(\"Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.\")'>Excluir</a>";
                                }
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Nenhum usuário encontrado</td></tr>";
                        }
                        
                        mysqli_stmt_close($stmt);
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-danger'>Erro na consulta: " . mysqli_error($conn) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <?php if ($total_paginas > 1): ?>
        <nav aria-label="Navegação de página">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($pagina_atual <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?>&nome=<?php echo urlencode($filtro_nome); ?>&email=<?php echo urlencode($filtro_email); ?>&status=<?php echo $filtro_status; ?>&filtrar=1" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo ($pagina_atual == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $i; ?>&nome=<?php echo urlencode($filtro_nome); ?>&email=<?php echo urlencode($filtro_email); ?>&status=<?php echo $filtro_status; ?>&filtrar=1">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo ($pagina_atual >= $total_paginas) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?>&nome=<?php echo urlencode($filtro_nome); ?>&email=<?php echo urlencode($filtro_email); ?>&status=<?php echo $filtro_status; ?>&filtrar=1" aria-label="Próximo">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        
        <!-- Botões de ação -->
        <div class="mt-4 text-center">
            <a href="adicionar_usuario.php" class="btn btn-success">Adicionar Novo Usuário</a>
            <a href="index.php" class="btn btn-secondary ms-2">Voltar para o Início</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
