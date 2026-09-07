@echo off
title Customer Display Launcher
echo ===================================================
echo   Launching Customer Display on Second Monitor...
echo ===================================================
echo.

:: Detect if Chrome is installed in the default location
set CHROME_PATH="C:\Program Files\Google\Chrome\Application\chrome.exe"
if not exist %CHROME_PATH% (
    set CHROME_PATH="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
)

:: If Chrome is not found, fallback to default start command
if not exist %CHROME_PATH% (
    echo [WARNING] Google Chrome was not found in default program files path.
    echo Trying to launch using default system browser association...
    start "" "https://tv.kandyanpos.lk/Sale/customer_display"
    goto end
)

:: Launcher configuration:
:: --window-position=1920,0 moves the window to the second monitor.
:: (Change 1920 to 1366 if your main monitor's resolution width is 1366px).
:: --start-fullscreen opens in fullscreen.
:: --app=URL opens Chrome in clean App Mode (no address bar, tabs, or toolbars).

echo Launching in App Mode...
start "" %CHROME_PATH% --window-position=1920,0 --start-fullscreen --app=https://tv.kandyanpos.lk/Sale/customer_display

:end
echo Done. You can close this window.
timeout /t 3 >nul
