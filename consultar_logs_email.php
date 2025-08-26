<?php
/**
 * consultar_logs_email.php
 * 
 * Script para consultar os logs de envio de e-mail
 */

// Incluir as configurações do banco de dados
require_once __DIR__ . '/backend/config.php';

// Verificar conexão
if (!isset($conn) || $conn->connect_error) {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
}

if ($conn->connect_error) {
    echo "ERRO: Falha na conexão com o banco de dados: {$conn->connect_error}\n";
    exit(1);
}

// Verificar se a tabela existe
$result = $conn->query("SHOW TABLES LIKE 'email_log'");
if ($result->num_rows == 0) {
    echo "A tabela email_log não existe. Execute o script criar_tabela_email_log.php primeiro.\n";
    exit(1);
}

// Parâmetros de linha de comando
$filtro_artigo = isset($argv[1]) ? intval($argv[1]) : 0;
$filtro_status = isset($argv[2]) ? $argv[2] : '';
$limite = isset($argv[3]) ? intval($argv[3]) : 20;

// Construir a consulta SQL
$sql = "SELECT * FROM email_log";
$where = [];
$params = [];
$types = "";

if ($filtro_artigo > 0) {
    $where[] = "artigo_id = ?";
    $params[] = $filtro_artigo;
    $types .= "i";
}

if (!empty($filtro_status)) {
    $where[] = "status_envio = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY data_envio DESC LIMIT ?";
$params[] = $limite;
$types .= "i";

// Executar a consulta
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Erro na preparação da consulta: {$conn->error}\n";
    exit(1);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Exibir os resultados
if ($result->num_rows == 0) {
    echo "Nenhum registro encontrado com os filtros especificados.\n";
} else {
    // Cabeçalho da tabela
    printf("%-5s | %-8s | %-30s | %-40s | %-10s | %-15s | %-20s\n", "ID", "ART_ID", "DESTINATÁRIO", "ASSUNTO", "STATUS", "MÉTODO", "DATA");
    echo str_repeat("-", 140) . "\n";
    
    // Dados
    while ($row = $result->fetch_assoc()) {
        $assunto = substr($row['assunto'], 0, 40);
        $destinatario = substr($row['destinatario'], 0, 30);
        
        printf("%-5s | %-8s | %-30s | %-40s | %-10s | %-15s | %-20s\n", 
            $row['id'],
            $row['artigo_id'],
            $destinatario,
            $assunto,
            $row['status_envio'],
            $row['metodo_envio'],
            $row['data_envio']
        );
    }
    
    echo "\nTotal de registros: {$result->num_rows}\n";
}

$stmt->close();
$conn->close();
?>
