# Script de preparação para deploy do site EntreLinhas
# Este script prepara os arquivos para deploy em produção

# Definição das pastas
$sourceFolder = "."  # Pasta atual do projeto
$deployFolder = ".\entrelinhas_deploy"  # Pasta onde será criada a versão de deploy

# Criar pasta de deploy se não existir
if (-not (Test-Path -Path $deployFolder)) {
    New-Item -ItemType Directory -Path $deployFolder | Out-Null
    Write-Host "✓ Pasta de deploy criada: $deployFolder" -ForegroundColor Green
}
else {
    Write-Host "! A pasta de deploy já existe. O conteúdo será substituído." -ForegroundColor Yellow
    Remove-Item -Path "$deployFolder\*" -Recurse -Force
}

# Copiar todos os arquivos para a pasta de deploy
Write-Host "Copiando arquivos para a pasta de deploy..." -ForegroundColor Cyan
Copy-Item -Path "$sourceFolder\*" -Destination $deployFolder -Recurse -Force -Exclude @(".git", "node_modules", "*.bak")

# Remover arquivos de teste e desenvolvimento
Write-Host "Removendo arquivos de teste e desenvolvimento..." -ForegroundColor Cyan
$filesToRemove = @(
    "teste_*.php",
    "phpinfo.php",
    "*.log",
    "pre_deploy_checklist.txt",
    "post_deploy_checklist.txt",
    "database_export_guide.txt",
    "upload_instructions.txt",
    "domain_setup.txt"
)

foreach ($pattern in $filesToRemove) {
    Get-ChildItem -Path $deployFolder -Recurse -Filter $pattern | ForEach-Object {
        Remove-Item $_.FullName -Force
        Write-Host "  Removido: $($_.Name)" -ForegroundColor Gray
    }
}

# Substituir arquivos de configuração para produção
if (Test-Path -Path "$deployFolder\backend\config.prod.php") {
    Copy-Item -Path "$deployFolder\backend\config.prod.php" -Destination "$deployFolder\backend\config.php" -Force
    Remove-Item -Path "$deployFolder\backend\config.prod.php" -Force
    Write-Host "✓ Arquivo de configuração substituído com versão de produção" -ForegroundColor Green
}
else {
    Write-Host "! Arquivo config.prod.php não encontrado. Lembre-se de atualizar manualmente o config.php" -ForegroundColor Yellow
}

# Verificar se pasta uploads tem permissão de escrita (não funciona completamente no Windows)
Write-Host "Lembrete: Após fazer upload dos arquivos para o servidor:" -ForegroundColor Yellow
Write-Host "- Certifique-se que a pasta 'uploads' tem permissões de escrita (chmod 755 ou 775)" -ForegroundColor Yellow
Write-Host "- Atualize as credenciais do banco de dados em backend/config.php" -ForegroundColor Yellow
Write-Host "- Configure o arquivo .env ou as variáveis de ambiente para o SendGrid" -ForegroundColor Yellow

Write-Host "Preparação para deploy concluída!" -ForegroundColor Green
Write-Host "Os arquivos prontos para deploy estão na pasta: $deployFolder" -ForegroundColor Green
