#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   BASE_URL="https://your.host" TOKEN="pat_xxx" bash scripts/v2_token_smoke.sh
# Optional:
#   RUN_WRITE=1      # enable create/update/delete smoke checks
#   SKIP_LLM=1       # skip llm endpoints check

BASE_URL="${BASE_URL:-}"
TOKEN="${TOKEN:-}"
RUN_WRITE="${RUN_WRITE:-0}"
SKIP_LLM="${SKIP_LLM:-0}"

if [[ -z "$BASE_URL" || -z "$TOKEN" ]]; then
  echo "Usage: BASE_URL=https://host TOKEN=pat_xxx [RUN_WRITE=1] bash scripts/v2_token_smoke.sh"
  exit 1
fi

BASE_URL="${BASE_URL%/}"

red() { printf "\033[31m%s\033[0m\n" "$*"; }
green() { printf "\033[32m%s\033[0m\n" "$*"; }
yellow() { printf "\033[33m%s\033[0m\n" "$*"; }

json_code() {
  php -r '$d=json_decode(stream_get_contents(STDIN), true); echo isset($d["code"])?$d["code"]:"";'
}

json_result_field() {
  local field="$1"
  php -r '$d=json_decode(stream_get_contents(STDIN), true); $r=$d["result"]??[]; echo isset($r["'"$field"'"])?$r["'"$field"'"]:"";'
}

request_json() {
  local method="$1"
  local path="$2"
  local payload="${3:-}"

  if [[ -n "$payload" ]]; then
    curl -sS -X "$method" \
      -H "Authorization: Bearer ${TOKEN}" \
      -H "Accept: application/json" \
      -H "Content-Type: application/json" \
      "${BASE_URL}/api/v2${path}" \
      -d "$payload"
  else
    curl -sS -X "$method" \
      -H "Authorization: Bearer ${TOKEN}" \
      -H "Accept: application/json" \
      "${BASE_URL}/api/v2${path}"
  fi
}

check_code_9999() {
  local name="$1"
  local body="$2"
  local code
  code="$(printf '%s' "$body" | json_code)"
  if [[ "$code" == "9999" ]]; then
    green "[PASS] ${name}"
  else
    red "[FAIL] ${name} -> code=${code}"
    printf '%s\n' "$body"
    exit 1
  fi
}

check_get() {
  local path="$1"
  check_code_9999 "GET ${path}" "$(request_json GET "${path}")"
}

yellow "== v2 token smoke test start =="
echo "BASE_URL=${BASE_URL}"
echo "RUN_WRITE=${RUN_WRITE}"
echo "SKIP_LLM=${SKIP_LLM}"

resp="$(request_json GET '/auth/me')"
check_code_9999 "GET /auth/me" "$resp"
user_id="$(printf '%s' "$resp" | php -r '$d=json_decode(stream_get_contents(STDIN), true); echo $d["result"]["user"]["id"]??"";')"
echo "user_id=${user_id:-unknown}"

check_get '/articles/navinfo'
check_get '/articles/navcountinfo'
check_get '/index'
check_get '/tasks'
check_get '/tasks/priority'
check_get '/tasks/parent-tasks'
check_get '/focus'
check_get '/focus/today'
check_get '/focus/status'
check_get '/notes'
check_get '/journals'
check_get '/daily-summaries'
check_get "/daily-summaries/tips?summary_date=$(date +%F)"
check_get '/minds'
check_get '/plans'
check_get '/categories'
check_get '/feeds/check-feed-url?url=https%3A%2F%2Fwww.v2ex.com%2Findex.xml'
check_get '/calendar'
check_get '/kindles'
check_get '/settings'
check_get '/statistics'
check_get '/points'
check_get '/accounts'
check_get '/thirds'
check_get '/help/about'
check_get '/personal-access-tokens'
check_get '/courses'
check_get '/course-enrollments'
check_get '/wechat/explorer'

if [[ "$SKIP_LLM" != "1" ]]; then
  check_get '/llm/models'
  check_get '/llm/providers'
  check_get '/llm/credentials'
  check_get '/llm/agents'
  check_get '/llm/sessions'
  check_get '/llm/usage-stats'
fi

if [[ "$RUN_WRITE" == "1" ]]; then
  yellow "-- write checks enabled --"
  ts="$(date +%s)"
  cname="smoke_${ts}"

  create_resp="$(request_json POST '/categories' "{\"name\":\"${cname}\"}")"
  check_code_9999 "POST /categories" "$create_resp"
  cid="$(printf '%s' "$create_resp" | php -r '$d=json_decode(stream_get_contents(STDIN),true); echo $d["result"]["id"]??"";')"
  if [[ -z "$cid" ]]; then
    red "[FAIL] category id not found in create response"
    printf '%s\n' "$create_resp"
    exit 1
  fi

  check_code_9999 "PUT /categories/{id}" "$(request_json PUT "/categories/${cid}" "{\"name\":\"${cname}_u\"}")"
  check_code_9999 "DELETE /categories/{id}" "$(request_json DELETE "/categories/${cid}")"
fi

green "== all smoke checks passed =="
