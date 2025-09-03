# Script simplificado de preparação para deploy no InfinityFree
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

# Copiar arquivos para a pasta de deploy
Write-Host "Copiando arquivos para a pasta de deploy..." -ForegroundColor Cyan
$filesToCopy = Get-ChildItem -Path $sourceFolder -File -Filter "*.php" | Where-Object { $_.Name -notlike "teste_*" -and $_.Name -notlike "diagnostico_*" }
foreach ($file in $filesToCopy) {
    Copy-Item -Path $file.FullName -Destination $deployFolder -Force
    Write-Host "Copiado: $($file.Name)" -ForegroundColor Gray
}

Write-Host "Preparacao concluida. Os arquivos estao na pasta: $deployFolder" -ForegroundColor Green
