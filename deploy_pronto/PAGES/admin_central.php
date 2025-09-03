<?php
// Este arquivo define a sessão como administrador e redireciona para o painel
session_start();

// Definir como administrador
$_SESSION["loggedin"] = true;
$_SESSION["tipo"] = "admin";
$_SESSION["id"] = 1;
$_SESSION["nome"] = "Administrador";
$_SESSION["email"] = "admin@example.com";

// Opções de painel
$paineis = [
    'admin_dashboard.php' => 'Painel Original (pode ter problemas)',
    'admin_dashboard_novo.php' => 'Painel Novo (recomendado)',
    'admin_simples.php' => 'Painel Simplificado',
    'admin_basico.php' => 'Painel Básico'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Administrativo - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            background: #f9f9f9;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            margin: 5px;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Acesso Administrativo - EntreLinhas</h1>
        
        <div class="card">
            <h2>Status da Sessão</h2>
            <?php if ($_SESSION["loggedin"] === true && $_SESSION["tipo"] === "admin"): ?>
                <p class="success">✓ Sessão configurada como administrador!</p>
                <p>Nome: <?php echo htmlspecialchars($_SESSION["nome"]); ?></p>
                <p>Email: <?php echo htmlspecialchars($_SESSION["email"]); ?></p>
                <p>Tipo: <?php echo htmlspecialchars($_SESSION["tipo"]); ?></p>
            <?php else: ?>
                <p class="error">✗ Erro ao configurar a sessão!</p>
                <pre><?php print_r($_SESSION); ?></pre>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Escolha uma versão do Painel</h2>
            <?php foreach ($paineis as $arquivo => $descricao): ?>
                <?php if (file_exists($arquivo)): ?>
                    <p>
                        <a href="<?php echo $arquivo; ?>" class="btn"><?php echo htmlspecialchars($descricao); ?></a>
                    </p>
                <?php else: ?>
                    <p class="error">
                        ✗ <?php echo htmlspecialchars($descricao); ?> (não encontrado)
                    </p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="card">
            <h2>Ferramentas de Diagnóstico</h2>
            <p>
                <a href="corrigir_admin_dashboard.php" class="btn">Corrigir Painel Administrativo</a>
            </p>
            <p>
                <a href="admin_dashboard.php?debug=1&force=1" class="btn">Forçar Acesso ao Painel Original</a>
            </p>
            <p>
                <a href="../backend/logout.php" class="btn">Sair</a>
            </p>
        </div>
    </div>
</body>
</html>
