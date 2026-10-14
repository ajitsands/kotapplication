@echo off
title SaNDS KOT & Billing System (Port 3031)
cd /d "%~dp0"

:: Ensure PHP is in PATH
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    if exist "C:\AppServ\php8\php.exe" (
        set "PATH=C:\AppServ\php8;%PATH%"
    ) else if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    )
)

:: Free port 3031 if previously occupied
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":3031" ^| findstr "LISTENING"') do (
    taskkill /f /pid %%a >nul 2>&1
)

echo ================================================================
echo           SaNDS KOT & RESTAURANT BILLING SYSTEM
echo ================================================================
echo.
echo  Starting application on: http://localhost:3031
echo.
echo  Quick Links:
echo    - Main Page:     http://localhost:3031/
echo    - Staff Login:   http://localhost:3031/login
echo    - Waiter App:    http://localhost:3031/waiter-app
echo    - Kitchen (KOT): http://localhost:3031/kot
echo    - Cashier:       http://localhost:3031/counter
echo    - Admin Console: http://localhost:3031/admin
echo.
echo  Login Credentials:
echo    - Admin:   admin    / admin123
echo    - Waiter:  waiter1  / waiter123
echo    - Chef:    chef1    / kot123
echo    - Cashier: counter1 / counter123
echo.
echo  [NOTE] Keep this window OPEN while using the application.
echo         Press Ctrl+C to stop the server.
echo ================================================================
echo.

:: Automatically open default browser after 2 seconds
start "" cmd /c "timeout /t 2 /nobreak >nul && start http://localhost:3031"

:: Start PHP built-in server
php -S 0.0.0.0:3031 index.php

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Failed to start PHP server on port 3031.
    echo Ensure PHP is installed and available.
    echo.
    pause
)
