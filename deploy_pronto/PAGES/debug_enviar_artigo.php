<?php
// Arquivo para depurar erros PHP

// Definir configurações para exibir todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir o arquivo que queremos depurar
include 'enviar-artigo.php';
?>
