@echo off
if "%DCROOT%" == "" goto doc
set releasespath=..\releases
set imagepath=%releasespath%\myForms
set packagepath=%releasespath%\plugin-myForms-x.x.zip
rmdir /S /Q %imagepath%
mkdir %imagepath%
del %packagepath%
xcopy ..\trunk %imagepath% /D /E /EXCLUDE:exclude.txt /Y
7za a %packagepath% %imagepath%
copy %packagepath% %DCROOT%\public
goto end
:doc
echo =========================================================================
echo This script copies the plugin package to the public folder in the Dotclear plugin tree
echo You need to set the DCROOT env var with the path to your Dotclear install
echo =========================================================================
:end
