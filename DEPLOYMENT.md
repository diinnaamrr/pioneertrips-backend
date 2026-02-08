# Deployment Guide

This project uses GitHub Actions for CI/CD to automatically deploy to servers when code is pushed to specific branches.

## 🚀 Workflow Overview

### Dev Environment
- **Trigger**: Push to `dev` branch
- **Image Tag**: `ghcr.io/{username}/wayak:dev`
- **Server Path**: `/var/www/wayak-dev`

### Production Environment
- **Trigger**: Push to `main` or `master` branch
- **Image Tag**: `ghcr.io/{username}/wayak:latest`
- **Server Path**: `/var/www/wayak-prod`

## 📋 Prerequisites

### GitHub Secrets Setup

Go to your repository → Settings → Secrets and variables → Actions, and add:

#### For Dev Environment:
- `SSH_HOST` - Your dev server IP/hostname
- `SSH_USER` - SSH username
- `SSH_KEY` - Private SSH key (content, not path)
- `SSH_PORT` - (Optional) SSH port, defaults to 22

#### For Production Environment:
- `SSH_HOST_PROD` - Your production server IP/hostname
- `SSH_USER_PROD` - SSH username for production
- `SSH_KEY_PROD` - Private SSH key for production
- `SSH_PORT_PROD` - (Optional) SSH port, defaults to 22

**Note**: `GITHUB_TOKEN` is automatically provided by GitHub Actions, no need to add it manually.

### Server Setup

1. **Install Docker & Docker Compose**:
```bash
# Ubuntu/Debian
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
sudo apt-get install docker-compose-plugin
```

2. **Create directories on server**:
```bash
# Dev environment
sudo mkdir -p /var/www/wayak-dev
sudo chown $USER:$USER /var/www/wayak-dev

# Production environment
sudo mkdir -p /var/www/wayak-prod
sudo chown $USER:$USER /var/www/wayak-prod
```

3. **Copy files to server**:
```bash
# Dev
scp docker-compose.dev.yml root@31.97.53.11:/var/www/wayak-dev/docker-compose.dev.yml
scp docker/nginx/default.conf root@31.97.53.11:/var/www/wayak-dev/docker/nginx/default.conf
scp docker/php/local.ini root@31.97.53.11:/var/www/wayak-dev/docker/php/local.ini

# Production
scp docker-compose.prod.yml root@31.97.53.11:/var/www/wayak-prod/docker-compose.prod.yml
scp docker/nginx/default.conf root@31.97.53.11:/var/www/wayak-prod/docker/nginx/default.conf
scp docker/php/local.ini root@31.97.53.11:/var/www/wayak-prod/docker/php/local.ini
```

4. **Create `.env` file on server**:
```bash
# On server, create .env file with your configuration
cd /var/www/wayak-dev  # or wayak-prod
nano .env
```

Example `.env`:
```env
# Application
APP_NAME=Wayak
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=wayak_db
DB_USERNAME=wayak_user
DB_PASSWORD=your-secure-password
DB_ROOT_PASSWORD=your-root-password

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Docker Image
GITHUB_USERNAME=your-github-username
IMAGE_TAG=latest  # or 'dev' for dev environment

# Nginx
NGINX_PORT=80
NGINX_SSL_PORT=443
```

5. **Update docker-compose.prod.yml**:
Edit the file on server and replace `${GITHUB_USERNAME}` with your actual GitHub username, or use environment variable.

## 🔄 Deployment Process

### Automatic Deployment

1. **Push to dev branch**:
```bash
git checkout dev
git add .
git commit -m "Your changes"
git push origin dev
```

2. **Push to main/master branch**:
```bash
git checkout main
git merge dev
git push origin main
```

The GitHub Actions workflow will:
- Build Docker image
- Push to GitHub Container Registry (GHCR)
- SSH to server
- Pull latest image
- Restart containers
- Clear Laravel caches

### Manual Deployment (if needed)

```bash
# SSH to server
ssh user@server

# Dev
cd /var/www/wayak-dev
docker pull ghcr.io/your-username/wayak:dev
docker compose -f docker-compose.prod.yml up -d

# Production
cd /var/www/wayak-prod
docker pull ghcr.io/your-username/wayak:latest
docker compose -f docker-compose.prod.yml up -d
```

## 🔙 Rollback

If you need to rollback to a previous version:

```bash
# SSH to server
cd /var/www/wayak-prod  # or wayak-dev

# Pull specific tag/version
docker pull ghcr.io/your-username/wayak:dev-abc1234  # specific commit SHA

# Or edit docker-compose.prod.yml to use specific tag
# Then restart
docker compose -f docker-compose.prod.yml up -d
```

## 🔍 Troubleshooting

### Check workflow logs
Go to GitHub → Actions tab → Select the failed workflow run

### Check server logs
```bash
# Container logs
docker logs wayak_app
docker logs wayak_nginx

# Docker compose logs
docker compose -f docker-compose.prod.yml logs -f
```

### Common issues

1. **SSH connection failed**: Check SSH key and host in GitHub secrets
2. **Image pull failed**: Verify GHCR permissions and image name
3. **Container won't start**: Check `.env` file and docker-compose.yml
4. **Permission errors**: Ensure storage and bootstrap/cache have correct permissions

## 📝 Notes

- The `.env` file should **never** be committed to Git
- Each environment (dev/prod) should have its own `.env` file on the server
- Database backups are stored in `./backups` directory (production)
- Images are cached in GitHub Actions for faster builds
- Old Docker images are automatically pruned after deployment

