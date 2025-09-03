# Script simplificado de preparação para deploy no InfinityFree

# Limpar a tela
Clear-Host

Write-Host "=== Preparando arquivos para deploy no InfinityFree ===" -ForegroundColor Cyan
Write-Host

# Definição das pastas
$sourceFolder = "."
$deployFolder = ".\deploy_pronto"

# Criar pasta de deploy
Write-Host "Criando pasta de deploy..." -ForegroundColor Yellow
if (Test-Path -Path $deployFolder) {
    Remove-Item -Path "$deployFolder\*" -Recurse -Force
} else {
    New-Item -ItemType Directory -Path $deployFolder | Out-Null
}

# Criar estrutura de pastas
Write-Host "Criando estrutura de pastas..." -ForegroundColor Yellow
$folders = @("assets", "backend", "PAGES", "uploads")
foreach ($folder in $folders) {
    New-Item -ItemType Directory -Path "$deployFolder\$folder" -Force | Out-Null
}

# Criar subpastas necessárias
New-Item -ItemType Directory -Path "$deployFolder\assets\css" -Force | Out-Null
New-Item -ItemType Directory -Path "$deployFolder\assets\js" -Force | Out-Null
New-Item -ItemType Directory -Path "$deployFolder\assets\images" -Force | Out-Null
New-Item -ItemType Directory -Path "$deployFolder\uploads\perfil" -Force | Out-Null
New-Item -ItemType Directory -Path "$deployFolder\PAGES\includes" -Force | Out-Null

# Copiar arquivos
Write-Host "Copiando arquivos..." -ForegroundColor Yellow

# Copiar arquivos PHP na raiz (exceto arquivos de teste)
Get-ChildItem -Path $sourceFolder -File -Filter "*.php" | 
    Where-Object { $_.Name -notlike "teste_*" -and $_.Name -notlike "diagnostico_*" } |
    ForEach-Object { Copy-Item $_.FullName -Destination $deployFolder }

# Copiar .htaccess se existir
if (Test-Path -Path ".\.htaccess") {
    Copy-Item -Path ".\.htaccess" -Destination $deployFolder
}

# Copiar assets
Copy-Item -Path ".\assets\css\*.css" -Destination "$deployFolder\assets\css\" -Force
Copy-Item -Path ".\assets\js\*.js" -Destination "$deployFolder\assets\js\" -Force
Copy-Item -Path ".\assets\images\*" -Destination "$deployFolder\assets\images\" -Force -Recurse

# Copiar PAGES
Copy-Item -Path ".\PAGES\*.php" -Destination "$deployFolder\PAGES\" -Force
Copy-Item -Path ".\PAGES\includes\*.php" -Destination "$deployFolder\PAGES\includes\" -Force

# Copiar backend
Copy-Item -Path ".\backend\*.php" -Destination "$deployFolder\backend\" -Force

# Usar a configuração do InfinityFree
if (Test-Path -Path ".\config_infinityfree.php") {
    Copy-Item -Path ".\config_infinityfree.php" -Destination "$deployFolder\backend\config.php" -Force
    Write-Host "Arquivo de configuração do InfinityFree copiado!" -ForegroundColor Green
}

# Informações de upload
Write-Host
Write-Host "=== PREPARAÇÃO CONCLUÍDA ===" -ForegroundColor Green
Write-Host "Os arquivos estão prontos na pasta: $deployFolder" -ForegroundColor Cyan
Write-Host
Write-Host "Para fazer o upload via FileZilla:" -ForegroundColor Yellow
Write-Host "1. Host: ftpupload.net" -ForegroundColor White
Write-Host "2. Usuário: if0_39798697" -ForegroundColor White
Write-Host "3. Senha: jimmysena123" -ForegroundColor White
Write-Host "4. Porta: 21" -ForegroundColor White
Write-Host
Write-Host "Faça upload do conteúdo da pasta $deployFolder para a pasta 'htdocs' no servidor" -ForegroundColor Yellow
