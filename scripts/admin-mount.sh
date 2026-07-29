#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ADMIN_DIR="$PROJECT_DIR/admin"
ENV_FILE="$ADMIN_DIR/.env"
ENV_BACKUP="$ADMIN_DIR/.env.backup"

echo "==> Building admin Docker image..."
docker build -t altobul-admin:latest -f "$ADMIN_DIR/Dockerfile" "$ADMIN_DIR"

echo "==> Creating Docker network 'altobul-net'..."
docker network create altobul-net 2>/dev/null || echo "    Network already exists"

if docker ps -a --format '{{.Names}}' | grep -q "^altobul-admin$"; then
    echo "    Removing existing container 'altobul-admin'..."
    docker rm -f altobul-admin >/dev/null
fi

echo "==> Preparing .env for Docker..."
if [ ! -f "$ENV_FILE" ]; then
    cp "$ADMIN_DIR/.env.example" "$ENV_FILE"
    echo "    Created .env from .env.example"
fi

cp "$ENV_FILE" "$ENV_BACKUP"
echo "    Backed up .env to .env.backup"

sed \
    -e 's|^ADMIN_API_BASE_URL=.*|ADMIN_API_BASE_URL=http://altobul-backend:8000|' \
    -e 's|^DB_CONNECTION=.*|DB_CONNECTION=sqlite|' \
    -e 's|^DB_DATABASE=.*|DB_DATABASE=database/database.sqlite|' \
    -e '/^DB_HOST=/d' \
    -e '/^DB_PORT=/d' \
    -e '/^DB_USERNAME=/d' \
    -e '/^DB_PASSWORD=/d' \
    -e 's|^APP_URL=.*|APP_URL=http://localhost:8001|' \
    "$ENV_BACKUP" > "$ENV_FILE"

echo "==> Creating admin container..."
docker create \
    --name altobul-admin \
    --network altobul-net \
    --restart unless-stopped \
    -p 8001:8000 \
    -v "$ADMIN_DIR:/var/www/html" \
    -w /var/www/html \
    altobul-admin:latest \
    php artisan serve --host=0.0.0.0 --port=8000

echo ""
echo "Container created!"
echo "  - altobul-admin -> http://localhost:8001"
echo ""
echo "Run './scripts/admin-on.sh' to start."
