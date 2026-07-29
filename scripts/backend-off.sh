#!/bin/bash
set -uo pipefail

for name in altobul-backend altobul-media altobul-postgres; do
  if docker ps --format '{{.Names}}' | grep -q "^${name}$"; then
    echo "==> Stopping $name..."
    docker stop "$name"
  else
    echo "    [SKIP] $name is not running"
  fi
done

echo ""
echo "All containers stopped."
echo "Run './scripts/backend-on.sh' to start again."
