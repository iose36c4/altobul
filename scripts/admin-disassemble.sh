#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ADMIN_DIR="$PROJECT_DIR/admin"
ENV_FILE="$ADMIN_DIR/.env"
ENV_BACKUP="$ADMIN_DIR/.env.backup"
DB_SQLITE="$ADMIN_DIR/database.sqlite"
DB_DIR="$ADMIN_DIR/database"

echo "==> Stopping container..."
docker stop altobul-admin 2>/dev/null || true

echo "==> Removing container..."
docker rm -v altobul-admin 2>/dev/null || true

echo "==> Removing Docker network (if unused)..."
docker network rm altobul-net 2>/dev/null || true

echo "==> Cleaning admin database..."
# Remove SQLite database file
rm -f "$DB_SQLITE"
# Remove database directory if exists
rm -rf "$DB_DIR"

echo "==> Cleaning admin .env (reset to .env.example)..."
if [ -f "$ADMIN_DIR/.env.example" ]; then
    cp "$ADMIN_DIR/.env.example" "$ENV_FILE"
    echo "    .env reset from .env.example"
else
    echo "    WARNING: .env.example not found"
fi

echo "==> Removing .env.backup..."
rm -f "$ENV_BACKUP"

echo "==> Cleaning storage/framework cache..."
docker run --rm -v "$ADMIN_DIR:/var/www/html" -w /var/www/html php:8.3-cli \
    sh -c "rm -rf storage/framework/cache/* storage/framework/sessions/* storage/framework/views/* bootstrap/cache/* 2>/dev/null || true"

echo ""
echo "All cleaned up - ready for fresh install."
echo "Run './scripts/admin-mount.sh' to rebuild and configure."
