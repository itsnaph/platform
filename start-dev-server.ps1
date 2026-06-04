# HustleHub Development Server Startup Script
# This script starts the PHP development server with the official PHP 8.3 installation

# Set the correct PHP installation - Use Scoop PHP since official has internal path issues
$phpPath = "C:\Users\User\scoop\apps\php\current\php.exe"
$projectPath = Split-Path -Parent $MyInvocation.MyCommand.Path

# Verify PHP exists
if (-not (Test-Path $phpPath)) {
    Write-Host "ERROR: PHP not found at $phpPath" -ForegroundColor Red
    Write-Host "Install PHP with: scoop install php" -ForegroundColor Yellow
    exit 1
}

Write-Host "Starting HustleHub Dev Server" -ForegroundColor Green
Write-Host "   PHP: $phpPath" -ForegroundColor Cyan
Write-Host "   Project: $projectPath" -ForegroundColor Cyan
Write-Host "   URL: http://localhost:8080" -ForegroundColor Cyan
Write-Host "   Press Ctrl+C to stop" -ForegroundColor Yellow
Write-Host ""

# Change to project directory
cd $projectPath

# Start the development server
& $phpPath -S localhost:8080 -t .
