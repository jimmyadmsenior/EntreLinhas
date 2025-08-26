# Sistema de Notificação por E-mail - EntreLinhas

Este sistema permite enviar notificações por e-mail para os autores dos artigos quando houver mudanças de status ou outras informações importantes.

## Arquivos Principais

- **notificar_status.php** - Script simplificado para enviar notificações com modelos predefinidos
- **notificar_artigo.php** - Script base para envio de notificações sobre artigos
- **sendgrid_api_helper.php** - Helper para envio via API do SendGrid
- **enviar_email_nativo.php** - Alternativa usando função mail() nativa do PHP
- **enviar_email_arquivo.php** - Alternativa salvando e-mails em arquivos locais
- **consultar_logs_email.php** - Visualizar logs de envios de e-mails
- **criar_tabela_email_log.php** - Script para criar a tabela de logs

## Como Usar

### Notificação Simples (Recomendado)

```bash
php notificar_status.php [id_artigo] [status] [comentario_opcional]
```

Exemplos:

```bash
php notificar_status.php 3 aprovado
php notificar_status.php 5 correcoes "Por favor, revise os parágrafos 3 e 5."
```

Status disponíveis:

- **aprovado** - Notifica que o artigo foi aprovado
- **recusado** - Notifica que o artigo foi recusado
- **revisao** - Notifica que o artigo está em revisão
- **correcoes** - Notifica que o artigo precisa de correções
- **publicado** - Notifica que o artigo foi publicado

### Notificação Avançada

```bash
php notificar_artigo.php [id_artigo] [status] [comentario]
```

Exemplos:

```bash
php notificar_artigo.php 3 aprovado "Seu artigo foi aprovado!"
php notificar_artigo.php 5 recusado "O artigo não atende aos critérios."
```

Status válidos: pendente, revisao, aprovado, publicado, recusado, correcoes

### Consultar Logs de E-mails

```bash
php consultar_logs_email.php [id_artigo] [status] [limite]
```

Exemplos:

```bash
php consultar_logs_email.php
php consultar_logs_email.php 3
php consultar_logs_email.php 3 enviado 10
```

## Sistema de Fallback

O sistema tenta enviar e-mails na seguinte ordem:

1. **SendGrid API** - Método preferencial usando API do SendGrid
2. **PHP mail()** - Função nativa do PHP como alternativa
3. **Arquivo Local** - Salva o e-mail como arquivo HTML quando os outros métodos falham

Todos os envios e tentativas são registrados na tabela `email_log` para referência futura.

## Configuração

Para usar o SendGrid, é necessário atualizar a chave de API no arquivo `sendgrid_api_helper.php`.

Para configurar o servidor SMTP, edite o arquivo `enviar_email_smtp.php`.

## Emails Salvos Localmente

Quando o e-mail é salvo como arquivo (método de último recurso), ele fica na pasta `/emails` com nome contendo data, hora e destinatário.
