#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKEND_DIR="$PROJECT_DIR/backend"
ENV_FILE="$BACKEND_DIR/.env"
ENV_BACKUP="$BACKEND_DIR/.env.backup"

echo "==> Building backend Docker image..."
docker build -t altobul-backend:latest -f "$BACKEND_DIR/Dockerfile" "$BACKEND_DIR"

echo "==> Creating Docker network 'altobul-net'..."
docker network create altobul-net 2>/dev/null || echo "    Network already exists"

for name in altobul-postgres altobul-media altobul-backend; do
  if docker ps -a --format '{{.Names}}' | grep -q "^${name}$"; then
    echo "    Removing existing container '$name'..."
    docker rm -f "$name" >/dev/null
  fi
done

echo "==> Preparing .env for Docker..."
if [ -f "$ENV_FILE" ]; then
  cp "$ENV_FILE" "$ENV_BACKUP"
  echo "    Backed up .env to .env.backup"
fi

sed \
  -e 's/^DB_HOST=.*/DB_HOST=altobul-postgres/' \
  -e 's/^REDIS_HOST=.*/REDIS_HOST=/' \
  -e 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' \
  -e 's/^CACHE_STORE=.*/CACHE_STORE=file/' \
  -e 's/^SESSION_DRIVER=.*/SESSION_DRIVER=database/' \
  "$ENV_BACKUP" > "$ENV_FILE"

echo "==> Creating PostgreSQL container..."
docker create \
  --name altobul-postgres \
  --network altobul-net \
  --restart unless-stopped \
  -e POSTGRES_DB=altobul \
  -e POSTGRES_USER=altobul \
  -e POSTGRES_PASSWORD=altobul_secret \
  -v altobul_postgres_data:/var/lib/postgresql/data \
  postgis/postgis:16-3.4

echo "==> Creating MinIO (media) container..."
docker create \
  --name altobul-media \
  --network altobul-net \
  --restart unless-stopped \
  -e MINIO_ROOT_USER=altobul \
  -e MINIO_ROOT_PASSWORD=altobul_secret \
  -v altobul_media_data:/data \
  minio/minio server /data --console-address ":9001"

echo "==> Creating Backend container..."
docker create \
  --name altobul-backend \
  --network altobul-net \
  --restart unless-stopped \
  -p 8000:8000 \
  -v "$BACKEND_DIR:/var/www/html" \
  -w /var/www/html \
  altobul-backend:latest \
  php artisan serve --host=0.0.0.0 --port=8000

echo ""
echo "Containers created!"
echo "  - altobul-backend  -> http://localhost:8000"
echo "  - altobul-postgres -> internal (altobul-postgres:5432)"
echo "  - altobul-media    -> internal (altobul-media:9000)"
echo ""
echo "Run './scripts/backend-on.sh' to start."
