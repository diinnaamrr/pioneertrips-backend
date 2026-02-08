#!/bin/bash

# Quick fix script for 419 Page Expired error in production
# Run this on production server: /var/www/wayak-prod

set -e

echo "🔧 Fixing 419 Page Expired error..."

cd /var/www/wayak-prod || exit 1

# Backup .env
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo "✅ .env backed up"
fi

# Check APP_KEY
echo "🔑 Checking APP_KEY..."
APP_KEY=$(docker exec wayak_app grep "^APP_KEY=" .env 2>/dev/null | cut -d'=' -f2- || echo "")

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "null" ] || [ "$APP_KEY" = "" ]; then
    echo "⚠️  APP_KEY is missing or empty!"
    echo "   This is the main cause of 419 errors!"
    
    # Try to restore from backup
    if ls .env.backup.* 1> /dev/null 2>&1; then
        echo "   Restoring from latest backup..."
        LATEST_BACKUP=$(ls -t .env.backup.* | head -1)
        cp "$LATEST_BACKUP" .env
        docker cp .env wayak_app:/var/www/html/.env 2>/dev/null || true
        echo "✅ Restored from backup"
    else
        echo "   No backup found. Generating new APP_KEY..."
        echo "   ⚠️  WARNING: This will invalidate all existing sessions!"
        docker exec wayak_app php artisan key:generate --force
        echo "✅ New APP_KEY generated"
    fi
else
    echo "✅ APP_KEY exists"
fi

# Clear config cache file directly
echo "🗑️  Deleting config cache..."
docker exec wayak_app rm -f bootstrap/cache/config.php 2>/dev/null || true

# Clear all Laravel caches
echo "🗑️  Clearing all caches..."
docker exec wayak_app php artisan config:clear 2>/dev/null || true
docker exec wayak_app php artisan cache:clear 2>/dev/null || true
docker exec wayak_app php artisan view:clear 2>/dev/null || true
docker exec wayak_app php artisan route:clear 2>/dev/null || true
docker exec wayak_app php artisan optimize:clear 2>/dev/null || true

# Delete compiled views
echo "🗑️  Deleting compiled views..."
docker exec wayak_app find storage/framework/views -name "*.php" -delete 2>/dev/null || true

# Copy .env to container
echo "📋 Copying .env to container..."
if [ -f .env ]; then
    docker cp .env wayak_app:/var/www/html/.env 2>/dev/null || echo "⚠️  Cannot copy .env"
fi

# Restart container
echo "🔄 Restarting container..."
docker restart wayak_app || docker-compose -f docker-compose.prod.yml restart app || true

# Wait for container to restart
sleep 5

# Copy .env again after restart
if [ -f .env ]; then
    docker cp .env wayak_app:/var/www/html/.env 2>/dev/null || echo "⚠️  Cannot copy .env after restart"
fi

# Verify APP_KEY is loaded
echo ""
echo "✅ Verification:"
APP_KEY_CHECK=$(docker exec wayak_app php artisan tinker --execute="echo config('app.key') ? 'SET' : 'MISSING';" 2>/dev/null | tr -d '\r\n' || echo "UNKNOWN")
echo "   APP_KEY: $APP_KEY_CHECK"

echo ""
echo "✅ Fix complete!"
echo ""
echo "📝 IMPORTANT: Now you MUST:"
echo "   1. Clear ALL cookies for the domain in your browser"
echo "   2. Clear browser cache"
echo "   3. Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)"
echo "   4. Try to login again"
echo ""
echo "   If still not working, check:"
echo "   - Browser console for errors"
echo "   - Network tab for CSRF token in headers"
echo "   - Server logs: docker exec wayak_app tail -f storage/logs/laravel.log"

