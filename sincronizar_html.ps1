# Script para sincronizar apenas os arquivos HTML e recursos estáticos
# Caminhos de origem e destino
$origem = "C:\Users\User\Documents\EntreLinhas"
$destino = "C:\xampp\htdocs\EntreLinhas"

# Criar o diretório de destino se não existir
if (-not (Test-Path $destino)) {
    New-Item -Path $destino -ItemType Directory -Force
}

# Função para copiar arquivos não PHP e criar diretórios
function Sync-NonPHPFiles {
    param (
        [string]$sourcePath,
        [string]$destPath,
        [int]$level = 0
    )

    # Cria o diretório de destino se não existir
    if (-not (Test-Path $destPath)) {
        New-Item -Path $destPath -ItemType Directory -Force | Out-Null
    }

    # Obtém todos os arquivos no diretório atual
    $files = Get-ChildItem -Path $sourcePath -File

    # Copia os arquivos que não são PHP
    foreach ($file in $files) {
        if ($file.Extension -ne ".php" -and $file.Extension -ne ".sql" -and $file.Name -ne "sincronizar.bat" -and $file.Name -ne "sincronizar.ps1" -and $file.Name -ne "sincronizar_html.ps1") {
            $destFile = Join-Path -Path $destPath -ChildPath $file.Name
            Copy-Item -Path $file.FullName -Destination $destFile -Force
            Write-Host "Copiado: $($file.Name)"
        }
    }

    # Processa os subdiretórios recursivamente
    $dirs = Get-ChildItem -Path $sourcePath -Directory

    foreach ($dir in $dirs) {
        # Pula o diretório backend
        if ($dir.Name -eq "backend") {
            Write-Host "Ignorando diretório: $($dir.Name)"
            continue
        }
        
        $sourceSubDir = Join-Path -Path $sourcePath -ChildPath $dir.Name
        $destSubDir = Join-Path -Path $destPath -ChildPath $dir.Name
        Sync-NonPHPFiles -sourcePath $sourceSubDir -destPath $destSubDir -level ($level + 1)
    }
}

# Limpar o diretório de destino (opcional, remova se não quiser limpar)
Write-Host "Limpando diretório de destino: $destino"
Remove-Item -Path "$destino\*" -Recurse -Force

# Iniciar a sincronização
Write-Host "Iniciando sincronização HTML de $origem para $destino"
Sync-NonPHPFiles -sourcePath $origem -destPath $destino

Write-Host "Sincronização concluída!"
