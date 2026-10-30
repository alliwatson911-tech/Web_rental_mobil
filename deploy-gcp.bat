@echo off
setlocal
chcp 65001 >nul
echo ==========================================================
echo   PADAIDI RENT CAR - GOOGLE CLOUD RUN DEPLOYMENT
echo ==========================================================
echo.

if exist "%LOCALAPPDATA%\Google\Cloud SDK\google-cloud-sdk\bin\gcloud.cmd" (
    set "PATH=%LOCALAPPDATA%\Google\Cloud SDK\google-cloud-sdk\bin;%PATH%"
)

where gcloud >nul 2>nul
if %errorlevel% neq 0 (
    echo [!] Google Cloud SDK belum terdeteksi di sistem ini.
    echo     Silakan unduh dan pasang melalui installer resmi:
    echo     https://cloud.google.com/sdk/docs/install-sdk
    echo.
    pause
    exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy-gcp.ps1"

pause
