# EntreLinhas

## Sistema de blog/revista para publicação de artigos

### Configuração de Ambiente

1. Clone o repositório
2. Configure seu banco de dados MySQL
3. Renomeie o arquivo `.env.example` para `.env` e preencha com suas configurações
4. Inicie o servidor PHP: `php -S localhost:8000`

### Sistema de E-mail

O sistema utiliza a API do SendGrid para envio de e-mails. Para configurar:

1. Crie uma conta no [SendGrid](https://sendgrid.com/)
2. Gere uma chave de API com permissões para envio de e-mails
3. Adicione a chave ao arquivo `.env`:
   ```
   SENDGRID_API_KEY="SUA_CHAVE_API_AQUI"
   EMAIL_REMETENTE="seu_email_verificado@exemplo.com"
   EMAIL_NOME="Nome do Remetente"
   ```
4. Verifique seu e-mail de remetente no painel do SendGrid

### Scripts de Notificação

O sistema inclui scripts para notificação por e-mail via linha de comando:

- `notificar_artigo.php`: Notificar autor sobre status do artigo
- `notificar_admins_novo_artigo.php`: Notificar administradores sobre novo artigo
- `notificar_rejeicao.php`: Notificar autor sobre rejeição de artigo

Para mais informações, consulte o arquivo `DOCS_SISTEMA_EMAIL.md`.

### Segurança

Nunca compartilhe suas chaves de API ou dados sensíveis. O arquivo `.env` está incluído no `.gitignore` para evitar exposição acidental de credenciais.