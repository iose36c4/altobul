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

# Use PostgreSQL via Docker network (connects to altobul-postgres container)
sed -i \
    -e 's|^ADMIN_API_BASE_URL=.*|ADMIN_API_BASE_URL=http://altobul-backend:8000|' \
    -e 's|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|' \
    -e 's|^DB_DATABASE=.*|DB_DATABASE=altobul_admin|' \
    -e 's|^DB_HOST=.*|DB_HOST=altobul-postgres|' \
    -e 's|^APP_URL=.*|APP_URL=http://localhost:8001|' \
    -e '/^ADMIN_API_KEY=/d' \
    "$ENV_FILE"

# Add PostgreSQL credentials (these don't exist in .env.example, so sed can't replace them)
for var in "DB_HOST=altobul-postgres" "DB_PORT=5432" "DB_USERNAME=altobul" "DB_PASSWORD=altobul_secret"; do
    key="${var%%=*}"
    if ! grep -q "^${key}=" "$ENV_FILE"; then
        echo "$var" >> "$ENV_FILE"
    else
        sed -i "s|^${key}=.*|${var}|" "$ENV_FILE"
    fi
done

# Add ADMIN_API_KEY if not present
if ! grep -q '^ADMIN_API_KEY=' "$ENV_FILE"; then
    echo "ADMIN_API_KEY=" >> "$ENV_FILE"
fi

echo "==> Creating Docker volume for persistent storage..."
docker volume create altobul_admin_data 2>/dev/null || echo "    Volume already exists"

echo "==> Creating admin container..."
docker create \
    --name altobul-admin \
    --network altobul_admin_net \
    --restart unless-stopped \
    -p 8001:8000 \
    -v altobul_admin_data:/var/www/html/storage \
    -v "$ADMIN_DIR:/var/www/html" \
    -w /var/www/html \
    altobul-admin:latest

echo "==> Connecting container to 'backend_default' network (for DB access)..."
docker network connect backend_default altobul-admin 2>/dev/null || echo "    Network 'backend_default' not found, DB may be unreachable"

echo "==> Connecting container to 'altobul-net' network (for backend API access)..."
docker network connect altobul-net altobul-admin 2>/dev/null || echo "    Network 'altobul-net' not found, backend API may be unreachable"

echo ""
echo "Container created!"
echo "  - altobul-admin -> http://localhost:8001"
echo ""
echo "Run './scripts/admin-on.sh' to start and run migrations."
echo "Then visit http://localhost:8001/install to configure backend URL and API key."