#!/usr/bin/env bash
set -euo pipefail

# 安装/卸载 macOS launchd 定时任务：定时运行 scripts/laravel_error_watch.php，
# 检测 storage/logs/laravel.log 的新 ERROR，Bark 通知并（可选）自动修复。
#
# Usage:
#   scripts/install_error_watch_launchd.sh install [--interval 300] [--repair 1] [--env-file FILE]
#   scripts/install_error_watch_launchd.sh uninstall
#   scripts/install_error_watch_launchd.sh status
#   scripts/install_error_watch_launchd.sh run        # 立刻手动跑一次
#
# 注意：用 launchd 而不是 cron，因为 macOS 上 cron 受 Full Disk Access / TCC 限制，
# 经常读不到项目目录里的日志。

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LABEL="com.montagegtd.laravel-error-watch"
PLIST_PATH="${HOME}/Library/LaunchAgents/${LABEL}.plist"
WATCHER="${ROOT_DIR}/scripts/laravel_error_watch.php"
LOG_DIR="${ROOT_DIR}/storage/logs"
OUT_LOG="${LOG_DIR}/error-watch-launchd.log"
ERR_LOG="${LOG_DIR}/error-watch-launchd.err.log"
PHP_BIN="$(command -v php || true)"

INTERVAL="300"
REPAIR="1"
ENV_FILE=""

usage() {
  cat <<EOF
Usage:
  scripts/install_error_watch_launchd.sh <command> [options]

Commands:
  install      安装并启动 launchd 定时任务
  uninstall    停止并删除定时任务
  status       查看任务与最近日志
  run          立即手动运行一次看门狗

Options (install):
  --interval N     运行间隔秒数，默认 300（5 分钟）
  --repair 0|1     是否自动调用 opencode 修复，默认 1
  --env-file FILE  可选 env 文件，逐行 KEY=VALUE 注入 launchd 环境
                   （例如放 OPENCODE_GO_API_KEY / BAI_API_KEY）

Paths:
  plist: ${PLIST_PATH}
  log:   ${OUT_LOG}
  err:   ${ERR_LOG}
EOF
}

parse_args() {
  COMMAND="${1:-}"
  if [[ -z "${COMMAND}" ]]; then
    usage
    exit 1
  fi
  shift || true
  while [[ $# -gt 0 ]]; do
    case "$1" in
      --interval)
        INTERVAL="${2:-}"; shift 2 ;;
      --repair)
        REPAIR="${2:-}"; shift 2 ;;
      --env-file)
        ENV_FILE="${2:-}"; shift 2 ;;
      -h|--help)
        usage; exit 0 ;;
      *)
        echo "未知参数: $1" >&2; usage >&2; exit 1 ;;
    esac
  done
}

xml_escape() {
  printf '%s' "$1" | sed -e 's/&/\&amp;/g' -e 's/</\&lt;/g' -e 's/>/\&gt;/g' -e 's/"/\&quot;/g'
}

build_env_entries() {
  local path_value
  path_value="${HOME}/bin:/opt/local/bin:/usr/local/bin:/opt/homebrew/bin:${HOME}/.nvm/versions/node/v22.22.0/bin:/usr/bin:/bin:/usr/sbin:/sbin"
  cat <<EOF
    <key>PATH</key>
    <string>$(xml_escape "${path_value}")</string>
    <key>HOME</key>
    <string>$(xml_escape "${HOME}")</string>
    <key>WATCH_REPAIR</key>
    <string>$(xml_escape "${REPAIR}")</string>
    <key>WATCH_OPENCODE_BIN</key>
    <string>$(xml_escape "${HOME}/bin/opencode")</string>
EOF
  if [[ -n "${ENV_FILE}" ]]; then
    if [[ ! -f "${ENV_FILE}" ]]; then
      echo "env 文件不存在: ${ENV_FILE}" >&2
      exit 1
    fi
    while IFS= read -r line || [[ -n "${line}" ]]; do
      line="${line%$'\r'}"
      [[ -z "${line}" || "${line:0:1}" == "#" ]] && continue
      [[ "${line}" != *=* ]] && continue
      local key="${line%%=*}"
      local val="${line#*=}"
      key="$(printf '%s' "${key}" | tr -d '[:space:]')"
      val="${val%\"}"; val="${val#\"}"
      [[ -z "${key}" ]] && continue
      printf '    <key>%s</key>\n    <string>%s</string>\n' "$(xml_escape "${key}")" "$(xml_escape "${val}")"
    done < "${ENV_FILE}"
  fi
}

