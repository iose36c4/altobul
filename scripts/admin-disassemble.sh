#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_DIR/admin/.env"
ENV_BACKUP="$PROJECT_DIR/admin/.env.backup"

echo "==> Stopping container..."
docker stop altobul-admin 2>/dev/null || true

echo "==> Removing container..."
docker rm -v altobul-admin 2>/dev/null || true

echo "==> Restoring .env from backup..."
if [ -f "$ENV_BACKUP" ]; then
    mv "$ENV_BACKUP" "$ENV_FILE"
    echo "    .env restored from .env.backup"
else
    echo "    No .env.backup found, skipping"
fi

echo ""
echo "All cleaned up."
echo "Run './scripts/admin-mount.sh' to rebuild."
