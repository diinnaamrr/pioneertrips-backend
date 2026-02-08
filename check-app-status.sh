#!/bin/bash
# Check application status and access info

echo "🔍 Checking application status..."
echo ""

# Check containers
echo "📊 Container status:"
docker ps --filter "name=wayak" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo "🌐 Access Information:"
echo "======================"

# Get server IP
SERVER_IP=$(curl -s ifconfig.me 2>/dev/null || hostname -I | awk '{print $1}' || echo "YOUR_SERVER_IP")

echo ""
echo "Production Environment:"
echo "  - Direct access: http://$SERVER_IP:8111"
echo "  - Domain (if configured): http://wayak.duckdns.org:8111"
echo "  - Or via CloudPanel reverse proxy: https://wayak.duckdns.org"
echo ""

# Check if port 8111 is listening
if netstat -tuln 2>/dev/null | grep -q ":8111 " || ss -tuln 2>/dev/null | grep -q ":8111 "; then
    echo "✅ Port 8111 is listening"
else
    echo "⚠️  Port 8111 is not listening"
fi

echo ""
echo "🔧 Troubleshooting:"
echo "==================="

# Check if vendor directory exists
if [ -d "/var/www/wayak-prod/vendor" ]; then
    echo "✅ vendor directory exists"
else
    echo "❌ vendor directory missing - files not copied correctly"
    echo ""
    echo "Fix: Copy files from image manually:"
    echo "  cd /var/www/wayak-prod"
    echo "  docker run --rm -v \$(pwd):/target ghcr.io/osama21245/wayak-back:latest sh -c 'cp -a /var/www/html/. /target/'"
fi

# Check if public/index.php exists
if [ -f "/var/www/wayak-prod/public/index.php" ]; then
    echo "✅ public/index.php exists"
else
    echo "❌ public/index.php missing"
fi

echo ""
echo "📝 Next steps:"
echo "=============="
echo "1. Test direct access: curl http://localhost:8111"
echo "2. Check container logs: docker logs wayak_app"
echo "3. Check nginx logs: docker logs wayak_nginx"
echo "4. If vendor missing, copy files from image (see above)"

