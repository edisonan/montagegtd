#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="${1:-.}"
cd "$ROOT_DIR"

TMP1="$(mktemp /tmp/task_audit_forms.XXXXXX)"
TMP2="$(mktemp /tmp/task_audit_js.XXXXXX)"
trap 'rm -f "$TMP1" "$TMP2"' EXIT

# 1) Form actions that are still pointing to non-v2 business routes.
rg -n "action=\"/(?!api/v2|login|register|password|logout)" resources/views --pcre2 > "$TMP1" || true
rg -n "action=\"\{\{\s*url\('/(?!api/v2|login|register|password|logout)[^']*'\)\s*\}\}" resources/views --pcre2 >> "$TMP1" || true

# 2) JS mutation calls that hit non-v2 routes directly.
rg -n "\$\.post\('/(?!api/v2|auth/|login|register|password|logout)" resources/views public/js --pcre2 -g '!*.backup' > "$TMP2" || true
rg -n "fetch\('/(?!api/v2|auth/|auth/token/bootstrap|login|register|password|logout)" resources/views public/js --pcre2 -g '!*.backup' >> "$TMP2" || true
rg -n "url:\s*['\"]/(?!api/v2|auth/|login|register|password|logout)" resources/views public/js --pcre2 -g '!*.backup' >> "$TMP2" || true

if [[ -s "$TMP1" || -s "$TMP2" ]]; then
  echo "[FAIL] Found potential legacy route usage:"
  if [[ -s "$TMP1" ]]; then
    echo "-- forms --"
    sort -u "$TMP1"
  fi
  if [[ -s "$TMP2" ]]; then
    echo "-- javascript --"
    sort -u "$TMP2"
  fi
  exit 1
fi

echo "[PASS] No obvious legacy form/actions found (excluding auth/password routes)."
