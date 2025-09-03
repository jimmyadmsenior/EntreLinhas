<?php
// Versão simplificada do index.php para implementar gradualmente
// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir apenas o mínimo necessário para a página funcionar
echo "<html>
<head>
    <title>EntreLinhas - Versão de Diagnóstico</title>
    <meta charset='utf-8'>
</head>
<body>
    <h1>EntreLinhas - Página de Diagnóstico</h1>
    <p>Este é um teste para verificar a funcionalidade básica da página.</p>
    
    <h2>Informações do ambiente:</h2>
    <ul>
        <li>Versão do PHP: " . phpversion() . "</li>
        <li>Servidor: " . $_SERVER['SERVER_NAME'] . "</li>
        <li>Caminho do script: " . $_SERVER['SCRIPT_NAME'] . "</li>
    </ul>
    
    <h2>Próximos passos:</h2>
    <p>Se esta página está sendo exibida corretamente, podemos começar a adicionar gradualmente as funcionalidades originais.</p>
</body>
</html>";
?>
