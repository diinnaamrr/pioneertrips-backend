#!/bin/bash

# Quick fix script for dev nginx static files
# Run this on the server: /var/www/wayak-dev

echo "🔧 Fixing Nginx static files configuration for dev..."

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
echo "🔄 Restarting nginx container..."
docker restart wayak_nginx_dev

echo ""
echo "✅ Done! Static files should now work correctly."
echo "📝 Test by accessing: http://your-domain/css/app.css (or any static file)"

