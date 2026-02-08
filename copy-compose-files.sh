#!/bin/bash
# Copy docker-compose files to new server

NEW_SERVER="109.176.199.107"
PROD_DIR="/var/www/wayak-prod"
DEV_DIR="/var/www/wayak-dev"

echo "📦 Copying docker-compose files to new server..."
echo ""

# Method 1: Direct scp (if SSH key is set up)
echo "Method 1: Using scp..."
scp docker-compose.prod.yml docker-compose.dev.yml root@$NEW_SERVER:$PROD_DIR/ && {
    echo "✅ Files copied successfully to $PROD_DIR"
    
    # Also copy to dev directory if it exists
    ssh root@$NEW_SERVER "mkdir -p $DEV_DIR && cp $PROD_DIR/docker-compose.dev.yml $DEV_DIR/" && {
        echo "✅ Also copied docker-compose.dev.yml to $DEV_DIR"
    } || echo "⚠️  Could not copy to dev directory"
    
    exit 0
}

echo ""
echo "❌ scp failed. Trying alternative methods..."
echo ""
echo "📋 Manual copy instructions:"
echo "=============================="
echo ""
echo "Option 1: From your local machine, run:"
echo "  scp docker-compose.prod.yml docker-compose.dev.yml root@$NEW_SERVER:/var/www/wayak-prod/"
echo ""
echo "Option 2: Copy content manually:"
echo "  1. Display content:"
echo "     cat docker-compose.prod.yml"
echo "     cat docker-compose.dev.yml"
echo ""
echo "  2. On server, create files:"
echo "     ssh root@$NEW_SERVER"
echo "     cd /var/www/wayak-prod"
echo "     nano docker-compose.prod.yml  # Paste content"
echo "     nano docker-compose.dev.yml   # Paste content"
echo ""
echo "Option 3: Use rsync (if available):"
echo "  rsync -avz docker-compose.prod.yml docker-compose.dev.yml root@$NEW_SERVER:/var/www/wayak-prod/"

