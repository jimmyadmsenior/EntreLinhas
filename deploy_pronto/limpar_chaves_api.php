<?php
/**
 * Script para substituir chaves de API expostas por referências seguras
 * 
 * Este script substitui todas as ocorrências de uma chave de API exposta
 * por uma referência segura às variáveis de ambiente.
 */

// Arquivos a serem verificados
$arquivos = [
    'backend/sendgrid_email.php',
    'teste_sendgrid_direto.php',
    'diagnostico_sendgrid.php',
    'verificar_sendgrid.php',
    'email_universal.php',
    'teste_sendgrid_simples.php',
    'teste_sendgrid_cli.php',
    'sendgrid_api_helper.php'
];

// Chave a ser substituída
$chave_api = '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';

// Texto para substituir a chave
$texto_padrao = 'CHAVE_SENDGRID_REMOVIDA';

// Função para verificar se o arquivo existe
function verificar_arquivo($arquivo) {
    if (!file_exists($arquivo)) {
        echo "AVISO: Arquivo não encontrado: $arquivo\n";
        return false;
    }
    return true;
}

// Função para fazer backup do arquivo
function fazer_backup($arquivo) {
    $backup = $arquivo . '.bak';
    if (!copy($arquivo, $backup)) {
        echo "ERRO: Não foi possível criar backup de $arquivo\n";
        return false;
    }
    echo "Backup criado: $backup\n";
    return true;
}

// Função para substituir a chave no arquivo
function substituir_chave($arquivo, $chave_api, $texto_padrao) {
    $conteudo = file_get_contents($arquivo);
    
    if (strpos($conteudo, $chave_api) === false) {
        echo "INFO: Chave não encontrada em $arquivo\n";
        return false;
    }
    
    // Substituir a chave
    $novo_conteudo = str_replace($chave_api, $texto_padrao, $conteudo);
    
    // Salvar o novo conteúdo
    if (file_put_contents($arquivo, $novo_conteudo) === false) {
        echo "ERRO: Não foi possível salvar alterações em $arquivo\n";
        return false;
    }
    
    echo "✅ Chave substituída em $arquivo\n";
    return true;
}

// Processar cada arquivo
echo "=== INICIANDO SUBSTITUIÇÃO DE CHAVES ===\n";
$total_arquivos = count($arquivos);
$arquivos_processados = 0;
$substituicoes_realizadas = 0;

foreach ($arquivos as $arquivo) {
    echo "\nProcessando: $arquivo\n";
    
    if (!verificar_arquivo($arquivo)) {
        continue;
    }
    
    if (!fazer_backup($arquivo)) {
        continue;
    }
    
    if (substituir_chave($arquivo, $chave_api, $texto_padrao)) {
        $substituicoes_realizadas++;
    }
    
    $arquivos_processados++;
}

echo "\n=== RESUMO DA OPERAÇÃO ===\n";
echo "Arquivos processados: $arquivos_processados/$total_arquivos\n";
echo "Substituições realizadas: $substituicoes_realizadas\n";
echo "\nOPERAÇÃO CONCLUÍDA!\n";

echo "\nINSTRUÇÕES:\n";
echo "1. Revise os arquivos alterados para garantir que funcionam corretamente\n";
echo "2. Modifique os arquivos para usar as variáveis de ambiente\n";
echo "3. Execute 'git add' e 'git commit' para salvar as alterações\n";
?>
