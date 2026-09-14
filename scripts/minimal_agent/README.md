# minimal_agent：自建最小 PHP agent harness

不依赖 dsh / 任何框架的极简 agent 循环，PHP 7.0+ 兼容，只需 `curl` + `json` 扩展。
模型走 OpenAI 兼容的 `chat/completions` 接口（默认 DeepSeek API），通过 function
calling 暴露 `bash` / `read_file` / `write_file` 三个工具。

## 快速开始

```sh
export DEEPSEEK_API_KEY=sk-xxx            # 必填
# export DEEPSEEK_BASE_URL=https://api.deepseek.com/v1   # 默认即此；可换任意兼容端点
# export DSH_MODEL=deepseek-chat

php agent.php "帮我审计一下 scripts/ 目录下有哪些 PHP 脚本" \
  --workspace /path/to/task-gitee
```

- 交互模式（终端里跑）：每条 bash / write_file 操作执行前都会弹 `y/N` 审批。
- 非交互模式（无人值守/脚本里跑）：默认**只放行只读命令**，其余一律拒绝；
  可以加白名单放行，见「安全」。

## 功能

| 能力 | 实现 |
|---|---|
| 工具 | `bash`（有超时+输出截断）、`read_file`（限 200KB）、`write_file` |
| 技能 skills | `skills/*.md` 全部拼进 system prompt，增删即时生效 |
| 会话 | `sessions/<id>.jsonl` 逐条 JSONL 记录；复用 `--session <id>` 续跑（含 Bash 进程状态之外的全部上下文） |
| 上下文压缩 | 消息超过 `MAX_HISTORY`（默认 80）时丢弃中间旧历史，只留 system + 最近 |
| 保护 | 每命令超时 `CMD_TIMEOUT`(120s)、输出截断 `OUTPUT_CAP`(8KB)、迭代上限 `MAX_ITER`(30) |

## 参数与环境变量

```
php agent.php "<prompt>" [--session <id>] [--model <name>] [--workspace <dir>] [--verbose] [--help]
```

| 环境变量 | 默认 | 说明 |
|---|---|---|
| `DEEPSEEK_API_KEY` | — | **必填** |
| `DEEPSEEK_BASE_URL` | `https://api.deepseek.com/v1` | OpenAI 兼容端点 |
| `DSH_MODEL` | `deepseek-chat` | 模型名 |
| `WORKSPACE` | 启动目录 | agent 文件操作被限制在此目录内 |
| `MAX_ITER` / `CMD_TIMEOUT` / `OUTPUT_CAP` / `MAX_HISTORY` | 30 / 120 / 8000 / 80 | 迭代上限 / 命令超时 / 输出截断 / 历史压缩阈值 |
| `APPROVE_COMMANDS` | 只读白名单 | 非交互模式下放行的命令前缀白名单（逗号分隔） |

## 安全（1 核 2G 机器上的使用要点）

自己写的 harness，**安全边界只能自己兜**。这套脚本默认策略：

1. **权限**：进程内所有路径强制校验必须位于 `WORKSPACE` 内（越界直接拒绝）；
   任何含 `sudo` 的命令无条件拒绝。
2. **审批**：交互模式逐条 `y/N`；非交互模式默认只放行只读命令
   （`ls/cat/head/tail/grep/pwd/find/wc/git status|log|diff/php -l` 前缀），
   带管道/重定向/`&&`/`;` 的命令一律拒绝，需要更多权限时显式设
   `APPROVE_COMMANDS`。
3. **失控保护**：命令超时强杀、输出截断、循环迭代上限、历史自动压缩。
4. **部署建议**：
   - 用**专用低权限用户**运行，给它一个只属于它的工作目录，别用 root；
   - 无人值守跑法示例：
     ```sh
     export WORKSPACE=/srv/agent/w1 APPROVE_COMMANDS="ls,cat,php -l"
     nohup php agent.php "处理 xxx 任务" --session nightly-$(date +%F) >> agent.log 2>&1 &
     ```
   - 更硬核的话套一层 `systemd-nspawn` 容器或内核 Landlock（本脚本未内置）。

## 局限（对比 dsh）

没有多子 agent、没有 Web UI、没有 MCP/ACP、没有流式输出、没有页面审批
（审批走终端 stdin）。要这些就直接用 dsh；要「轻、自控、看得懂」就选本脚本。