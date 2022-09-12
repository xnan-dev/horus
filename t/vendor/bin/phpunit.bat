@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/phpunit
SET COMPOSER_RUNTIME_BIN_DIR=%~dp0
D:\devel\xampp\php\php.exe "%BIN_TARGET%" %*
pause
