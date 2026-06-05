Write-Host ""
Write-Host "  HustleHub -- Starting Demo Environment" -ForegroundColor Cyan
Write-Host "  =======================================" -ForegroundColor Cyan
Write-Host ""

# Start MySQL + PHP server
Start-Process powershell -ArgumentList "-ExecutionPolicy Bypass -File `"$PSScriptRoot\start-all.ps1`""

Write-Host "  Waiting for local server to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 6

# Start Cloudflare tunnel
Write-Host ""
Write-Host "  Starting Cloudflare tunnel..." -ForegroundColor Yellow
Write-Host "  Your public URL will appear below -- copy it for your demo." -ForegroundColor Green
Write-Host ""

& "C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --url http://localhost:8080
