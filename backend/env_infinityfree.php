<?php
/**
 * Carregador de variáveis de ambiente para o InfinityFree
 * 
 * Como o InfinityFree não suporta arquivos .env, este arquivo
 * configura manualmente as variáveis de ambiente necessárias.
 */

function carregarVariaveisAmbiente() {
    // Defina suas variáveis de ambiente aqui
    putenv("SENDGRID_API_KEY=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");
    putenv("EMAIL_REMETENTE=noreply@entrelinhas.com");
    putenv("EMAIL_NOME=EntreLinhas");
    
    // Adicione outras variáveis conforme necessário
}

// Executa a função automaticamente
carregarVariaveisAmbiente();
?>
