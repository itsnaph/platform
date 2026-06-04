# HustleHub Master Startup Script
# Starts MySQL + PHP dev server. Safe to run multiple times -- skips already-running services.

$MYSQL_EXE  = "C:\Users\User\scoop\apps\mysql\current\bin\mysqld.exe"
$PHP_EXE    = "C:\Users\User\scoop\apps\php\current\php.exe"
$MYSQL_PORT = 3306
$PHP_PORT   = 8080
$SCRIPT_DIR = Split-Path -Parent $MyInvocation.MyCommand.Path

function Test-PortOpen($port) {
    $result = Test-NetConnection -ComputerName localhost -Port $port -WarningAction SilentlyContinue -ErrorAction SilentlyContinue
    return $result.TcpTestSucceeded
}

function Wait-ForPort($port, $timeoutSec = 30) {
    $elapsed = 0
    while ($elapsed -lt $timeoutSec) {
        if (Test-PortOpen $port) { return $true }
        Start-Sleep -Milliseconds 500
        $elapsed += 0.5
        Write-Host -NoNewline "."
    }
    return $false
}

Write-Host ""
Write-Host "  HustleHub -- Starting Dev Environment" -ForegroundColor Cyan
Write-Host "  ======================================" -ForegroundColor Cyan
Write-Host ""

# --- 1. MySQL ---
if (Test-PortOpen $MYSQL_PORT) {
    Write-Host "[MySQL]  Already running on port $MYSQL_PORT -- skipping start" -ForegroundColor Green
} else {
    if (-not (Test-Path $MYSQL_EXE)) {
        Write-Host "[MySQL]  ERROR: mysqld not found at $MYSQL_EXE" -ForegroundColor Red
        Write-Host "         Install with: scoop install mysql" -ForegroundColor Yellow
        Read-Host "Press Enter to exit"
        exit 1
    }

    Write-Host "[MySQL]  Starting MySQL 9.7 ..." -ForegroundColor Yellow
    Start-Process -FilePath $MYSQL_EXE -ArgumentList "--console" `
        -WindowStyle Minimized -PassThru | Out-Null

    Write-Host -NoNewline "         Waiting for port $MYSQL_PORT"
    $ready = Wait-ForPort $MYSQL_PORT
    Write-Host ""

    if (-not $ready) {
        Write-Host "[MySQL]  ERROR: MySQL did not start within 30 seconds." -ForegroundColor Red
        Write-Host "         Check the minimised MySQL window for error messages." -ForegroundColor Yellow
        Read-Host "Press Enter to exit"
        exit 1
    }
    Write-Host "[MySQL]  Ready on port $MYSQL_PORT" -ForegroundColor Green
}

# --- 2. PHP dev server ---
if (Test-PortOpen $PHP_PORT) {
    Write-Host "[PHP]    Already running on port $PHP_PORT -- skipping start" -ForegroundColor Green
} else {
    if (-not (Test-Path $PHP_EXE)) {
        Write-Host "[PHP]    ERROR: php.exe not found at $PHP_EXE" -ForegroundColor Red
        Write-Host "         Install with: scoop install php" -ForegroundColor Yellow
        Read-Host "Press Enter to exit"
        exit 1
    }

    Write-Host "[PHP]    Starting PHP 8.5 dev server ..." -ForegroundColor Yellow
    Start-Process -FilePath $PHP_EXE `
        -ArgumentList "-S localhost:$PHP_PORT -t `"$SCRIPT_DIR`"" `
        -WorkingDirectory $SCRIPT_DIR `
        -WindowStyle Minimized -PassThru | Out-Null

    Write-Host -NoNewline "         Waiting for port $PHP_PORT"
    $ready = Wait-ForPort $PHP_PORT
    Write-Host ""

    if (-not $ready) {
        Write-Host "[PHP]    ERROR: PHP dev server did not start within 30 seconds." -ForegroundColor Red
        Write-Host "         Is another app using port $PHP_PORT?" -ForegroundColor Yellow
        Read-Host "Press Enter to exit"
        exit 1
    }
    Write-Host "[PHP]    Ready on port $PHP_PORT" -ForegroundColor Green
}

# --- 3. Open browser ---
Write-Host ""
Write-Host "  All services running -- opening browser ..." -ForegroundColor Cyan
Start-Process "http://localhost:$PHP_PORT"

Write-Host ""
Write-Host "  http://localhost:$PHP_PORT" -ForegroundColor White
Write-Host ""
Write-Host "  Both services run in minimised background windows." -ForegroundColor DarkGray
Write-Host "  Close those windows to stop the servers." -ForegroundColor DarkGray
Write-Host "  This window can be closed now." -ForegroundColor DarkGray
Write-Host ""
