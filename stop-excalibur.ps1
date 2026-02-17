Get-Process php,node -ErrorAction SilentlyContinue | Stop-Process -Force
Write-Host "Excalibur servers stopped." -ForegroundColor Yellow