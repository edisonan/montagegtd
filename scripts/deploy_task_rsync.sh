#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REMOTE_HOST="${REMOTE_HOST:-root@task.congcong.us}"
REMOTE_PATH="${REMOTE_PATH:-/home/q/system/task}"
SSH_KEY="${SSH_KEY:-/Users/ancongcong/.ssh/id_rsa}"
SSH_CMD="ssh -i ${SSH_KEY} -o IdentitiesOnly=yes -o BatchMode=yes -o PreferredAuthentications=publickey -o ConnectTimeout=10 -o ConnectionAttempts=3 -o IPQoS=none -o KexAlgorithms=diffie-hellman-group14-sha1 -o HostKeyAlgorithms=ecdsa-sha2-nistp256 -c aes128-ctr -o ControlMaster=auto -o ControlPath=/tmp/task-gitee-ssh-%r@%h:%p -o ControlPersist=120"

DELETE_FLAG=""
DRY_RUN_FLAG=""
FILES_FROM=""

usage() {
  cat <<'EOF'
Usage:
  scripts/deploy_task_rsync.sh [--delete] [--dry-run] [--files-from FILE]

Environment overrides:
  REMOTE_HOST=root@task.congcong.us
  REMOTE_PATH=/home/q/system/task
  SSH_KEY=/Users/ancongcong/.ssh/id_rsa

Notes:
  Default mode only syncs local files to remote and does not delete extra remote files.
  Use --delete only when you want the remote directory to mirror local tracked files more closely.
  Use --files-from to deploy only the relative paths listed in FILE.
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
    --files-from)
      if [[ $# -lt 2 || ! -f "$2" ]]; then
        echo "--files-from requires an existing file" >&2
        exit 1
      fi
      FILES_FROM="$2"
      shift 2
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
if [[ -n "$FILES_FROM" ]]; then
  echo "Selective mode enabled; paths are read from ${FILES_FROM}."
fi

FILES_FROM_FLAG=()
if [[ -n "$FILES_FROM" ]]; then
  FILES_FROM_FLAG=(--files-from="$FILES_FROM")
fi

$SSH_CMD "$REMOTE_HOST" "mkdir -p '$REMOTE_PATH'"

rsync -azv \
  -e "$SSH_CMD" \
  $DRY_RUN_FLAG \
  $DELETE_FLAG \
  ${FILES_FROM_FLAG[@]+"${FILES_FROM_FLAG[@]}"} \
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
  --exclude='debug_*.html' \
  --exclude='test_*.html' \
  --exclude='final_alignment_test.html' \
  --exclude='public/test.php' \
  --exclude='skills/montagegtd/scripts/__pycache__/' \
  --exclude='*.pyc' \
  "$ROOT_DIR/" \
  "${REMOTE_HOST}:${REMOTE_PATH}/"

echo "Deploy complete."
