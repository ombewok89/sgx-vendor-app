@echo off
TITLE SGX Vendor Work Evidence - Application Runner
COLOR 0B
cls
echo =======================================================
echo   SGX VENDOR WORK EVIDENCE - STARTING SYSTEM
echo =======================================================
echo.
echo [1/2] Memeriksa Node.js environment...
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Node.js belum terinstall atau tidak terdaftar di PATH.
    pause
    exit /b
)

echo [2/2] Menjalankan Backend Server dan Frontend Client...
echo.
node start.js

pause
