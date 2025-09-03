@echo off
cls
echo ===================================================
echo     PREPARAR ARQUIVOS PARA UPLOAD - INFINITYFREE
echo ===================================================
echo.
echo Este script vai criar uma pasta "deploy_pronto" com todos
echo os arquivos necessarios para upload no InfinityFree.
echo.
echo Pressione qualquer tecla para continuar...
pause > nul

powershell -ExecutionPolicy Bypass -File deploy_simple.ps1

echo.
echo Pressione qualquer tecla para sair...
pause > nul
