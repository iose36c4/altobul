#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_DIR/backend/.env"
ENV_BACKUP="$PROJECT_DIR/backend/.env.backup"

echo "==> Stopping containers..."
docker stop altobul-backend altobul-media altobul-postgres 2>/dev/null || true

echo "==> Removing containers and volumes..."
docker rm -v altobul-backend 2>/dev/null || true
docker rm -v altobul-postgres 2>/dev/null || true
docker rm -v altobul-media 2>/dev/null || true

echo "==> Removing volumes..."
docker volume rm altobul_postgres_data altobul_media_data 2>/dev/null || true

echo "==> Removing network..."
docker network rm altobul-net 2>/dev/null || true

echo "==> Restoring .env from backup..."
if [ -f "$ENV_BACKUP" ]; then
  mv "$ENV_BACKUP" "$ENV_FILE"
  echo "    .env restored from .env.backup"
else
  echo "    No .env.backup found, skipping"
fi

echo ""
echo "All cleaned up."
echo "Run './scripts/backend-mount.sh' to rebuild."
