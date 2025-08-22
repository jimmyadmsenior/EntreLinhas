@echo off
echo Sincronizando projeto EntreLinhas com o XAMPP...
xcopy "C:\Users\3anoA\Documents\EntreLinhas\*" "C:\xampp\htdocs\EntreLinhas\" /E /Y /D

echo.
echo Sincronização concluída!
echo Acesse http://localhost/EntreLinhas/ no navegador
pause
