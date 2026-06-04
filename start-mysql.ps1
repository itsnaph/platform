# HustleHub MySQL Startup Script
# This script starts MySQL 9.7 database server using Scoop installation

$mysqlPath = "C:\Users\User\scoop\apps\mysql\current\bin\mysqld.exe"

# Verify MySQL exists
if (-not (Test-Path $mysqlPath)) {
    Write-Host "ERROR: MySQL not found at $mysqlPath" -ForegroundColor Red
    Write-Host "MySQL may not be installed. Install it with: scoop install mysql" -ForegroundColor Yellow
    exit 1
}

Write-Host "Starting MySQL Server..." -ForegroundColor Green
Write-Host "   MySQL: $mysqlPath" -ForegroundColor Cyan
Write-Host "   Port: 3306 (default)" -ForegroundColor Cyan
Write-Host "   User: root (no password)" -ForegroundColor Cyan
Write-Host "   Press Ctrl+C to stop" -ForegroundColor Yellow
Write-Host ""

# Run MySQL in console mode (shows output)
# Using @() array syntax to avoid PowerShell parsing issues with --
& $mysqlPath @("--console")
