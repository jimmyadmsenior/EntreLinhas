# INSTRUÇÕES PARA CONFIGURAÇÃO DO AMBIENTE

## ATENÇÃO: CHAVES DE API REMOVIDAS

As chaves de API e outras informações sensíveis foram removidas do código-fonte e substituídas por variáveis de ambiente.

## Como configurar seu ambiente local:

1. Copie o arquivo `.env.example` para `.env`
   ```
   cp .env.example .env
   ```

2. Edite o arquivo `.env` e adicione suas chaves:
   ```
   SENDGRID_API_KEY="SUA_CHAVE_API_AQUI"
   EMAIL_REMETENTE="seu_email@exemplo.com"
   EMAIL_NOME="Nome do Remetente"
   ```

3. Todos os scripts que usavam chaves de API diretamente foram atualizados para usar o sistema de variáveis de ambiente.

4. Se precisar criar novos scripts, use o sistema de carregamento de variáveis de ambiente:
   ```php
   require_once 'backend/env_loader.php';
   carregarVariaveisAmbiente();
   
   $sendgrid_key = getenv('SENDGRID_API_KEY');
   ```

## NUNCA cometa o erro de:

1. Adicionar o arquivo `.env` ao Git
2. Adicionar chaves de API diretamente no código
3. Compartilhar chaves de API em comentários ou documentação

## Em caso de chave vazada:

Se uma chave de API for comprometida, regenere-a imediatamente no painel do serviço correspondente.
