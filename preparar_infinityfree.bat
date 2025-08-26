@echo off
echo Preparando arquivos para deploy no InfinityFree...
echo.

rem Cria a pasta de deploy se não existir
if not exist deploy_infinityfree mkdir deploy_infinityfree

rem Copia todos os arquivos e diretórios necessários
echo Copiando arquivos...
xcopy /E /I /Y assets deploy_infinityfree\assets
xcopy /E /I /Y backend deploy_infinityfree\backend
xcopy /E /I /Y PAGES deploy_infinityfree\PAGES
xcopy /E /I /Y uploads deploy_infinityfree\uploads
copy index.php deploy_infinityfree\
copy .htaccess deploy_infinityfree\

rem Remove arquivos de teste
echo Removendo arquivos desnecessários...
del deploy_infinityfree\*.bak /S /Q
del deploy_infinityfree\phpinfo.php
del deploy_infinityfree\teste_*.php /S /Q
del deploy_infinityfree\*.log /S /Q

rem Substitui arquivos de configuração
echo Configurando para produção...
copy backend\config_infinityfree.php deploy_infinityfree\backend\config.php
copy backend\env_infinityfree.php deploy_infinityfree\backend\env_loader.php
copy backend\sendgrid_email_prod.php deploy_infinityfree\backend\sendgrid_email.php

echo.
echo Arquivos preparados com sucesso!
echo Os arquivos para upload estão na pasta deploy_infinityfree
echo.
echo IMPORTANTE: Antes de fazer upload, atualize as credenciais do banco de dados
echo em deploy_infinityfree\backend\config.php com as informações do InfinityFree.
echo.
pause
