<?php
/**
 * Carrega variáveis de ambiente do arquivo .env
 */
function carregarVariaveisAmbiente() {
    $envFile = dirname(__DIR__) . '/.env';
    
    if (file_exists($envFile)) {
        $linhas = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($linhas as $linha) {
            // Ignorar comentários
            if (strpos(trim($linha), '#') === 0) {
                continue;
            }
            
            // Processar variáveis de ambiente
            if (strpos($linha, '=') !== false) {
                list($nome, $valor) = explode('=', $linha, 2);
                $nome = trim($nome);
                $valor = trim($valor);
                
                // Remover aspas se existirem
                if (strpos($valor, '"') === 0 && strrpos($valor, '"') === strlen($valor) - 1) {
                    $valor = substr($valor, 1, -1);
                }
                
                // Definir a variável de ambiente
                putenv("$nome=$valor");
            }
        }
        
        return true;
    }
    
    return false;
}
?>
