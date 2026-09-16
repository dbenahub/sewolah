@echo off
cd /d "%~dp0"
echo.
echo ===================================================
echo   SEWOLAH - Push perubahan ke GitHub
echo   Folder: %cd%
echo ===================================================
echo.

git status
echo.

set /p MSG="Taip penerangan ringkas untuk perubahan ni (atau tekan Enter untuk guna default): "
if "%MSG%"=="" set MSG=Update fail SEWOLAH

git add .
git add -f storage/app/public/vehicles
git commit -m "%MSG%"
git push

echo.
echo ===================================================
echo   SIAP! Tengok atas ni kalau ada error.
echo   Kalau semua OK, fail dah sampai ke GitHub.
echo ===================================================
echo.
pause
