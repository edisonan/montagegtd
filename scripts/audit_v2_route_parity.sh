#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="${1:-.}"
cd "$ROOT_DIR"

ROUTES_FILE="routes/api.php"
if [[ ! -f "$ROUTES_FILE" ]]; then
  echo "[FAIL] routes/api.php not found"
  exit 1
fi

# method:path (path without /api/v2 prefix)
required=(
  "GET:/tasks"
  "GET:/index"
  "POST:/tasks"
  "PUT:/tasks/{task}"
  "DELETE:/tasks/{task}"

  "GET:/pomos"
  "GET:/pomos/today"
  "GET:/pomos/status"
  "POST:/pomos/start"
  "POST:/pomos/discard"
  "POST:/pomos/discard/{pomo}"
  "POST:/pomos/{pomo}"
  "PUT:/pomos/{pomo}"
  "DELETE:/pomos/{pomo}"

  "GET:/notes"
  "GET:/notes/{note}"
  "GET:/notes/{note}/record"
  "POST:/notes"
  "POST:/notes/upload"
  "PUT:/notes/{note}"
  "DELETE:/notes/{note}"
  "POST:/notes/{note}/like"

  "GET:/minds"
  "GET:/minds/{mind}"
  "GET:/minds/{mind}/jsmind"
  "GET:/minds/{mind}/outline"
  "POST:/minds"
  "PUT:/minds/{mind}"
  "DELETE:/minds/{mind}"
  "POST:/minds/{mind}/tags"

  "GET:/goals"
  "GET:/goals/{goal}"
  "POST:/goals"
  "PUT:/goals/{goal}"
  "DELETE:/goals/{goal}"

  "GET:/settings"
  "PUT:/settings/{setting}"
  "POST:/settings/current"
  "POST:/settings/test-kindle"
  "POST:/settings/test-ifttt"
  "GET:/settings/export"
  "GET:/kindles"
  "POST:/kindles/test"

  "GET:/calendar"
  "GET:/calendar/ics/{theme}"
  "GET:/calendar/taskics/{calToken}"

  "GET:/categories"
  "GET:/categories/{category}"
  "POST:/categories"
  "PUT:/categories/{category}"
  "DELETE:/categories/{category}"
  "POST:/categories/sort"

  "GET:/articles"
  "GET:/articles/list"
  "GET:/articles/navinfo"
  "GET:/articles/navcountinfo"
  "GET:/articles/{article}"
  "GET:/articles/{articleSub}/record"
  "GET:/articles/proxyview"
  "POST:/articles/status/{articleSub}"
  "POST:/articles/allstatus"
  "POST:/articles/mark"
  "DELETE:/articles/{articleSub}"

  "GET:/feeds/check-feed-url"
  "POST:/feeds"
  "POST:/feeds/quickstore"
  "PUT:/feeds/{feedSub}"
  "DELETE:/feeds/{feedSub}"
  "POST:/feeds/sort"
  "POST:/feeds/{feedSub}/refresh"
  "POST:/feeds/{feedSub}/toggle-status"
  "POST:/feeds/{feedSub}/clear-articles"
  "POST:/feeds/import-opml"

  "GET:/things"
  "GET:/things/{thing}"
  "POST:/things"
  "PUT:/things/{thing}"
  "DELETE:/things/{thing}"

  "GET:/daily-summaries"
  "GET:/daily-summaries/{dailySummary}"
  "GET:/daily-summaries/by-date"
  "GET:/daily-summaries/tips"
  "POST:/daily-summaries"
  "PUT:/daily-summaries/{dailySummary}"
  "DELETE:/daily-summaries/{dailySummary}"

  "GET:/statistics"
  "GET:/points"
  "GET:/achievements"
  "POST:/achievements/claim"

  "GET:/accounts"
  "GET:/personal-access-tokens"
  "POST:/personal-access-tokens"
  "DELETE:/personal-access-tokens/{id}"
  "GET:/help/about"
  "POST:/help/feedback"

  "GET:/courses"
  "GET:/courses/management"
  "GET:/course-enrollments"
  "GET:/courses/{id}"
  "POST:/courses"
  "PUT:/courses/{id}"
  "DELETE:/courses/{id}"
  "POST:/courses/{id}/join"

  "GET:/courses/{courseId}/items"
  "GET:/courses/{courseId}/items/{id}"
  "GET:/course-items/structure/{courseId}"
  "GET:/course-items/{id}"
  "POST:/courses/{courseId}/items"
  "PUT:/courses/{courseId}/items/{id}"
  "DELETE:/courses/{courseId}/items/{id}"
  "POST:/course-items"
  "PUT:/course-items/{id}"
  "DELETE:/course-items/{id}"

  "GET:/courses/{courseId}/discussions"
  "GET:/courses/{courseId}/discussions/{id}"
  "POST:/courses/{courseId}/discussions"
  "POST:/courses/{courseId}/discussions/{id}/reply"

  "GET:/llm/sessions"
  "GET:/llm/sessions/{id}"
  "POST:/llm/sessions"
  "PUT:/llm/sessions/{id}/title"
  "POST:/llm/sessions/{id}/clear"
  "POST:/llm/sessions/{id}/toggle-pin"
  "DELETE:/llm/sessions/{id}"

  "GET:/llm/agents"
  "GET:/llm/agents/{id}"
  "POST:/llm/agents"
  "PUT:/llm/agents/{id}"
  "DELETE:/llm/agents/{id}"
  "POST:/llm/agents/{id}/toggle-status"
  "POST:/llm/agents/create-draft"
  "PUT:/llm/agents/{id}/draft"
  "POST:/llm/agents/{id}/publish"
  "POST:/llm/agents/{id}/test-chat"

  "GET:/llm/providers"
  "GET:/llm/providers/{id}"
  "POST:/llm/providers"
  "PUT:/llm/providers/{id}"
  "DELETE:/llm/providers/{id}"

  "GET:/llm/models"
  "GET:/llm/models/{id}"
  "POST:/llm/models"
  "PUT:/llm/models/{id}"
  "DELETE:/llm/models/{id}"

  "GET:/llm/credentials"
  "GET:/llm/credentials/{id}"
  "GET:/llm/usage-stats"
  "POST:/llm/credentials"
  "PUT:/llm/credentials/{id}"
  "DELETE:/llm/credentials/{id}"
  "POST:/llm/credentials/{id}/test"
  "POST:/llm/chat"
  "POST:/llm/ask-ai"

  "POST:/wechat/login"
  "GET:/wechat/explorer"
  "GET:/wechat/articles"
  "GET:/wechat/articleview"
  "GET:/wechat/notes"
  "POST:/wechat/notes"
  "POST:/wechat/addNote"
  "POST:/wechat/articles/status"
  "POST:/wechat/articles/status/{articleSub}"

  "POST:/auth/login"
  "POST:/auth/register"
  "POST:/auth/refresh"
  "POST:/auth/password/email"
  "POST:/auth/password/reset"
  "POST:/auth/logout"
  "GET:/auth/me"
  "GET:/auth/verify"
)

missing=0
for item in "${required[@]}"; do
  method="${item%%:*}"
  path="${item#*:}"
  method_lc="$(printf '%s' "$method" | tr '[:upper:]' '[:lower:]')"

  if ! rg -Fq "Route::${method_lc}('${path}'" "$ROUTES_FILE"; then
    echo "[MISS] ${method} /api/v2${path}"
    missing=1
  fi
done

if [[ "$missing" -eq 1 ]]; then
  echo "[FAIL] route parity check found missing v2 endpoints"
  exit 1
fi

echo "[PASS] v2 route parity check passed"
