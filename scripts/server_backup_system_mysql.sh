#!/usr/bin/env bash
set -euo pipefail

BASE_DIR="${BASE_DIR:-/root/server_bak}"
SYSTEM_DIR="${SYSTEM_DIR:-/home/q/system}"
TASK_ENV="${TASK_ENV:-/home/q/system/task/.env}"
WP_CONFIG="${WP_CONFIG:-/home/q/system/wordpress/wp-config.php}"
RETENTION_DAYS="${RETENTION_DAYS:-7}"
TODAY="$(date +%F)"
DAY_OF_MONTH="$(date +%d)"
LOG_DIR="$BASE_DIR/logs"
LOCK_FILE="${LOCK_FILE:-/var/lock/server_bak.lock}"

mkdir -p \
  "$BASE_DIR/code/daily" "$BASE_DIR/code/monthly" \
  "$BASE_DIR/mysql/daily" "$BASE_DIR/mysql/monthly" \
  "$LOG_DIR"

LOG_FILE="$LOG_DIR/backup-$TODAY.log"
exec >> "$LOG_FILE" 2>&1

log() {
  printf '[%s] %s\n' "$(date '+%F %T')" "$*"
}

fail() {
  log "ERROR: $*"
  exit 1
}

need_cmd() {
  command -v "$1" >/dev/null 2>&1 || fail "$1 not found"
}

need_cmd awk
need_cmd find
need_cmd flock
need_cmd gzip
need_cmd mysql
need_cmd mysqldump
need_cmd sed
need_cmd tar

exec 9>"$LOCK_FILE"
if ! flock -n 9; then
  log "Another backup is already running; exiting."
  exit 0
fi

read_env_value() {
  local file="$1"
  local key="$2"
  awk -F= -v key="$key" '
    $1 == key {
      value = substr($0, length(key) + 2)
      gsub(/^"/, "", value)
      gsub(/"$/, "", value)
      gsub(/^\047/, "", value)
      gsub(/\047$/, "", value)
      print value
      exit
    }
  ' "$file"
}

read_wp_define() {
  local file="$1"
  local key="$2"
  sed -n "s/^[[:space:]]*define([[:space:]]*['\"]$key['\"][[:space:]]*,[[:space:]]*['\"]\\([^'\"]*\\)['\"][[:space:]]*).*$/\\1/p" "$file" | head -n 1
}

mysql_dump_one() {
  local name="$1"
  local host="$2"
  local port="$3"
  local user="$4"
  local pass="$5"
  local db="$6"
  local out_dir="$7"
  local out_file="$out_dir/${name}_${TODAY}.sql.gz"
  local defaults_file

  [ -n "$host" ] || host="127.0.0.1"
  [ -n "$port" ] || port="3306"
  [ -n "$user" ] || fail "empty mysql user for $name"
  [ -n "$db" ] || fail "empty mysql database for $name"

  defaults_file="$(mktemp /tmp/server-bak-mysql.XXXXXX)"
  chmod 600 "$defaults_file"
  {
    printf '[client]\n'
    printf 'user=%s\n' "$user"
    printf 'password=%s\n' "$pass"
    printf 'host=%s\n' "$host"
    printf 'port=%s\n' "$port"
  } > "$defaults_file"

  log "Dumping MySQL database $db to $out_file"
  mysqldump --defaults-extra-file="$defaults_file" \
    --single-transaction --quick --routines --triggers --events \
    --default-character-set=utf8mb4 "$db" | gzip -9 > "$out_file"
  rm -f "$defaults_file"
  chmod 600 "$out_file"
}

if [ "$DAY_OF_MONTH" = "01" ]; then
  CODE_OUT_DIR="$BASE_DIR/code/monthly"
  MYSQL_OUT_DIR="$BASE_DIR/mysql/monthly"
else
  CODE_OUT_DIR="$BASE_DIR/code/daily"
  MYSQL_OUT_DIR="$BASE_DIR/mysql/daily"
fi

log "Backup started"
log "Disk before backup: $(df -h /root | awk 'NR==2 {print $4 " available of " $2 " (" $5 " used)"}')"

[ -d "$SYSTEM_DIR" ] || fail "$SYSTEM_DIR not found"
CODE_FILE="$CODE_OUT_DIR/system_${TODAY}.tar.gz"
log "Archiving $SYSTEM_DIR to $CODE_FILE"
tar --one-file-system --warning=no-file-changed -czf "$CODE_FILE" -C /home/q system
chmod 600 "$CODE_FILE"

[ -f "$TASK_ENV" ] || fail "$TASK_ENV not found"
TASK_DB_HOST="$(read_env_value "$TASK_ENV" DB_HOST)"
TASK_DB_PORT="$(read_env_value "$TASK_ENV" DB_PORT)"
TASK_DB_NAME="$(read_env_value "$TASK_ENV" DB_DATABASE)"
TASK_DB_USER="$(read_env_value "$TASK_ENV" DB_USERNAME)"
TASK_DB_PASS="$(read_env_value "$TASK_ENV" DB_PASSWORD)"
mysql_dump_one "task" "$TASK_DB_HOST" "$TASK_DB_PORT" "$TASK_DB_USER" "$TASK_DB_PASS" "$TASK_DB_NAME" "$MYSQL_OUT_DIR"

[ -f "$WP_CONFIG" ] || fail "$WP_CONFIG not found"
WP_DB_HOST_RAW="$(read_wp_define "$WP_CONFIG" DB_HOST)"
WP_DB_HOST="${WP_DB_HOST_RAW%%:*}"
WP_DB_PORT="${WP_DB_HOST_RAW##*:}"
if [ "$WP_DB_PORT" = "$WP_DB_HOST_RAW" ]; then
  WP_DB_PORT="3306"
fi
WP_DB_NAME="$(read_wp_define "$WP_CONFIG" DB_NAME)"
WP_DB_USER="$(read_wp_define "$WP_CONFIG" DB_USER)"
WP_DB_PASS="$(read_wp_define "$WP_CONFIG" DB_PASSWORD)"
mysql_dump_one "wordpress" "$WP_DB_HOST" "$WP_DB_PORT" "$WP_DB_USER" "$WP_DB_PASS" "$WP_DB_NAME" "$MYSQL_OUT_DIR"

log "Applying daily retention: keep $RETENTION_DAYS days; monthly backups are kept"
find "$BASE_DIR/code/daily" -type f -name 'system_*.tar.gz' -mtime +"$((RETENTION_DAYS - 1))" -delete
find "$BASE_DIR/mysql/daily" -type f \( -name 'task_*.sql.gz' -o -name 'wordpress_*.sql.gz' \) -mtime +"$((RETENTION_DAYS - 1))" -delete
find "$LOG_DIR" -type f -name 'backup-*.log' -mtime +30 -delete

log "Disk after backup: $(df -h /root | awk 'NR==2 {print $4 " available of " $2 " (" $5 " used)"}')"
log "Backup completed"
