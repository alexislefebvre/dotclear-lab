@echo off
if "%DCROOT%" == "" goto doc
xcopy ..\trunk %DCROOT%\plugins\myForms /D /E /EXCLUDE:exclude.txt /Y
goto end
:doc
echo =========================================================================
echo This script copies all updated files in svn trunk to Dotclear plugin tree
echo You need to set the DCROOT env var with the path to your Dotclear install
echo =========================================================================
:end
