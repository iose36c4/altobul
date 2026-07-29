#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ADMIN_DIR="$PROJECT_DIR/admin"

echo "==> Stopping container..."
docker stop altobul-admin 2>/dev/null || true

echo "==> Removing container..."
docker rm -v altobul-admin 2>/dev/null || true

echo "==> Removing Docker network..."
docker network rm altobul_admin_net 2>/dev/null || true

echo "==> Dropping PostgreSQL database (altobul_admin)..."
docker exec backend-postgres-1 psql -U altobul -d altobul -c "DROP DATABASE IF EXISTS altobul_admin;" 2>/dev/null || \
docker exec altobul-postgres psql -U altobul -d altobul -c "DROP DATABASE IF EXISTS altobul_admin;" 2>/dev/null || \
echo "    (Postgres container not running or DB already gone)"

echo "==> Cleaning admin .env (reset to .env.example)..."
if [ -f "$ADMIN_DIR/.env.example" ]; then
    cp "$ADMIN_DIR/.env.example" "$ADMIN_DIR/.env"
    echo "    .env reset from .env.example"
else
    echo "    WARNING: .env.example not found"
fi

echo "==> Removing .env.backup..."
rm -f "$ADMIN_DIR/.env.backup"

echo "==> Cleaning storage/framework cache..."
docker run --rm -v "$ADMIN_DIR:/var/www/html" -w /var/www/html php:8.3-cli \
    sh -c "rm -rf storage/framework/cache/* storage/framework/sessions/* storage/framework/views/* bootstrap/cache/* 2>/dev/null || true"

echo ""
echo "All cleaned up - ready for fresh install."
echo "Run './scripts/admin-mount.sh' to rebuild and configure."