# Configuração de E-mail para o Sistema EntreLinhas

## Problema Atual

O sistema EntreLinhas está enfrentando problemas para enviar e-mails utilizando a função `mail()` nativa do PHP. Isso acontece porque esta função depende de um servidor SMTP configurado no servidor onde o PHP está sendo executado.

No ambiente de desenvolvimento local (localhost), o PHP geralmente não tem um servidor SMTP configurado por padrão, o que resulta no não envio dos e-mails.

## Soluções Possíveis

### Opção 1: Configurar SMTP Local para Teste

Para desenvolvimento local, você pode instalar um servidor SMTP de teste:

1. **Instale o smtp4dev**:
   - Baixe em: https://github.com/rnwood/smtp4dev/releases
   - Este é um servidor SMTP falso que captura e-mails sem realmente enviá-los
   
2. **Configure o php.ini**:
   ```
   [mail function]
   SMTP = localhost
   smtp_port = 25
   sendmail_from = noreply@entrelinhas.com
   ```

3. **Reinicie o servidor PHP**

### Opção 2: Usar Serviço de API de E-mail (Recomendado para Produção)

1. **Mailgun** (https://www.mailgun.com):
   - Oferece 10.000 e-mails grátis por mês
   - Fácil integração via API REST
   - Boa documentação e suporte

2. **SendGrid** (https://sendgrid.com):
   - Oferece 100 e-mails grátis por dia
   - API simples e biblioteca PHP oficial
   
3. **Amazon SES** (https://aws.amazon.com/ses):
   - Preço extremamente baixo por e-mail
   - Ótima escalabilidade
   - Requer conta AWS

### Opção 3: Usar PHPMailer com SMTP Externo

PHPMailer é uma biblioteca popular para envio de e-mails que permite usar servidores SMTP externos:

1. **Instale PHPMailer**:
   ```
   composer require phpmailer/phpmailer
   ```

2. **Configure com Gmail ou outro provedor**:
   ```php
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\SMTP;
   use PHPMailer\PHPMailer\Exception;

   $mail = new PHPMailer(true);
   $mail->isSMTP();
   $mail->Host = 'smtp.gmail.com';
   $mail->SMTPAuth = true;
   $mail->Username = 'seu_email@gmail.com';
   $mail->Password = 'sua_senha_de_app'; // Senha de aplicativo
   $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
   $mail->Port = 587;
   ```

3. **Utilize no projeto**

## Implementação Recomendada para EntreLinhas

Criamos duas opções de implementação prontas para uso:

1. `backend/email_service.php`: Implementação simples com função mail() nativa
2. `backend/email_api.php`: Implementação avançada usando serviços de API web

### Para ambiente de desenvolvimento:

Recomendamos usar a opção 1 (smtp4dev) ou implementar logs detalhados para simular o envio de e-mails.

### Para ambiente de produção:

Recomendamos usar a opção 2 (API de E-mail) ou opção 3 (PHPMailer com SMTP).

## Próximos Passos

1. Para testes durante o desenvolvimento:
   - Execute `email_debug.php` para verificar a configuração atual
   - Use o script `teste_email.php` para testar o envio direto

2. Para implementar uma solução definitiva:
   - Escolha uma das opções acima
   - Atualize o arquivo `email_notification.php` para usar a solução escolhida
   - Faça testes para verificar se os e-mails estão sendo enviados corretamente

## Observações Importantes

- No Windows, a função `mail()` pode ser particularmente problemática
- Verifique a pasta de spam ao testar o recebimento de e-mails
- Alguns provedores (como Gmail) podem bloquear e-mails de fontes não confiáveis
- Para uma solução definitiva, considere usar um domínio dedicado para envio de e-mails
