#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REMOTE_HOST="${REMOTE_HOST:-root@task.congcong.us}"
REMOTE_PATH="${REMOTE_PATH:-/home/q/system/task}"
SSH_KEY="${SSH_KEY:-/Users/ancongcong/.ssh/id_rsa}"
SSH_CMD="ssh -i ${SSH_KEY} -o IdentitiesOnly=yes"

DELETE_FLAG=""
DRY_RUN_FLAG=""

usage() {
  cat <<'EOF'
Usage:
  scripts/deploy_task_rsync.sh [--delete] [--dry-run]

Environment overrides:
  REMOTE_HOST=root@task.congcong.us
  REMOTE_PATH=/home/q/system/task
  SSH_KEY=/Users/ancongcong/.ssh/id_rsa

Notes:
  Default mode only syncs local files to remote and does not delete extra remote files.
  Use --delete only when you want the remote directory to mirror local tracked files more closely.
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --delete)
      DELETE_FLAG="--delete"
      shift
      ;;
    --dry-run)
      DRY_RUN_FLAG="--dry-run"
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      usage >&2
      exit 1
      ;;
  esac
done

echo "Deploying local project to ${REMOTE_HOST}:${REMOTE_PATH}"
if [[ -n "$DRY_RUN_FLAG" ]]; then
  echo "Dry-run mode enabled; no files will be changed."
fi
if [[ -n "$DELETE_FLAG" ]]; then
  echo "Delete mode enabled; remote files not present locally may be removed."
fi

$SSH_CMD "$REMOTE_HOST" "mkdir -p '$REMOTE_PATH'"

rsync -azv \
  -e "$SSH_CMD" \
  $DRY_RUN_FLAG \
  $DELETE_FLAG \
  --exclude='.git/' \
  --exclude='.idea/' \
  --exclude='.env' \
  --exclude='node_modules/' \
  --exclude='vendor/' \
  --exclude='storage/logs/' \
  --exclude='storage/framework/cache/' \
  --exclude='storage/framework/sessions/' \
  --exclude='storage/framework/views/' \
  --exclude='bootstrap/cache/*.php' \
  --exclude='desktop.zip' \
  --exclude='skills/montage-gtd.zip' \
  --exclude='debug_*.html' \
  --exclude='test_*.html' \
  --exclude='final_alignment_test.html' \
  --exclude='public/test.php' \
  --exclude='skills/montage-gtd/scripts/__pycache__/' \
  --exclude='*.pyc' \
  "$ROOT_DIR/" \
  "${REMOTE_HOST}:${REMOTE_PATH}/"

echo "Deploy complete."
