# Script de sincronização para copiar os arquivos do projeto para o XAMPP
# Salve este arquivo como 'sincronizar.ps1' na pasta do seu projeto

# Caminho do seu projeto
$caminhoProjeto = "C:\Users\3anoA\Documents\EntreLinhas"
# Caminho do XAMPP
$caminhoXampp = "C:\xampp\htdocs\EntreLinhas"

# Função para mostrar mensagem colorida
function Mostrar-Mensagem {
    param (
        [string]$mensagem,
        [string]$tipo = "info" # info, sucesso, erro
    )
    
    switch ($tipo) {
        "info" { Write-Host $mensagem -ForegroundColor Cyan }
        "sucesso" { Write-Host $mensagem -ForegroundColor Green }
        "erro" { Write-Host $mensagem -ForegroundColor Red }
    }
}

# Limpar a tela
Clear-Host

# Mostrar cabeçalho
Write-Host "=====================================================" -ForegroundColor Blue
Write-Host "           SINCRONIZAÇÃO DO PROJETO ENTRELINHAS      " -ForegroundColor White
Write-Host "=====================================================" -ForegroundColor Blue
Write-Host ""

# Verificar se os diretórios existem
if (-not (Test-Path $caminhoProjeto)) {
    Mostrar-Mensagem "❌ Diretório do projeto não encontrado: $caminhoProjeto" "erro"
    exit 1
}

if (-not (Test-Path $caminhoXampp)) {
    Mostrar-Mensagem "❌ Diretório XAMPP não encontrado: $caminhoXampp" "erro"
    exit 1
}

# Iniciar sincronização
Mostrar-Mensagem "🔄 Iniciando sincronização com o XAMPP..." "info"
Write-Host ""

# Copiar todos os arquivos do projeto para o XAMPP
Mostrar-Mensagem "📂 Copiando arquivos..." "info"
Copy-Item -Path "$caminhoProjeto\*" -Destination $caminhoXampp -Recurse -Force

Write-Host ""
Mostrar-Mensagem "✅ Sincronização concluída com sucesso!" "sucesso"
Write-Host ""
Mostrar-Mensagem "🌐 Você pode acessar seu site em: http://localhost/EntreLinhas/" "info"
Write-Host ""
Write-Host "=====================================================" -ForegroundColor Blue

# Perguntar se deseja abrir o site no navegador
$resposta = Read-Host "Deseja abrir o site no navegador agora? (S/N)"
if ($resposta -eq "S" -or $resposta -eq "s") {
    Start-Process "http://localhost/EntreLinhas/"
    Mostrar-Mensagem "🌐 Abrindo o site no navegador padrão..." "info"
}

Write-Host ""
Mostrar-Mensagem "Pressione qualquer tecla para sair..." "info"
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
