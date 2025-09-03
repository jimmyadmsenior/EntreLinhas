@echo off
echo ================================================
echo  Preparando arquivos para upload no InfinityFree
echo ================================================
echo.

echo EXPLICACAO DO PROCESSO:
echo 1. Este script vai preparar os arquivos do projeto para upload
echo 2. Os arquivos serao organizados na pasta "deploy_pronto"
echo 3. Depois, voce precisara fazer o upload via FileZilla para a pasta /htdocs no servidor
echo.

:: Executa o script PowerShell com bypass de política de execução
echo Executando script de preparacao...
powershell -ExecutionPolicy Bypass -File .\deploy_infinityfree.ps1

echo.
echo Se o script terminou com sucesso, seus arquivos estao prontos na pasta "deploy_pronto"
echo.
pause
