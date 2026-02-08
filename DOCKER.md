# Docker Setup Guide

This guide will help you set up and run the Laravel application using Docker.

## Prerequisites

- Docker Desktop (Windows/Mac) or Docker Engine + Docker Compose (Linux)
- Git

## Quick Start

1. **Copy environment file** (if not already present):
   ```bash
   cp .env.example .env
   ```

2. **Update your `.env` file** with the following database configuration:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=wayak_db
   DB_USERNAME=wayak_user
   DB_PASSWORD=root
   
   REDIS_HOST=redis
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

3. **Build and start the containers**:
   ```bash
   docker-compose up -d --build
   ```

4. **Install PHP dependencies**:
   ```bash
   docker-compose exec app composer install
   ```

5. **Generate application key**:
   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Run database migrations**:
   ```bash
   docker-compose exec app php artisan migrate
   ```

7. **Install and build frontend assets**:
   ```bash
   docker-compose exec node npm install
   docker-compose exec node npm run dev
   ```

8. **Set proper permissions** (if needed):
   ```bash
   docker-compose exec app chmod -R 775 storage bootstrap/cache
   docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
   ```

## Access the Application

- **Web Application**: http://localhost:8000
- **MySQL Database**: localhost:3306
- **Redis**: localhost:6379

## Common Commands

### Start containers
```bash
docker-compose up -d
```

### Stop containers
```bash
docker-compose down
```

### View logs
```bash
docker-compose logs -f app
docker-compose logs -f nginx
```

### Execute Artisan commands
```bash
docker-compose exec app php artisan [command]
```

### Access container shell
```bash
docker-compose exec app bash
```

### Run database migrations
```bash
docker-compose exec app php artisan migrate
```

### Run database seeders
```bash
docker-compose exec app php artisan db:seed
```

### Clear application cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Rebuild containers
```bash
docker-compose down
docker-compose up -d --build
```

## Services

- **app**: PHP-FPM 8.2 application container
- **nginx**: Web server (port 8080)
- **db**: MySQL 8.0 database
- **redis**: Redis cache/queue service
- **node**: Node.js for asset compilation

## Troubleshooting

### Permission Issues
If you encounter permission issues with storage or cache directories:
```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues
Ensure your `.env` file has the correct database host (`db` instead of `localhost` or `127.0.0.1`).

### Port Conflicts
If port 8000, 3306, or 6379 are already in use, modify the ports in `docker-compose.yml`.

### Clear Everything and Start Fresh
```bash
docker-compose down -v
docker-compose up -d --build
```

## Production Considerations

For production deployment, consider:
- Using environment-specific `.env` files
- Setting up proper SSL/TLS certificates
- Configuring proper database backups
- Using managed database services
- Setting up proper logging and monitoring
- Optimizing PHP-FPM and Nginx configurations
- Using multi-stage builds for smaller images

