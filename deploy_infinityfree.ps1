# Script simplificado de preparação para deploy no InfinityFree
# Específico para o projeto EntreLinhas

# Definição das pastas
$sourceFolder = "."  # Pasta atual do projeto (EntreLinhas)
$deployFolder = ".\deploy_pronto"  # Pasta onde será criada a versão pronta para upload

# Informações de conexão FTP
$ftpHost = "ftpupload.net"
$ftpUser = "if0_39798697"
$ftpPassword = "jimmysena123"
$ftpPort = "21"

# Criar pasta de deploy se não existir
if (-not (Test-Path -Path $deployFolder)) {
    New-Item -ItemType Directory -Path $deployFolder | Out-Null
    Write-Host "✓ Pasta de deploy criada: $deployFolder" -ForegroundColor Green
}
else {
    Write-Host "! A pasta $deployFolder já existe. O conteúdo será substituído." -ForegroundColor Yellow
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
        Write-Host "  Pasta criada: $folder" -ForegroundColor Gray
    }
}

# Copiar arquivos para a pasta de deploy
Write-Host "Copiando arquivos para a pasta de deploy..." -ForegroundColor Cyan
$filesToCopy = Get-ChildItem -Path $sourceFolder -File -Filter "*.php" | Where-Object { $_.Name -notlike "teste_*" -and $_.Name -notlike "diagnostico_*" }
foreach ($file in $filesToCopy) {
    Copy-Item -Path $file.FullName -Destination $deployFolder -Force
    Write-Host "  Copiado: $($file.Name)" -ForegroundColor Gray
}

# Copiar o .htaccess se existir
if (Test-Path -Path "$sourceFolder\.htaccess") {
    Copy-Item -Path "$sourceFolder\.htaccess" -Destination $deployFolder -Force
    Write-Host "  Copiado: .htaccess" -ForegroundColor Gray
}

# Copiar conteúdo das pastas específicas
foreach ($folder in $foldersToCopy) {
    if (Test-Path -Path "$sourceFolder\$folder") {
        Copy-Item -Path "$sourceFolder\$folder\*" -Destination "$deployFolder\$folder" -Recurse -Force
        Write-Host "  Conteúdo copiado: $folder" -ForegroundColor Gray
    }
}

# Remover arquivos de teste e desenvolvimento
Write-Host "Removendo arquivos de teste e desenvolvimento..." -ForegroundColor Cyan
$filesToRemove = @(
    "teste_*.php",
    "diagnostico_*.php",
    "phpinfo.php",
    "*.log",
    "*.bak",
    "*_temp.*",
    "pre_deploy_checklist.txt",
    "post_deploy_checklist.txt"
)

foreach ($pattern in $filesToRemove) {
    Get-ChildItem -Path $deployFolder -Recurse -Filter $pattern | ForEach-Object {
        Remove-Item $_.FullName -Force
        Write-Host "  Removido: $($_.Name)" -ForegroundColor Gray
    }
}

# Substituir arquivos de configuração para produção
if (Test-Path -Path "$sourceFolder\config_infinityfree.php") {
    Copy-Item -Path "$sourceFolder\config_infinityfree.php" -Destination "$deployFolder\backend\config.php" -Force
    Write-Host "✓ Arquivo de configuração substituído com versão do InfinityFree" -ForegroundColor Green
}
else {
    Write-Host "! Arquivo config_infinityfree.php não encontrado. Lembre-se de atualizar manualmente o config.php" -ForegroundColor Yellow
}

if (Test-Path -Path "$sourceFolder\backend\config_infinityfree.php") {
    Copy-Item -Path "$sourceFolder\backend\config_infinityfree.php" -Destination "$deployFolder\backend\config.php" -Force
    Write-Host "✓ Arquivo de configuração substituído com versão do InfinityFree" -ForegroundColor Green
}

# Substituir URL's
Write-Host "Atualizando referências a localhost..." -ForegroundColor Cyan
$files = Get-ChildItem -Path $deployFolder -Recurse -Include "*.php","*.js","*.css"
foreach ($file in $files) {
    $content = Get-Content -Path $file.FullName -Raw
    if ($content -match "localhost/EntreLinhas" -or $content -match "localhost:8000") {
        $newContent = $content -replace "localhost/EntreLinhas", "entrelinhas.infinityfreeapp.com" -replace "localhost:8000", "entrelinhas.infinityfreeapp.com"
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "  Atualizado: $($file.FullName)" -ForegroundColor Gray
    }
}

Write-Host "`n===== PREPARAÇÃO CONCLUÍDA =====" -ForegroundColor Green
Write-Host "Os arquivos para upload estão prontos na pasta: $deployFolder" -ForegroundColor Cyan
Write-Host "`nPara fazer o upload, use o FileZilla com os seguintes dados:" -ForegroundColor Yellow
Write-Host "  Servidor: $ftpHost" -ForegroundColor White
Write-Host "  Usuário: $ftpUser" -ForegroundColor White
Write-Host "  Senha: $ftpPassword" -ForegroundColor White
Write-Host "  Porta: $ftpPort" -ForegroundColor White
Write-Host "`nPASSOS PARA UPLOAD:" -ForegroundColor Green
Write-Host "1. No FileZilla, conecte-se ao servidor usando os dados acima" -ForegroundColor White
Write-Host "2. No painel DIREITO (servidor), navegue até a pasta '/htdocs'" -ForegroundColor White
Write-Host "3. No painel ESQUERDO (local), navegue até a pasta 'C:\Users\User\Documents\EntreLinhas\$deployFolder'" -ForegroundColor White
Write-Host "4. Selecione TODOS os arquivos e pastas na pasta '$deployFolder'" -ForegroundColor White
Write-Host "5. Arraste tudo para o painel direito (pasta /htdocs) para iniciar o upload" -ForegroundColor White
Write-Host "`nApós o upload, seu site estará disponível em: https://entrelinhas.infinityfreeapp.com" -ForegroundColor Green

Write-Host "`nDeseja iniciar o FileZilla automaticamente? (S/N)" -ForegroundColor Cyan
$response = Read-Host
if ($response -eq "S" -or $response -eq "s") {
    $filezillaPath = "C:\Program Files\FileZilla FTP Client\filezilla.exe"
    if (Test-Path $filezillaPath) {
        Start-Process $filezillaPath
        Write-Host "FileZilla iniciado!" -ForegroundColor Green
    } else {
        Write-Host "FileZilla não encontrado no caminho padrão. Inicie-o manualmente." -ForegroundColor Yellow
    }
}
