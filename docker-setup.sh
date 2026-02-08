#!/bin/bash

# Docker Setup Script for Laravel Application
# Run this script to set up your Docker environment

echo "=== Laravel Docker Setup ==="

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
        echo ".env file created!"
    else
        echo "Warning: .env.example not found. Please create .env manually."
    fi
fi

# Update .env with Docker database settings
echo ""
echo "Updating .env with Docker database settings..."
sed -i.bak 's/DB_HOST=.*/DB_HOST=db/' .env
sed -i.bak 's/DB_PORT=.*/DB_PORT=3306/' .env
sed -i.bak 's/DB_DATABASE=.*/DB_DATABASE=wayak_db/' .env
sed -i.bak 's/DB_USERNAME=.*/DB_USERNAME=wayak_user/' .env
sed -i.bak 's/DB_PASSWORD=.*/DB_PASSWORD=root/' .env

# Update Redis settings
sed -i.bak 's/REDIS_HOST=.*/REDIS_HOST=redis/' .env
sed -i.bak 's/REDIS_PORT=.*/REDIS_PORT=6379/' .env

# Remove backup file
rm -f .env.bak

echo ".env updated!"

# Build and start containers
echo ""
echo "Building and starting Docker containers..."
docker-compose up -d --build

if [ $? -eq 0 ]; then
    echo "Containers started successfully!"

    echo ""
    echo "Installing PHP dependencies..."
    docker-compose exec -T app composer install

    echo ""
    echo "Generating application key..."
    docker-compose exec -T app php artisan key:generate

    echo ""
    echo "Setting permissions..."
    docker-compose exec -T app chmod -R 775 storage bootstrap/cache
    docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache

    echo ""
    echo "=== Setup Complete! ==="
    echo "Your application is available at: http://localhost:8000"
    echo ""
    echo "Next steps:"
    echo "1. Run migrations: docker-compose exec app php artisan migrate"
    echo "2. Build assets: docker-compose exec node npm install && npm run dev"
    echo "3. Or start dev watcher: docker-compose --profile dev up node"
else
    echo "Failed to start containers. Please check the errors above."
    exit 1
fi

