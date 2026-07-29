#!/bin/bash
set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

start_container() {
  local name="$1"
  if docker inspect "$name" >/dev/null 2>&1; then
    echo "==> Starting $name..."
    docker start "$name" || echo "    [ERROR] Failed to start $name"
  else
    echo "    [SKIP] Container '$name' does not exist. Run './scripts/backend-mount.sh' first."
  fi
}

start_container altobul-postgres
start_container altobul-media
start_container altobul-backend

echo ""
echo "==> Waiting for PostgreSQL to accept connections..."
for i in $(seq 1 30); do
  if docker exec altobul-postgres pg_isready -U altobul >/dev/null 2>&1; then
    if docker exec altobul-backend php -r "
      try {
        new PDO('pgsql:host=altobul-postgres;port=5432;dbname=altobul','altobul','altobul_secret');
        echo 'ok';
      } catch (Exception \$e) { echo 'no'; }
    " 2>/dev/null | grep -q ok; then
      echo "    PostgreSQL ready after ${i}s"
      break
    fi
  fi
  if [ "$i" -eq 30 ]; then
    echo "    [ERROR] PostgreSQL not ready after 30s"
    exit 1
  fi
  sleep 1
done

echo ""
echo "==> Running Laravel setup..."
docker exec altobul-backend php artisan key:generate --force 2>/dev/null || true
echo ""
echo "==> Running migrations..."
docker exec altobul-backend php artisan migrate --force

echo ""
echo "==> Building frontend assets..."
docker exec altobul-backend npm install --silent
docker exec altobul-backend npm run build 2>&1 | tail -5

OUTPUT_FILE="$SCRIPT_DIR/backend-on.md"

cat > "$OUTPUT_FILE" <<-EOF
# Backend Servers

## Backend (Laravel)
- URL: http://localhost:8000

## PostgreSQL
- Host: altobul-postgres
- Port: 5432
- Database: altobul
- Username: altobul
- Password: altobul_secret

## MinIO (Media)
- URL del servidor: http://altobul-media:9000
- Console: http://altobul-media:9001
- Access Key: altobul
- Secret Key: altobul_secret
- Bucket: altobul
- Región: us-east-1
EOF

echo ""
echo "Ready! http://localhost:8000"
echo "Details: $OUTPUT_FILE"
