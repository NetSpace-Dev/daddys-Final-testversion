@echo off
title POS System Master Launcher
echo ===================================================
echo   Launching POS Cashier and Customer Display...
echo ===================================================
echo.

:: --- CONFIGURATION ---
:: Customize your URLs here if they change in the future:
set POS_URL=https://tv.kandyanpos.lk/Sale/POS/1/1
set DISPLAY_URL=https://tv.kandyanpos.lk/Sale/customer_display

:: Secondary monitor window start position (X-coordinate, Y-coordinate)
:: Change 1920 to 1366 if your primary monitor's resolution width is 1366px.
set SECOND_MONITOR_POSITION=1920,0
:: ---------------------

:: Detect if Google Chrome is installed in the default location
set CHROME_PATH="C:\Program Files\Google\Chrome\Application\chrome.exe"
if not exist %CHROME_PATH% (
    set CHROME_PATH="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
)

:: If Chrome is not found, fallback to default start command
if not exist %CHROME_PATH% (
    echo [WARNING] Google Chrome was not found in default program files path.
    echo Trying to launch using default system browser association...
    start "" "%POS_URL%"
    timeout /t 2 >nul
    start "" "%DISPLAY_URL%"
    goto end
)

echo 1. Launching Main POS Cashier Terminal (Main Monitor)...
start "" %CHROME_PATH% --window-position=0,0 --start-maximized "%POS_URL%"

echo Waiting 2 seconds for main screen initialization...
timeout /t 2 >nul

echo 2. Launching Customer Display Screen (Second Monitor)...
start "" %CHROME_PATH% --window-position=%SECOND_MONITOR_POSITION% --start-fullscreen --app=%DISPLAY_URL%

:end
echo.
echo Launching completed successfully.
echo Done. You can close this window.
timeout /t 3 >nul