write_plist() {
  mkdir -p "${LOG_DIR}" "$(dirname "${PLIST_PATH}")"
  local env_entries
  env_entries="$(build_env_entries)"
  cat > "${PLIST_PATH}" <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
  <key>Label</key>
  <string>${LABEL}</string>
  <key>ProgramArguments</key>
  <array>
    <string>${PHP_BIN}</string>
    <string>${WATCHER}</string>
  </array>
  <key>WorkingDirectory</key>
  <string>${ROOT_DIR}</string>
  <key>StartInterval</key>
  <integer>${INTERVAL}</integer>
  <key>RunAtLoad</key>
  <false/>
  <key>ProcessType</key>
  <string>Background</string>
  <key>StandardOutPath</key>
  <string>${OUT_LOG}</string>
  <key>StandardErrorPath</key>
  <string>${ERR_LOG}</string>
  <key>EnvironmentVariables</key>
  <dict>
${env_entries}
  </dict>
</dict>
</plist>
EOF
}

load_plist() {
  launchctl bootout "gui/${UID}/${LABEL}" >/dev/null 2>&1 || true
  launchctl bootout "gui/${UID}" "${PLIST_PATH}" >/dev/null 2>&1 || true
  if launchctl bootstrap "gui/${UID}" "${PLIST_PATH}" 2>/dev/null; then
    launchctl enable "gui/${UID}/${LABEL}" >/dev/null 2>&1 || true
  else
    launchctl load -w "${PLIST_PATH}"
  fi
}

unload_plist() {
  launchctl bootout "gui/${UID}/${LABEL}" >/dev/null 2>&1 && return 0
  launchctl unload -w "${PLIST_PATH}" >/dev/null 2>&1 && return 0
  return 0
}

main() {
  parse_args "$@"

  if [[ -z "${PHP_BIN}" ]]; then
    echo "未找到 php，请先安装 PHP CLI。" >&2
    exit 1
  fi

  case "${COMMAND}" in
    install)
      [[ "${INTERVAL}" =~ ^[0-9]+$ ]] || { echo "--interval 必须是整数秒" >&2; exit 1; }
      [[ "${REPAIR}" == "0" || "${REPAIR}" == "1" ]] || { echo "--repair 只能是 0 或 1" >&2; exit 1; }
      write_plist
      plutil -lint "${PLIST_PATH}" >/dev/null
      load_plist
      echo "已安装: ${LABEL}"
      echo "间隔:   ${INTERVAL}s"
      echo "自动修复: ${REPAIR}"
      echo "plist:  ${PLIST_PATH}"
      echo "日志:   ${OUT_LOG}"
      ;;
    uninstall)
      unload_plist
      rm -f "${PLIST_PATH}"
      echo "已卸载: ${LABEL}"
      ;;
    status)
      echo "plist: ${PLIST_PATH}"
      if [[ -f "${PLIST_PATH}" ]]; then
        echo "状态: 已安装"
        launchctl print "gui/${UID}/${LABEL}" 2>/dev/null | sed -n '1,20p' || echo "（launchctl 未报告该任务，可能未加载）"
      else
        echo "状态: 未安装"
      fi
      echo "--- 最近看门狗日志 (${OUT_LOG}) ---"
      [[ -f "${OUT_LOG}" ]] && tail -n 20 "${OUT_LOG}" || echo "(暂无)"
      echo "--- 最近错误日志 (${ERR_LOG}) ---"
      [[ -f "${ERR_LOG}" ]] && tail -n 20 "${ERR_LOG}" || echo "(暂无)"
      ;;
    run)
      php "${WATCHER}" --verbose
      ;;
    *)
      usage; exit 1 ;;
  esac
}

main "$@"
