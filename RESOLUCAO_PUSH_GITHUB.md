# RESOLUÇÃO DO PROBLEMA DE PUSH

O GitHub está bloqueando o push devido a chaves de API que foram detectadas em commits anteriores.

## Opções para resolver o problema:

### Opção 1: Autorizar a chave (mais simples)

Se a chave já foi revogada ou regenerada, você pode autorizar o push acessando o link:
https://github.com/jimmyadmsenior/EntreLinhas/security/secret-scanning/unblock-secret/31nxFFUU5qgeIacxOp65eJ2BN9w

### Opção 2: Reescrever o histórico do Git (mais complexa)

Essa opção é mais avançada e requer cautela, pois altera o histórico do repositório:

1. Faça backup do repositório
2. Use o comando `git filter-repo` para remover a chave de todos os commits:
   ```bash
   pip install git-filter-repo
   git filter-repo --replace-text patterns.txt
   ```

   Onde `patterns.txt` contém:
   ```
   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==>CHAVE_SENDGRID_REMOVIDA
   regex:SG\.U-8z00lQQLOGgS2jBYZvOA\.UzuCd163lX5DSDfuPszu59v2nFYVpypr3ycqhZ5Ed5o==>CHAVE_SENDGRID_REMOVIDA
   ```

3. Force o push para o repositório remoto:
   ```bash
   git push origin Jimmy --force
   ```

## Prevenção para o futuro:

1. Nunca adicione chaves de API diretamente no código
2. Use o arquivo `.env` para todas as configurações sensíveis 
3. Configure o `.gitignore` para evitar que arquivos sensíveis sejam adicionados ao repositório
4. Use hooks de pre-commit para verificar se há chaves de API no código antes do commit
