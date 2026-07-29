#!/bin/bash
set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

start_container() {
    local name="$1"
    if docker inspect "$name" >/dev/null 2>&1; then
        echo "==> Starting $name..."
        docker start "$name" || echo "    [ERROR] Failed to start $name"
    else
        echo "    [SKIP] Container '$name' does not exist. Run './scripts/admin-mount.sh' first."
    fi
}

start_container altobul-admin

echo ""
echo "==> Installing Composer dependencies..."
docker exec altobul-admin composer install --no-interaction --prefer-dist --optimize-autoloader 2>/dev/null || true

echo ""
echo "==> Running Laravel setup..."
docker exec altobul-admin php artisan key:generate --force 2>/dev/null || true

echo ""
echo "==> Running database migrations..."
docker exec altobul-admin php artisan migrate --force 2>/dev/null || true

echo ""
echo "==> Building frontend assets..."
docker exec altobul-admin npm install --silent 2>/dev/null || true
docker exec altobul-admin npm run build 2>&1 | tail -5

OUTPUT_FILE="$SCRIPT_DIR/admin-on.md"

cat > "$OUTPUT_FILE" <<-EOF
# Admin Server

- URL: http://localhost:8001
- Backend API: http://altobul-backend:8000
EOF

echo ""
echo "Ready! http://localhost:8001"
echo "Details: $OUTPUT_FILE"
