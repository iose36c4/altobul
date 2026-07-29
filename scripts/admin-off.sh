#!/bin/bash
set -uo pipefail

if docker ps --format '{{.Names}}' | grep -q "^altobul-admin$"; then
    echo "==> Stopping altobul-admin..."
    docker stop altobul-admin
else
    echo "    [SKIP] altobul-admin is not running"
fi

echo ""
echo "Container stopped. Data preserved."
echo "Run './scripts/admin-on.sh' to start again."
echo "Run './scripts/admin-disassemble.sh' for full cleanup (removes container, DB, cache, .env)."