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

build_message() {
  local status="$1"
  local detail="$2"
  php -r '
    $status = trim($argv[1] ?? "");
    $detail = trim($argv[2] ?? "");
    if ($status === "") {
      $status = "完成";
    }
    $text = $detail === "" ? $status : $status . ":" . $detail;
    echo mb_substr($text, 0, 10, "UTF-8");
  ' "$status" "$detail"
}

MESSAGE="$(build_message "$STATUS" "$DETAIL")"

curl -fsS -G "${BARK_URL%/}/" \
  --data-urlencode "title=${MESSAGE}" \
  --data-urlencode "body=${MESSAGE}" \
  --data-urlencode "group=codex" \
  --data-urlencode "icon=https://openai.com/favicon.ico" \
  > /dev/null
