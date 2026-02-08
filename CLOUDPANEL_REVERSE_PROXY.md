# CloudPanel Nginx as Reverse Proxy - Complete Setup

## Problem
We stopped CloudPanel's Nginx to free port 80, which broke CloudPanel functionality.

## Solution
Configure CloudPanel's Nginx as a reverse proxy to our Docker container, keeping both working!

---

## Architecture

```
Internet → Port 80 (CloudPanel Nginx)
                ↓
        Reverse Proxy
                ↓
    ┌───────────┼───────────┐
    ↓           ↓           ↓
CloudPanel  wayak.duckdns.org  Other Sites
(8443)      → Docker:8111      (if any)
```

---

## Step 1: Update Docker Compose (Use Port 8111)

Update `docker-compose.prod.yml` to use port 8111 instead of 80:

```yaml
nginx:
  ports:
    - "8111:80"  # Changed from "80:80" to "8111:80"
    # - "443:443"   # Commented out - SSL handled by CloudPanel Nginx
```

**On Server:**
```bash
cd /var/www/wayak-prod

# Update docker-compose.prod.yml
# Change line 32 from:
#   - "${NGINX_PORT:-80}:80"
# To:
#   - "8111:80"

# Or use sed:
sed -i 's/"${NGINX_PORT:-80}:80"/"8111:80"/' docker-compose.prod.yml

# Restart containers
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d

# Verify port 8111 is listening
docker ps | grep wayak_nginx
netstat -tulpn | grep :8111
```

---

## Step 2: Start CloudPanel Nginx

```bash
# Start CloudPanel Nginx
sudo systemctl start nginx
sudo systemctl enable nginx  # Enable on boot

# Verify it's running
sudo systemctl status nginx
sudo netstat -tulpn | grep :80
# Should show nginx listening on port 80
```

---

## Step 3: Create CloudPanel Nginx Configuration

CloudPanel stores configurations in `/etc/nginx/sites-available/` and `/etc/nginx/sites-enabled/`.

### Option A: Create New Site Configuration (Recommended)

```bash
# Create configuration file
sudo nano /etc/nginx/sites-available/wayak.duckdns.org
```

Add this configuration:

```nginx
# HTTP Server - Redirect to HTTPS (if you have SSL)
server {
    listen 80;
    listen [::]:80;
    server_name wayak.duckdns.org;

    # Let's Encrypt challenge (if using certbot)
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }

    # Redirect to HTTPS (if SSL is configured)
    # Uncomment if you want HTTPS redirect:
    # return 301 https://$host$request_uri;

    # Or proxy directly to Docker (if no SSL):
    location / {
        proxy_pass http://127.0.0.1:8111;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;

        # WebSocket support (if needed)
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;

        # Buffer settings
        proxy_buffering off;
        proxy_request_buffering off;
    }
}

# HTTPS Server (if you have SSL certificate)
# Uncomment and configure if you have SSL:
# server {
#     listen 443 ssl http2;
#     listen [::]:443 ssl http2;
#     server_name wayak.duckdns.org;
#
#     ssl_certificate /etc/letsencrypt/live/wayak.duckdns.org/fullchain.pem;
#     ssl_certificate_key /etc/letsencrypt/live/wayak.duckdns.org/privkey.pem;
#
#     location / {
#         proxy_pass http://127.0.0.1:8111;
#         proxy_set_header Host $host;
#         proxy_set_header X-Real-IP $remote_addr;
#         proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
#         proxy_set_header X-Forwarded-Proto https;
#         proxy_set_header X-Forwarded-Host $host;
#         proxy_set_header X-Forwarded-Port $server_port;
#     }
# }
```

### Option B: Use CloudPanel's Site Manager

If CloudPanel has a web interface for managing sites:

1. Login to CloudPanel: `https://31.97.53.11:8443`
2. Go to Sites → Add Site
3. Domain: `wayak.duckdns.org`
4. Choose "Reverse Proxy" option
5. Backend: `http://127.0.0.1:8111`

---

## Step 4: Enable the Site

```bash
# Create symlink to enable site
sudo ln -s /etc/nginx/sites-available/wayak.duckdns.org /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t

# If test passes, reload Nginx
sudo systemctl reload nginx
```

---

## Step 5: Update Laravel Configuration

Since requests now come through CloudPanel's Nginx, update Laravel to trust the proxy:

