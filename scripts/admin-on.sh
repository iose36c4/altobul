#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ADMIN_DIR="$PROJECT_DIR/admin"

echo "==> Starting altobul-admin container..."
docker start altobul-admin 2>/dev/null || {
    echo "    Container not found. Run './scripts/admin-mount.sh' first."
    exit 1
}

echo "==> Waiting for container to be ready..."
sleep 3

echo "==> Checking Laravel config..."
docker exec altobul-admin sh -c "
    cd /var/www/html &&
    if [ ! -f .env ] || [ ! -s .env ]; then
        echo 'No .env found, copying from .env.example'
        cp .env.example .env
    fi
"

echo "==> Generating APP_KEY if needed..."
docker exec altobul-admin php artisan key:generate --force

echo "==> Running migrations..."
docker exec altobul-admin php artisan migrate --force

echo "==> Installing deps & building assets..."
docker exec altobul-admin sh -c "
    cd /var/www/html &&
    composer install --no-dev --optimize-autoloader &&
    npm ci && npm run build
" 2>&1 | tail -20

echo ""
echo "============================================"
echo "altobul-admin started!"
echo "URL: http://localhost:8001"
echo ""
if ! grep -q "^ADMIN_API_KEY=" "$ADMIN_DIR/.env" || [ -z "$(grep '^ADMIN_API_KEY=' "$ADMIN_DIR/.env" | cut -d'=' -f2)" ]; then
    echo "⚠️  ADMIN_API_KEY is empty - first run will show installer at /install"
    echo "   Enter your backend URL and ADMIN API Key there."
else
    echo "✓ ADMIN_API_KEY configured - goes directly to /admin/login"
fi
echo "============================================"