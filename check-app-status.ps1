# Quick status check script
Write-Host "=== Checking Application Status ===" -ForegroundColor Cyan

Write-Host "`n1. Checking containers..." -ForegroundColor Yellow
docker-compose ps

Write-Host "`n2. Checking if index.php exists..." -ForegroundColor Yellow
docker-compose exec -T app test -f public/index.php
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ index.php exists" -ForegroundColor Green
} else {
    Write-Host "✗ index.php missing" -ForegroundColor Red
}

Write-Host "`n3. Checking if vendor directory exists..." -ForegroundColor Yellow
docker-compose exec -T app test -d vendor
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ vendor directory exists" -ForegroundColor Green
} else {
    Write-Host "✗ vendor directory missing - run: docker-compose exec app composer install" -ForegroundColor Red
}

Write-Host "`n4. Checking if .env file exists..." -ForegroundColor Yellow
docker-compose exec -T app test -f .env
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ .env file exists" -ForegroundColor Green
} else {
    Write-Host "✗ .env file missing - create it from .env.example" -ForegroundColor Red
}

Write-Host "`n5. Testing application response..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000" -Method GET -TimeoutSec 5 -UseBasicParsing -ErrorAction Stop
    Write-Host "✓ Application is responding! Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "✗ Application not responding: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== Status Check Complete ===" -ForegroundColor Cyan
Write-Host "`nAccess your application at: http://localhost:8000" -ForegroundColor Yellow


