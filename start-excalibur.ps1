Write-Host "Starting Excalibur servers..." -ForegroundColor Cyan

# Start Laravel API
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PSScriptRoot'; php artisan serve"

# Start React frontend
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PSScriptRoot\frontend'; npm run dev"

Write-Host "Servers launching..." -ForegroundColor Green
Write-Host "Frontend: http://localhost:5173"
Write-Host "API: http://localhost:8000"
