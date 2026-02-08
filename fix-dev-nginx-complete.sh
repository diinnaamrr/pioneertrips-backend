#!/bin/bash

# Complete fix for dev nginx - handles /public/ and /storage/ paths
# Run this on the server: /var/www/wayak-dev

echo "🔧 Fixing Nginx configuration for dev (complete fix)..."

cd /var/www/wayak-dev || exit 1

# Backup current config
if [ -f docker/nginx/default.conf ]; then
    cp docker/nginx/default.conf docker/nginx/default.conf.backup.$(date +%Y%m%d_%H%M%S)
    echo "✅ Backup created"
fi

# Update nginx config
cat > docker/nginx/default.conf << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php;
    
    client_max_body_size 100M;
    
    # Serve files stored under storage/app/public
    location ^~ /storage/ {
        alias /var/www/html/storage/app/public/;
        try_files $uri =404;
        access_log off;
        log_not_found off;
        expires 30d;
    }
    
    # Serve files referenced with asset('public/...') correctly
    # This handles /public/landing-page/, /public/assets/, etc.
    location ^~ /public/ {
        alias /var/www/html/public/;
        try_files $uri $uri/ =404;
        access_log off;
        log_not_found off;
        expires 30d;
    }
    
    # Static files (CSS, JS, Images, Fonts) - Serve directly without PHP processing
    # This MUST come before location / to handle static files correctly
    location ~* \.(?:css|js|png|jpg|jpeg|gif|ico|svg|woff2?|eot|ttf|otf|mp4|webp|json|xml)$ {
        root /var/www/html/public;
        try_files $uri =404;
        access_log off;
        log_not_found off;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

echo "✅ Nginx config updated"
echo ""

# Check and create storage symlink if needed
echo "🔗 Checking storage symlink..."
if docker exec wayak_app_dev test -L /var/www/html/public/storage; then
    echo "✅ Storage symlink exists"
elif docker exec wayak_app_dev test -d /var/www/html/storage/app/public; then
    echo "📝 Creating storage symlink..."
    docker exec wayak_app_dev php artisan storage:link
else
    echo "⚠️  Storage directory not found, skipping symlink"
fi

echo ""
echo "🔄 Restarting nginx container..."
docker restart wayak_nginx_dev

echo ""
echo "✅ Done! All static files should now work:"
echo "   - /public/landing-page/assets/..."
echo "   - /public/assets/..."
echo "   - /storage/app/public/..."
echo ""
echo "📝 Test URLs:"
echo "   https://devwayak.duckdns.org/public/landing-page/assets/css/main.css"
echo "   https://devwayak.duckdns.org/storage/app/public/business/..."

