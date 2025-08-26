# Sistema de Notificação por E-mail - EntreLinhas

Este documento descreve o sistema de notificação por e-mail do projeto EntreLinhas, incluindo os scripts disponíveis e como utilizá-los.

## Visão Geral

O sistema utiliza a API do SendGrid para enviar e-mails e registra todos os envios em uma tabela de log no banco de dados. Os scripts foram projetados para serem executados via linha de comando (CLI), tornando-os independentes da disponibilidade do servidor web.

## Configuração

O sistema utiliza as seguintes configurações:

- **API Key do SendGrid**: Configurada no arquivo `backend/sendgrid_api_helper.php`
- **E-mail do Remetente**: `jimmycastilho555@gmail.com` (verificado na conta do SendGrid)
- **Nome do Remetente**: "EntreLinhas"

## Scripts Disponíveis

### 1. Notificação de Status de Artigo

**Arquivo**: `notificar_artigo.php`

**Descrição**: Notifica o autor sobre mudanças no status de seu artigo.

**Uso**:

```bash
php notificar_artigo.php <id_artigo> <status> <comentario>
```

**Exemplo**:

```bash
php notificar_artigo.php 3 aprovado "Seu artigo foi aprovado pela equipe editorial. Parabéns!"
```

**Status suportados**: aprovado, em_revisao, pendente, rejeitado

### 2. Notificação para Administradores

**Arquivo**: `notificar_admins_novo_artigo.php`

**Descrição**: Notifica todos os administradores quando um novo artigo é submetido.

**Uso**:

```bash
php notificar_admins_novo_artigo.php <id_artigo>
```

**Exemplo**:

```bash
php notificar_admins_novo_artigo.php 3
```

### 3. Notificação de Rejeição

**Arquivo**: `notificar_rejeicao.php`

**Descrição**: Notifica o autor que seu artigo foi rejeitado, com o motivo da rejeição.

**Uso**:

```bash
php notificar_rejeicao.php <id_artigo> <motivo>
```

**Exemplo**:

```bash
php notificar_rejeicao.php 3 "O artigo não atende aos padrões de qualidade da publicação."
```

## Registro de Logs

Todos os e-mails enviados são registrados na tabela `email_log` com as seguintes informações:

- **ID**: Identificador único do log
- **Artigo ID**: ID do artigo relacionado
- **Destinatário**: E-mail do destinatário
- **Assunto**: Assunto do e-mail
- **Status Envio**: enviado ou falha
- **Método Envio**: sendgrid (ou outros métodos que venham a ser implementados)
- **Data Envio**: Data e hora do envio

## Scripts Auxiliares

### Verificar Logs de E-mail

**Arquivo**: `verificar_logs_email.php`

**Descrição**: Exibe os últimos registros de envio de e-mail.

**Uso**:

```bash
php verificar_logs_email.php
```

### Criar Tabela de Log

**Arquivo**: `criar_tabela_email_log.php`

**Descrição**: Cria a tabela de log de e-mails no banco de dados.

**Uso**:

```bash
php criar_tabela_email_log.php
```

## Integração com o Sistema Web

Para integrar o sistema de notificação ao sistema web:

1. **Quando um artigo for aprovado**:

   ```php
   shell_exec("php notificar_artigo.php {$id_artigo} aprovado '{$comentario}'");
   ```

2. **Quando um novo artigo for submetido**:

   ```php
   shell_exec("php notificar_admins_novo_artigo.php {$id_artigo}");
   ```

3. **Quando um artigo for rejeitado**:

   ```php
   shell_exec("php notificar_rejeicao.php {$id_artigo} '{$motivo}'");
   ```

## Solução de Problemas

Se houver falhas no envio de e-mails:

1. **Verifique a conexão com a internet**
2. **Verifique o status da API do SendGrid**: [https://status.sendgrid.com/](https://status.sendgrid.com/)
3. **Verifique os logs de erro do PHP**: `error_log`
4. **Verifique a tabela `email_log`** para confirmar se os registros estão sendo criados

## Manutenção

Para adicionar novos tipos de notificação:

1. Crie um novo script PHP baseado nos existentes
2. Certifique-se de registrar os envios na tabela `email_log`
3. Atualize esta documentação com as informações do novo script
