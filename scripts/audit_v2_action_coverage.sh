#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="${1:-.}"
cd "$ROOT_DIR"

WEB_ROUTES="routes/web.php"
API_ROUTES="routes/api.php"

if [[ ! -f "$WEB_ROUTES" || ! -f "$API_ROUTES" ]]; then
  echo "[FAIL] routes/web.php or routes/api.php not found"
  exit 1
fi

TMP_WEB_RAW="$(mktemp /tmp/task_web_actions.XXXXXX)"
TMP_V2_RAW="$(mktemp /tmp/task_v2_actions.XXXXXX)"
TMP_WEB_NORM="$(mktemp /tmp/task_web_actions_norm.XXXXXX)"
TMP_V2_NORM="$(mktemp /tmp/task_v2_actions_norm.XXXXXX)"
TMP_MISSING="$(mktemp /tmp/task_missing_actions.XXXXXX)"
TMP_FILTERED="$(mktemp /tmp/task_missing_actions_filtered.XXXXXX)"
trap 'rm -f "$TMP_WEB_RAW" "$TMP_V2_RAW" "$TMP_WEB_NORM" "$TMP_V2_NORM" "$TMP_MISSING" "$TMP_FILTERED"' EXIT

# Extract controller actions from web routes.
awk -F"'" '/Controller@/{for(i=1;i<=NF;i++){if($i ~ /Controller@/){print $i}}}' "$WEB_ROUTES" \
  | sort -u > "$TMP_WEB_RAW"

# Extract v2 controller actions from api routes and normalize class prefix.
awk -F"'" '/Api\\\\V2\\\\.*Controller@/{for(i=1;i<=NF;i++){if($i ~ /Api\\\\V2\\\\.*Controller@/){gsub(/^Api\\\\V2\\\\/,"",$i); print $i}}}' "$API_ROUTES" \
  | sort -u > "$TMP_V2_RAW"

# Normalize known method aliases between web and v2.
sed \
  -e 's/^Api\\\\V2\\\\AuthController@bootstrapSession$/AuthController@bootstrapSession/' \
  -e 's/CourseController@getUserCourses$/CourseController@enrollments/' \
  -e 's/CourseController@joinCourse$/CourseController@join/' \
  -e 's/CourseItemController@showForModal$/CourseItemController@show/' \
  -e 's/PomoController@todayPomos$/PomoController@today/' \
  -e 's/ArticleController@getArticleRecord$/ArticleController@getRecord/' \
  -e 's/FeedController@checkNewFeed$/FeedController@checkFeedUrl/' \
  -e 's/Auth\\LoginController@logout$/AuthController@logout/' \
  "$TMP_WEB_RAW" | sort -u > "$TMP_WEB_NORM"

cp "$TMP_V2_RAW" "$TMP_V2_NORM"

# Web actions that are expected to remain web-only (SSR pages, OAuth callbacks, etc).
web_only_allowlist=(
  "ArticleController@welcome"
  "ArticleController@view"
  "ArticleController@store"
  "DailySummaryController@create"
  "FeedController@index"
  "FeedController@setting"
  "FeedController@explorer"
  "FeedController@search"
  "FeedController@opml"
  "FeedController@weiborss"
  "FeedController@weixinrss"
  "HelpController@feedback"
  "IndexController@index"
  "IndexController@test"
  "LlmAgentController@getAgents"
  "LlmAgentController@showDraftEditor"
  "LlmSessionController@index"
  "MindController@view"
  "MindController@welcome"
  "MindController@ajaxget"
  "MindController@outlineView"
  "MindController@outlineViewv2"
  "MindController@ajaxoutlineget"
  "NoteController@welcome"
  "PersonalAccessTokenController@create"
  "PomoController@welcome"
  "ThirdController@fanfouIndex"
  "ThirdController@fanfouCallback"
  "ThirdController@twitterIndex"
  "ThirdController@twitterCallback"
  "Auth\\LoginController@thirdRedirect"
  "Auth\\LoginController@thirdCallback"
  "AuthController@bootstrapSession"
)

comm -23 "$TMP_WEB_NORM" "$TMP_V2_NORM" > "$TMP_MISSING" || true

if [[ -s "$TMP_MISSING" ]]; then
  cp "$TMP_MISSING" "$TMP_FILTERED"
  for action in "${web_only_allowlist[@]}"; do
    grep -Fxv "$action" "$TMP_FILTERED" > "${TMP_FILTERED}.next" || true
    mv "${TMP_FILTERED}.next" "$TMP_FILTERED"
  done

  if [[ -s "$TMP_FILTERED" ]]; then
    echo "[FAIL] Unmigrated web actions (not in v2 api routes):"
    cat "$TMP_FILTERED"
    echo
    echo "[INFO] Raw diff before allowlist/aliases:"
    cat "$TMP_MISSING"
    exit 1
  fi
fi

echo "[PASS] v2 action coverage check passed (allowlist + aliases applied)."
