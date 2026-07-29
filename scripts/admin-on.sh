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

echo "==> Connecting to backend_default network (if not already)..."
docker network connect backend_default altobul-admin 2>/dev/null || true

echo "==> Connecting to altobul-net network (for backend API access)..."
docker network connect altobul-net altobul-admin 2>/dev/null || true

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

# Update .env with correct PostgreSQL settings (inside container, connects to backend-postgres-1)
docker exec altobul-admin sh -c "
    cd /var/www/html &&
    sed -i \
        -e 's|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|' \
        -e 's|^DB_DATABASE=.*|DB_DATABASE=altobul_admin|' \
        .env
    # Add PostgreSQL credentials (these may not exist in .env.example)
    for var in 'DB_HOST=backend-postgres-1' 'DB_PORT=5432' 'DB_USERNAME=altobul' 'DB_PASSWORD=altobul'; do
        key=\"\${var%%=*}\"
        if ! grep -q \"^\${key}=\" .env; then
            echo \"\$var\" >> .env
        fi
    done
"

echo "==> Generating APP_KEY if needed..."
docker exec altobul-admin php artisan key:generate --force

echo "==> Fixing storage permissions..."
docker exec altobul-admin sh -c "
    mkdir -p /var/www/html/storage/framework/sessions &&
    mkdir -p /var/www/html/storage/framework/views &&
    mkdir -p /var/www/html/storage/framework/cache/data &&
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
"

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
    echo "ADMIN_API_KEY is empty - first run will show installer at /install"
    echo "Enter your backend URL and ADMIN API Key there."
else
    echo "ADMIN_API_KEY configured - goes directly to /admin/login"
fi
echo "============================================"