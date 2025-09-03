<?php
// Arquivo de configuração do banco de dados para produção
// IMPORTANTE: Atualize esses valores com as credenciais fornecidas pela sua hospedagem
define('DB_SERVER', 'localhost'); // Normalmente localhost ou um endereço fornecido pela hospedagem
define('DB_USERNAME', 'usuario_da_hospedagem');  // Usuário do banco de dados na hospedagem
define('DB_PASSWORD', 'senha_na_hospedagem');    // Senha do banco de dados na hospedagem
define('DB_NAME', 'nome_do_banco_na_hospedagem');

// Email do administrador que receberá as notificações
define('ADMIN_EMAIL', 'jimmycastilho555@gmail.com');

// Tentativa de conexão com o banco de dados MySQL
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if (!$conn) {
    die("ERRO: Não foi possível conectar ao MySQL. " . mysqli_connect_error());
}

// Configurar charset para UTF-8
mysqli_set_charset($conn, "utf8mb4");
