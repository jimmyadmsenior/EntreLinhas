# Script de sincronização para copiar os arquivos do projeto para o XAMPP
# Caminho do seu projeto
$caminhoProjeto = "C:\Users\User\Documents\EntreLinhas"
# Caminho do XAMPP
$caminhoXampp = "C:\xampp\htdocs\EntreLinhas"

Write-Host "=====================================================" -ForegroundColor Blue
Write-Host "           SINCRONIZAÇÃO DO PROJETO ENTRELINHAS      " -ForegroundColor White
Write-Host "=====================================================" -ForegroundColor Blue
Write-Host ""

# Verificar se os diretórios existem
if (-not (Test-Path $caminhoProjeto)) {
    Write-Host "Diretório do projeto não encontrado: $caminhoProjeto" -ForegroundColor Red
    exit 1
}

# Criar diretório XAMPP se não existir
if (-not (Test-Path $caminhoXampp)) {
    Write-Host "Criando diretório no XAMPP: $caminhoXampp" -ForegroundColor Cyan
    New-Item -Path $caminhoXampp -ItemType Directory -Force | Out-Null
}

# Iniciar sincronização
Write-Host "Iniciando sincronização com o XAMPP..." -ForegroundColor Cyan
Write-Host ""

# Copiar todos os arquivos do projeto para o XAMPP
Write-Host "Copiando arquivos..." -ForegroundColor Cyan
Copy-Item -Path "$caminhoProjeto\*" -Destination $caminhoXampp -Recurse -Force

Write-Host ""
Write-Host "Sincronização concluída com sucesso!" -ForegroundColor Green
Write-Host ""
Write-Host "Você pode acessar seu site em: http://localhost/EntreLinhas/" -ForegroundColor Cyan
Write-Host ""
Write-Host "=====================================================" -ForegroundColor Blue

# Perguntar se deseja abrir o site no navegador
$resposta = Read-Host "Deseja abrir o site no navegador agora? (S/N)"
if ($resposta -eq "S" -or $resposta -eq "s") {
    Start-Process "http://localhost/EntreLinhas/"
    Write-Host "Abrindo o site no navegador padrão..." -ForegroundColor Cyan
}

Write-Host ""
Write-Host "Pressione qualquer tecla para sair..." -ForegroundColor Cyan
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
