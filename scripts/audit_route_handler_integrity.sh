#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="${1:-.}"
cd "$ROOT_DIR"
php scripts/audit_route_handler_integrity.php
