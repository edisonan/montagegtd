#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   bash scripts/bark_notify.sh 完成
#   bash scripts/bark_notify.sh 完成 接口
# Optional:
#   BARK_URL=https://api.day.app/your_key/

BARK_URL="${BARK_URL:-https://api.day.app/yxye2KjnXirYAVvaRq3S8K/}"
STATUS="${1:-完成}"
DETAIL="${2:-}"

truncate_text() {
  local status="$1"
  local detail="$2"
  local max_length="$3"
  php -r '
    $status = trim($argv[1] ?? "");
    $detail = trim($argv[2] ?? "");
    $maxLength = max(1, (int)($argv[3] ?? 200));
    if ($status === "") {
      $status = "完成";
    }
    $text = $detail === "" ? $status : $status . ":" . $detail;
    echo mb_substr($text, 0, $maxLength, "UTF-8");
  ' "$status" "$detail" "$max_length"
}

TITLE="$(truncate_text "$STATUS" "" 20)"
BODY="$(truncate_text "$STATUS" "$DETAIL" 200)"

curl -fsS -G "${BARK_URL%/}/" \
  --data-urlencode "title=${TITLE}" \
  --data-urlencode "body=${BODY}" \
  --data-urlencode "group=codex" \
  --data-urlencode "icon=https://openai.com/favicon.ico" \
  > /dev/null
