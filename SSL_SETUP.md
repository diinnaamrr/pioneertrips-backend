# SSL Certificate Setup Guide

## Option 1: Let's Encrypt with Certbot (Recommended - Free)

### Prerequisites
- Domain name pointing to your server IP
- Port 80 and 443 open in firewall
- Docker and docker-compose installed

### Step 1: Install Certbot on Server

```bash
# On your server (not in Docker)
apt-get update
apt-get install -y certbot

# Or use snap
snap install --classic certbot
ln -sf /snap/bin/certbot /usr/bin/certbot
```

### Step 2: Stop Nginx Container Temporarily

```bash
cd /var/www/wayak-prod
docker compose -f docker-compose.prod.yml stop nginx
```

### Step 3: Get SSL Certificate

```bash
# Replace your-domain.com with your actual domain
certbot certonly --standalone \
  -d your-domain.com \
  -d www.your-domain.com \
  --email your-email@example.com \
  --agree-tos \
  --non-interactive
```

### Step 4: Create Certificate Directories

```bash
cd /var/www/wayak-prod
mkdir -p docker/nginx/ssl certbot/www certbot/conf

# Copy certificates to nginx ssl directory
cp /etc/letsencrypt/live/your-domain.com/fullchain.pem docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/your-domain.com/privkey.pem docker/nginx/ssl/key.pem

# Set proper permissions
chmod 644 docker/nginx/ssl/cert.pem
chmod 600 docker/nginx/ssl/key.pem
```

### Step 5: Update Nginx Config

```bash
# Copy SSL config
cp docker/nginx/default-ssl.conf docker/nginx/default.conf

# Edit and replace your-domain.com with your actual domain
nano docker/nginx/default.conf
# Replace: your-domain.com → your-actual-domain.com
```

### Step 6: Update docker-compose.prod.yml

The file is already updated to include SSL volumes. Make sure it has:

```yaml
ports:
  - "${NGINX_PORT:-80}:80"
  - "${NGINX_SSL_PORT:-443}:443"
volumes:
  - ./docker/nginx/ssl:/etc/nginx/ssl:ro
```

### Step 7: Start Nginx

```bash
docker compose -f docker-compose.prod.yml up -d nginx
```

### Step 8: Set Up Auto-Renewal

```bash
# Create renewal script
cat > /etc/cron.monthly/renew-ssl.sh << 'EOF'
#!/bin/bash
cd /var/www/wayak-prod
certbot renew --quiet
cp /etc/letsencrypt/live/your-domain.com/fullchain.pem docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/your-domain.com/privkey.pem docker/nginx/ssl/key.pem
docker compose -f docker-compose.prod.yml restart nginx
EOF

chmod +x /etc/cron.monthly/renew-ssl.sh
```

---

## Option 2: Use Certbot in Docker (Alternative)

### Add Certbot Service to docker-compose.prod.yml

```yaml
  certbot:
    image: certbot/certbot
    container_name: wayak_certbot
    volumes:
      - ./certbot/www:/var/www/certbot:rw
      - ./certbot/conf:/etc/letsencrypt:rw
    command: certonly --webroot -w /var/www/certbot --email your-email@example.com -d your-domain.com --agree-tos --non-interactive
```

### Get Certificate

```bash
# Make sure nginx is running on port 80 first
docker compose -f docker-compose.prod.yml up -d nginx

# Get certificate
docker compose -f docker-compose.prod.yml run --rm certbot

# Copy certificates
cp certbot/conf/live/your-domain.com/fullchain.pem docker/nginx/ssl/cert.pem
cp certbot/conf/live/your-domain.com/privkey.pem docker/nginx/ssl/key.pem
```

---

## Option 3: Use Your Own SSL Certificates

If you have your own SSL certificates:

```bash
cd /var/www/wayak-prod
mkdir -p docker/nginx/ssl

# Copy your certificates
cp /path/to/your/certificate.crt docker/nginx/ssl/cert.pem
cp /path/to/your/private.key docker/nginx/ssl/key.pem

# Set permissions
chmod 644 docker/nginx/ssl/cert.pem
chmod 600 docker/nginx/ssl/key.pem

# Update nginx config to use SSL (use default-ssl.conf)
cp docker/nginx/default-ssl.conf docker/nginx/default.conf
# Edit and replace your-domain.com

# Restart nginx
docker compose -f docker-compose.prod.yml restart nginx
```

---

## Update .env File

Add to your `.env`:

```env
NGINX_PORT=80
NGINX_SSL_PORT=443
APP_URL=https://your-domain.com
```

---

## Verify SSL

```bash
# Check nginx is listening on 443
ss -tulpn | grep :443

# Test SSL
curl -I https://your-domain.com

# Or use online tools:
# https://www.ssllabs.com/ssltest/
```

---

## Troubleshooting

### Port 443 already in use
```bash
# Find what's using port 443
ss -tulpn | grep :443

# If it's CloudPanel or another service, you may need to:
# 1. Use a different port (e.g., 8443)
# 2. Or configure CloudPanel to proxy to your app
```

### Certificate renewal fails
```bash
# Manually renew
certbot renew

# Check certificate expiry
certbot certificates
```

### Nginx can't read certificates
```bash
# Check permissions
ls -la docker/nginx/ssl/

# Fix permissions
chmod 644 docker/nginx/ssl/cert.pem
chmod 600 docker/nginx/ssl/key.pem
chown root:root docker/nginx/ssl/*
```

---

## Quick Setup Script

```bash
#!/bin/bash
DOMAIN="your-domain.com"
EMAIL="your-email@example.com"

cd /var/www/wayak-prod

# Stop nginx
docker compose -f docker-compose.prod.yml stop nginx

# Get certificate
certbot certonly --standalone -d $DOMAIN -d www.$DOMAIN --email $EMAIL --agree-tos --non-interactive

# Create directories
mkdir -p docker/nginx/ssl certbot/www certbot/conf

# Copy certificates
cp /etc/letsencrypt/live/$DOMAIN/fullchain.pem docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/$DOMAIN/privkey.pem docker/nginx/ssl/key.pem

# Update nginx config
sed "s/your-domain.com/$DOMAIN/g" docker/nginx/default-ssl.conf > docker/nginx/default.conf

# Start nginx
docker compose -f docker-compose.prod.yml up -d nginx

echo "SSL setup complete! Access your site at https://$DOMAIN"
```

Save as `setup-ssl.sh`, make executable (`chmod +x setup-ssl.sh`), and run it.

