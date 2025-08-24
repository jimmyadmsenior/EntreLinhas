@echo off
echo Sincronizando arquivos HTML e recursos estaticos...
powershell -ExecutionPolicy Bypass -File "%~dp0sincronizar_html.ps1"
echo.
echo Sincronizacao concluida!
pause