### Update TrustProxies Middleware

Already configured, but verify `app/Http/Middleware/TrustProxies.php`:

```php
protected $proxies = '*';  // Trust all proxies
```

This is already set, so it should work.

### Update .env (if needed)

```bash
cd /var/www/wayak-prod

# APP_URL should be the public URL (without port)
sed -i 's|^APP_URL=.*|APP_URL=https://wayak.duckdns.org|' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=https://wayak.duckdns.org|' .env

# Clear Laravel caches
docker exec wayak_app php artisan config:clear
docker exec wayak_app php artisan cache:clear
```

---

## Step 6: SSL/HTTPS Setup (Optional)

If you want HTTPS through CloudPanel's Nginx:

### Option A: Use CloudPanel's SSL Manager

1. Login to CloudPanel
2. Go to Sites → wayak.duckdns.org → SSL
3. Enable Let's Encrypt SSL
4. CloudPanel will automatically configure SSL

### Option B: Manual SSL Setup

```bash
# Get SSL certificate (if not already done)
sudo certbot certonly --nginx -d wayak.duckdns.org

# Update nginx config to use SSL
sudo nano /etc/nginx/sites-available/wayak.duckdns.org
# Uncomment and configure the HTTPS server block
```

---

## Step 7: Test Everything

```bash
# 1. Test CloudPanel Nginx config
sudo nginx -t

# 2. Check CloudPanel Nginx is running
sudo systemctl status nginx

# 3. Check Docker container is running
docker ps | grep wayak_nginx

# 4. Test connection from server
curl -I http://127.0.0.1:8111
curl -I http://wayak.duckdns.org

# 5. Test from browser
# Open: http://wayak.duckdns.org
# Should work without port! ✅
```

---

## Benefits of This Approach

✅ **CloudPanel continues working** - No need to stop its Nginx  
✅ **Port 80 available** - CloudPanel Nginx handles it  
✅ **Multiple sites** - Can add more domains easily  
✅ **SSL management** - CloudPanel can manage SSL certificates  
✅ **Easy maintenance** - All sites managed in one place  
✅ **Better logging** - Centralized access logs  

---

## Troubleshooting

### Problem: 502 Bad Gateway

```bash
# Check Docker container is running
docker ps | grep wayak_nginx

# Check port 8111 is accessible
curl http://127.0.0.1:8111

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log
```

### Problem: CloudPanel Nginx won't start

```bash
# Check for syntax errors
sudo nginx -t

# Check if port 80 is already in use
sudo lsof -i :80

# Check Nginx status
sudo systemctl status nginx
```

### Problem: Domain not resolving

```bash
# Check DNS
nslookup wayak.duckdns.org

# Check Nginx config is enabled
ls -la /etc/nginx/sites-enabled/ | grep wayak

# Check Nginx is listening
sudo netstat -tulpn | grep :80
```

---

## Complete Setup Script

```bash
#!/bin/bash

# 1. Update docker-compose.prod.yml
cd /var/www/wayak-prod
sed -i 's/"${NGINX_PORT:-80}:80"/"8111:80"/' docker-compose.prod.yml

# 2. Restart Docker containers
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d

# 3. Create CloudPanel Nginx config
sudo tee /etc/nginx/sites-available/wayak.duckdns.org > /dev/null << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name wayak.duckdns.org;

    location / {
        proxy_pass http://127.0.0.1:8111;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
    }
}
EOF

# 4. Enable site
sudo ln -sf /etc/nginx/sites-available/wayak.duckdns.org /etc/nginx/sites-enabled/

# 5. Start CloudPanel Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# 6. Test and reload
sudo nginx -t && sudo systemctl reload nginx

# 7. Update .env
sed -i 's|^APP_URL=.*|APP_URL=http://wayak.duckdns.org|' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://wayak.duckdns.org|' .env

# 8. Clear Laravel caches
docker exec wayak_app php artisan config:clear
docker exec wayak_app php artisan cache:clear

echo "✅ Setup complete! Test: http://wayak.duckdns.org"
```

---

## Summary

1. ✅ Change Docker port to 8111
2. ✅ Start CloudPanel Nginx
3. ✅ Create reverse proxy config
4. ✅ Enable site
5. ✅ Test

**Result:** Both CloudPanel and your app work on port 80! 🎉

---

**Date:** December 16, 2025  
**Status:** ✅ Ready to implement

