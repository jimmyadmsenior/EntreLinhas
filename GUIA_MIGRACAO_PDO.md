# Guia de Migração para PDO

Este documento contém instruções para completar a migração do projeto EntreLinhas de mysqli para PDO.

## Arquivos já convertidos

1. `config_infinityfree.php` - Arquivo principal de configuração
2. `remover_artigos.php` - Script para remover artigos
3. `rebuild_comentarios_table.php` - Script para recriar tabela de comentários
4. `correcao_direta.php` - Script para corrigir erros

## Arquivos auxiliares criados

1. `config_pdo.php` - Nova versão do arquivo de configuração usando PDO
2. `pdo_helper.php` - Funções auxiliares para facilitar a migração
3. `verificar_migracao_pdo.php` - Script para verificar o progresso da migração
4. `exemplo_migracao_pdo.php` - Exemplos de conversão de mysqli para PDO

## Passos para completar a migração

1. Execute o script `verificar_migracao_pdo.php` para identificar arquivos que ainda usam mysqli
2. Para cada arquivo que precisa ser convertido:
   - Substitua a inclusão de `config.php` por `config_pdo.php`
   - Utilize as funções de `pdo_helper.php` para simplificar a conversão
   - Substitua consultas mysqli por prepared statements PDO
   - Teste o arquivo após as alterações

3. Priorize os arquivos principais:
   - Arquivos de autenticação e login
   - Arquivos de processamento de formulários
   - Arquivos da interface principal

4. Execute regularmente `verificar_migracao_pdo.php` para acompanhar o progresso

## Padrões de conversão principais

1. **Conexão**: 
```php
// Antes
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Depois
$pdo = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME.";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

2. **Consultas simples**:
```php
// Antes
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Depois
$stmt = $pdo->query($sql);
$row = $stmt->fetch();

// Ou usando helper
$row = pdo_query_first($pdo, $sql);
```

3. **Prepared Statements**:
```php
// Antes
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $nome, $id);
mysqli_stmt_execute($stmt);

// Depois
$stmt = $pdo->prepare($sql);
$stmt->execute([$nome, $id]);

// Ou com parâmetros nomeados
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
```

4. **Obtendo resultados**:
```php
// Antes
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    // ...
}

// Depois
$stmt = $pdo->query($sql);
while ($row = $stmt->fetch()) {
    // ...
}

// Ou obter todos de uma vez
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    // ...
}
```

5. **Valores de retorno**:
```php
// Antes
$id = mysqli_insert_id($conn);
$affected = mysqli_affected_rows($conn);

// Depois
$id = $pdo->lastInsertId();
$affected = $stmt->rowCount();
```

## Teste e verificação

Após converter cada arquivo:

1. Teste a funcionalidade para garantir que continua funcionando
2. Verifique se não há mais referências a funções mysqli
3. Verifique se os erros são tratados corretamente (usando try/catch)

Ao concluir a migração, execute `verificar_migracao_pdo.php` para confirmar que não há mais arquivos usando mysqli.
