# Script completo de preparação para deploy no InfinityFree
# Específico para o projeto EntreLinhas

# Definição das pastas
$sourceFolder = "."  # Pasta atual do projeto (EntreLinhas)
$deployFolder = ".\deploy_pronto"  # Pasta onde será criada a versão pronta para upload

# Criar pasta de deploy se não existir
if (-not (Test-Path -Path $deployFolder)) {
    New-Item -ItemType Directory -Path $deployFolder | Out-Null
    Write-Host "Pasta de deploy criada: $deployFolder" -ForegroundColor Green
}
else {
    Write-Host "A pasta $deployFolder ja existe. O conteudo sera substituido." -ForegroundColor Yellow
    Remove-Item -Path "$deployFolder\*" -Recurse -Force
}

# Criar estrutura de pastas
$foldersToCopy = @(
    "assets",
    "backend",
    "PAGES",
    "uploads"
)

foreach ($folder in $foldersToCopy) {
    if (-not (Test-Path -Path "$deployFolder\$folder")) {
        New-Item -ItemType Directory -Path "$deployFolder\$folder" | Out-Null
        Write-Host "Pasta criada: $folder" -ForegroundColor Gray
    }
}

# Copiar arquivos PHP para a pasta de deploy
Write-Host "Copiando arquivos para a pasta de deploy..." -ForegroundColor Cyan
$filesToCopy = Get-ChildItem -Path $sourceFolder -File -Filter "*.php" | Where-Object { $_.Name -notlike "teste_*" -and $_.Name -notlike "diagnostico_*" }
foreach ($file in $filesToCopy) {
    Copy-Item -Path $file.FullName -Destination $deployFolder -Force
    Write-Host "Copiado: $($file.Name)" -ForegroundColor Gray
}

# Copiar .htaccess se existir
if (Test-Path -Path ".\.htaccess") {
    Copy-Item -Path ".\.htaccess" -Destination "$deployFolder\" -Force
    Write-Host "Copiado: .htaccess" -ForegroundColor Green
}

# Copiar conteúdo das pastas assets, backend e PAGES
Write-Host "Copiando conteúdo das subpastas..." -ForegroundColor Cyan

# Copiar conteúdo da pasta assets
if (Test-Path -Path ".\assets") {
    Write-Host "Copiando assets..." -ForegroundColor Gray
    # Primeiro copiar os arquivos da raiz da pasta assets
    Get-ChildItem -Path ".\assets" -File | ForEach-Object {
        Copy-Item -Path $_.FullName -Destination "$deployFolder\assets\" -Force
    }
    
    # Copiar todas as subpastas de assets
    Get-ChildItem -Path ".\assets" -Directory | ForEach-Object {
        $subFolder = $_.Name
        if (-not (Test-Path -Path "$deployFolder\assets\$subFolder")) {
            New-Item -ItemType Directory -Path "$deployFolder\assets\$subFolder" | Out-Null
        }
        Copy-Item -Path ".\assets\$subFolder\*" -Destination "$deployFolder\assets\$subFolder" -Recurse -Force
        Write-Host "  Copiado assets\$subFolder" -ForegroundColor Gray
    }
}

# Copiar conteúdo da pasta backend
if (Test-Path -Path ".\backend") {
    Write-Host "Copiando backend..." -ForegroundColor Gray
    Copy-Item -Path ".\backend\*" -Destination "$deployFolder\backend\" -Recurse -Force
}

# Copiar conteúdo da pasta PAGES
if (Test-Path -Path ".\PAGES") {
    Write-Host "Copiando PAGES..." -ForegroundColor Gray
    Copy-Item -Path ".\PAGES\*" -Destination "$deployFolder\PAGES\" -Recurse -Force
}

# Copiar conteúdo da pasta uploads se existir
if (Test-Path -Path ".\uploads") {
    Write-Host "Copiando uploads..." -ForegroundColor Gray
    Copy-Item -Path ".\uploads\*" -Destination "$deployFolder\uploads\" -Recurse -Force
}

# Configuração específica para o InfinityFree
# Substituir arquivo de configuração
if (Test-Path -Path ".\config_infinityfree.php") {
    Copy-Item -Path ".\config_infinityfree.php" -Destination "$deployFolder\backend\config.php" -Force
    Write-Host "Arquivo config.php substituído para versão de produção" -ForegroundColor Green
}

if (Test-Path -Path ".\backend\config_infinityfree.php") {
    Copy-Item -Path ".\backend\config_infinityfree.php" -Destination "$deployFolder\backend\config.php" -Force
    Write-Host "Arquivo config.php substituído para versão de produção" -ForegroundColor Green
}

# Substituir URLs
Write-Host "Atualizando referências a localhost..." -ForegroundColor Cyan
$files = Get-ChildItem -Path $deployFolder -Recurse -Include "*.php","*.js","*.css" -File
foreach ($file in $files) {
    try {
        $content = Get-Content -Path $file.FullName -Raw -ErrorAction SilentlyContinue
        if ($content -match "localhost/EntreLinhas" -or $content -match "localhost:8000") {
            $newContent = $content -replace "localhost/EntreLinhas", "entrelinhas.infinityfreeapp.com" -replace "localhost:8000", "entrelinhas.infinityfreeapp.com"
            Set-Content -Path $file.FullName -Value $newContent -Force
            Write-Host "  URL atualizada em: $($file.Name)" -ForegroundColor Gray
        }
    } catch {
        Write-Host "  Erro ao processar: $($file.Name)" -ForegroundColor Red
    }
}

# Verificar se todos os arquivos essenciais foram copiados
$essencialFiles = @(
    "$deployFolder\.htaccess",
    "$deployFolder\index.php",
    "$deployFolder\PAGES\index.php",
    "$deployFolder\backend\config.php"
)

$allOk = $true
foreach ($file in $essencialFiles) {
    if (-not (Test-Path -Path $file)) {
        Write-Host "AVISO: Arquivo essencial não encontrado: $file" -ForegroundColor Red
        $allOk = $false
    }
}

if ($allOk) {
    Write-Host "`n===== PREPARAÇÃO CONCLUÍDA COM SUCESSO =====" -ForegroundColor Green
} else {
    Write-Host "`n===== PREPARAÇÃO CONCLUÍDA COM AVISOS =====" -ForegroundColor Yellow
}

# Contar arquivos para verificação
$totalRoot = (Get-ChildItem -Path "$deployFolder" -File | Measure-Object).Count
$totalBackend = (Get-ChildItem -Path "$deployFolder\backend" -File -Recurse | Measure-Object).Count
$totalPages = (Get-ChildItem -Path "$deployFolder\PAGES" -File -Recurse | Measure-Object).Count
$totalAssets = (Get-ChildItem -Path "$deployFolder\assets" -File -Recurse | Measure-Object).Count

Write-Host "`nResumo de arquivos:"
Write-Host "  Raiz: $totalRoot arquivos" -ForegroundColor Cyan
Write-Host "  backend: $totalBackend arquivos" -ForegroundColor Cyan
Write-Host "  PAGES: $totalPages arquivos" -ForegroundColor Cyan
Write-Host "  assets: $totalAssets arquivos" -ForegroundColor Cyan

Write-Host "`nOs arquivos estão prontos para upload na pasta: $deployFolder" -ForegroundColor Green
Write-Host "Use o FileZilla para fazer o upload de TODOS os arquivos e pastas para a pasta /htdocs do servidor" -ForegroundColor Yellow
