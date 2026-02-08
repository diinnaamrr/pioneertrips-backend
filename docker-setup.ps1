# Docker Setup Script for Laravel Application
# Run this script to set up your Docker environment

Write-Host "=== Laravel Docker Setup ===" -ForegroundColor Green

# Check if .env exists
if (-not (Test-Path .env)) {
    Write-Host "Creating .env file from .env.example..." -ForegroundColor Yellow
    if (Test-Path .env.example) {
        Copy-Item .env.example .env
        Write-Host ".env file created!" -ForegroundColor Green
    } else {
        Write-Host "Warning: .env.example not found. Please create .env manually." -ForegroundColor Red
    }
}

# Update .env with Docker database settings
Write-Host "`nUpdating .env with Docker database settings..." -ForegroundColor Yellow
$envContent = Get-Content .env -Raw

# Update DB settings
$envContent = $envContent -replace 'DB_HOST=.*', 'DB_HOST=db'
$envContent = $envContent -replace 'DB_PORT=.*', 'DB_PORT=3306'
$envContent = $envContent -replace 'DB_DATABASE=.*', 'DB_DATABASE=wayak_db'
$envContent = $envContent -replace 'DB_USERNAME=.*', 'DB_USERNAME=wayak_user'
$envContent = $envContent -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=root'

# Update Redis settings
$envContent = $envContent -replace 'REDIS_HOST=.*', 'REDIS_HOST=redis'
$envContent = $envContent -replace 'REDIS_PORT=.*', 'REDIS_PORT=6379'

Set-Content .env $envContent
Write-Host ".env updated!" -ForegroundColor Green

# Build and start containers
Write-Host "`nBuilding and starting Docker containers..." -ForegroundColor Yellow
docker-compose up -d --build

if ($LASTEXITCODE -eq 0) {
    Write-Host "Containers started successfully!" -ForegroundColor Green

    Write-Host "`nInstalling PHP dependencies..." -ForegroundColor Yellow
    docker-compose exec -T app composer install

    Write-Host "`nGenerating application key..." -ForegroundColor Yellow
    docker-compose exec -T app php artisan key:generate

    Write-Host "`nSetting permissions..." -ForegroundColor Yellow
    docker-compose exec -T app chmod -R 775 storage bootstrap/cache
    docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache

    Write-Host "`n=== Setup Complete! ===" -ForegroundColor Green
    Write-Host "Your application is available at: http://localhost:8000" -ForegroundColor Cyan
    Write-Host "`nNext steps:" -ForegroundColor Yellow
    Write-Host "1. Run migrations: docker-compose exec app php artisan migrate" -ForegroundColor White
    Write-Host "2. Build assets: docker-compose exec node npm install && npm run dev" -ForegroundColor White
    Write-Host "3. Or start dev watcher: docker-compose --profile dev up node" -ForegroundColor White
} else {
    Write-Host "Failed to start containers. Please check the errors above." -ForegroundColor Red
}

