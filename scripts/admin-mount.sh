#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ADMIN_DIR="$PROJECT_DIR/admin"
ENV_FILE="$ADMIN_DIR/.env"

echo "==> Building admin Docker image..."
docker build -t altobul-admin:latest -f "$ADMIN_DIR/Dockerfile" "$ADMIN_DIR"

echo "==> Creating Docker network 'altobul_admin_net'..."
docker network create altobul_admin_net 2>/dev/null || echo "    Network already exists"

if docker ps -a --format '{{.Names}}' | grep -q "^altobul-admin$"; then
    echo "    Removing existing container 'altobul-admin'..."
    docker rm -f altobul-admin >/dev/null
fi

echo "==> Preparing .env for fresh install..."
if [ ! -f "$ENV_FILE" ] || [ ! -s "$ENV_FILE" ]; then
    cp "$ADMIN_DIR/.env.example" "$ENV_FILE"
    echo "    Created .env from .env.example"
fi

# Use PostgreSQL via Docker network (connects to backend-postgres-1 container)
sed -i \
    -e 's|^ADMIN_API_BASE_URL=.*|ADMIN_API_BASE_URL=http://backend-postgres-1:8000|' \
    -e 's|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|' \
    -e 's|^DB_HOST=.*|DB_HOST=backend-postgres-1|' \
    -e 's|^DB_PORT=.*|DB_PORT=5432|' \
    -e 's|^DB_DATABASE=.*|DB_DATABASE=altobul_admin|' \
    -e 's|^DB_USERNAME=.*|DB_USERNAME=altobul|' \
    -e 's|^DB_PASSWORD=.*|DB_PASSWORD=altobul_secret|' \
    -e 's|^APP_URL=.*|APP_URL=http://localhost:8001|' \
    -e '/^ADMIN_API_KEY=/d' \
    "$ENV_FILE"

# Add ADMIN_API_KEY if not present
if ! grep -q '^ADMIN_API_KEY=' "$ENV_FILE"; then
    echo "ADMIN_API_KEY=" >> "$ENV_FILE"
fi

echo "==> Creating admin container..."
docker create \
    --name altobul-admin \
    --network altobul_admin_net \
    --restart unless-stopped \
    -p 8001:8000 \
    -v "$ADMIN_DIR:/var/www/html" \
    -w /var/www/html \
    altobul-admin:latest

echo ""
echo "Container created!"
echo "  - altobul-admin -> http://localhost:8001"
echo ""
echo "Run './scripts/admin-on.sh' to start and run migrations."
echo "Then visit http://localhost:8001/install to configure backend URL and API key."