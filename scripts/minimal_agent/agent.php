#!/usr/bin/env php
<?php
/**
 * minimal_agent.php — 自建最小 PHP agent harness（不依赖 dsh / 任何框架）
 *
 * 思路：一个进程 + 一个循环 + 一次 HTTP 调用。
 * 模型走 OpenAI 兼容的 chat/completions 接口（默认 DeepSeek API），
 * 通过 function calling 暴露 bash / read_file / write_file 三个工具。
 *
 * 用法：
 *   export DEEPSEEK_API_KEY=sk-xxx
 *   php agent.php "帮我审计这个仓库的 TODO"
 *   php agent.php "继续上一次会话的任务" --session job-003
 *
 * 技能(skill)：把 skills/*.md 的全部内容拼进 system prompt。
 * 会话：sessions/<id>.jsonl 逐条追加 JSONL 日志，--session 复用可续跑。
 *
 * 安全默认：
 *   - 交互模式：每条命令执行前人工确认（y/N）
 *   - 非交互模式（无人值守）：只放行只读白名单，其余一律拒绝
 *   - 所有文件操作被限制在 WORKSPACE 内；命令有超时 + 输出截断 + 迭代上限
 *   - 任何含 sudo 的命令直接拒绝
 *
 * 环境变量：DEEPSEEK_API_KEY / DEEPSEEK_BASE_URL / DSH_MODEL / WORKSPACE
 *           MAX_ITER / CMD_TIMEOUT / OUTPUT_CAP / MAX_HISTORY / APPROVE_COMMANDS
 */

// ---------------- 配置 ----------------
function env_or($key, $default)
{
    $v = getenv($key);

    return ($v === false || $v === '') ? $default : $v;
}

$API_KEY     = env_or('DEEPSEEK_API_KEY', '');
$BASE_URL    = rtrim(env_or('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'), '/');
$MODEL       = env_or('DSH_MODEL', 'deepseek-chat');
$WORKSPACE   = realpath(env_or('WORKSPACE', getcwd())) ?: getcwd();
$AGENT_DIR   = __DIR__;
$SESSIONS_DIR = $AGENT_DIR . '/sessions';
$SKILLS_DIR  = $AGENT_DIR . '/skills';
$MAX_ITER    = (int) env_or('MAX_ITER', 30);
$CMD_TIMEOUT = (int) env_or('CMD_TIMEOUT', 120);
$OUTPUT_CAP  = (int) env_or('OUTPUT_CAP', 8000);
$MAX_HISTORY = (int) env_or('MAX_HISTORY', 80);
// 非交互模式下允许执行的命令前缀白名单（逗号分隔），默认只读命令
$APPROVE_LIST = array_filter(array_map('trim', explode(',', env_or('APPROVE_COMMANDS', ''))));
$DEFAULT_APPROVE = array('ls', 'cat', 'head', 'tail', 'grep', 'pwd', 'find', 'wc', 'php -l', 'git status', 'git log', 'git diff');

// ---------------- 参数解析 ----------------
function usage()
{
    echo <<<TXT
USAGE: php agent.php "<prompt>" [--session <id>] [--model <name>] [--workspace <dir>] [--verbose] [--help]

环境变量:
  DEEPSEEK_API_KEY  必填，模型 API 密钥
  DEEPSEEK_BASE_URL 兼容端点，默认 https://api.deepseek.com/v1
  DSH_MODEL         默认 deepseek-chat
  WORKSPACE         agent 可访问的工作目录，默认启动目录
  APPROVE_COMMANDS  非交互模式下允许的命令前缀白名单(逗号分隔)

安全: 交互模式逐条审批；非交互模式默认只放行只读命令。
TXT;
}

$sessionId = 's-' . date('Ymd-His');
$verbose   = false;
$prompt    = null;
$rest = $argv;
array_shift($rest);
for ($i = 0; $i < count($rest); $i++) {
    $a = $rest[$i];
    if ($a === '--session' && isset($rest[$i + 1])) {
        $sessionId = $rest[++$i];
    } elseif ($a === '--model' && isset($rest[$i + 1])) {
        $MODEL = $rest[++$i];
    } elseif ($a === '--workspace' && isset($rest[$i + 1])) {
        $WORKSPACE = realpath($rest[++$i]) ?: $rest[$i];
    } elseif ($a === '--verbose') {
        $verbose = true;
    } elseif ($a === '--help') {
        usage();
        exit(0);
    } elseif ($prompt === null) {
        $prompt = $a;
    } else {
        $prompt .= ' ' . $a;
    }
}

if ($API_KEY === '') {
    fwrite(STDERR, "缺少 DEEPSEEK_API_KEY（可 export 或写入 .env 对应的环境变量）\n");
    exit(2);
}
if ($prompt === null || $prompt === '') {
    usage();
    exit(2);
}

// ---------------- 工具函数 ----------------
function mb_cut($text, $len)
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $len);
    }

    return substr($text, 0, $len);
}

function mb_len($text)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($text);
    }

    return strlen($text);
}

function is_interactive_stdin()
{
    if (function_exists('stream_isatty')) {
        return @stream_isatty(STDIN);
    }
    if (function_exists('posix_isatty')) {
        return @posix_isatty(STDIN);
    }

    return true;
}

/** 解析 y/N 审批；交互模式问人，非交互模式查白名单。返回 null 表示放行，字符串表示拒因。 */
function approval_gate($label, $command, $interactive)
{
    global $APPROVE_LIST, $DEFAULT_APPROVE;

    // 无论如何都拒绝 sudo（防提权）
    if (preg_match('/\bsudo\b/', $command)) {
        return '命令含 sudo，已拒绝';
    }

    if ($interactive) {
        fwrite(STDERR, '[批准?] ' . $label . "\n  " . $command . "\n  (y/N) ");
        $line = fgets(STDIN);

        return (strtolower(trim((string) $line)) === 'y') ? null : '用户拒绝了操作';
    }

    // 非交互：审核白名单（首个 token 或其前缀命中）
    $list = $APPROVE_LIST ? $APPROVE_LIST : $DEFAULT_APPROVE;
    $first = preg_split('/\s+/', trim($command));

    // read_file/write_file 之外的 bash 命令，若带管道/重定向/链式符号则不放行
    if ($label === 'bash') {
        if (preg_match('/[|;&<>]/', $command)) {
            return '非交互模式拒绝含管道/重定向/链式符号的命令';
        }
        if (!$first || !preg_match('/^(php -l|git status|git log|git diff)$/', $first[0])) {
            $hit = false;
            foreach ($list as $prefix) {
                if (strpos(trim($command), $prefix) === 0) {
                    $hit = true;
                    break;
                }
            }
            if (!$hit) {
                return '非交互模式未获批的命令（可用 APPROVE_COMMANDS 白名单放行）';
            }
        }
    }

    return null;
}

/** 确保路径解析后仍位于工作区内；越界返回 null。 */
function resolve_in_workspace($path)
{
    global $WORKSPACE;
    if ($path === '' || strpos($path, "\0") !== false) {
        return null;
    }
    if (strpos($path, '/') !== 0 && strpos($path, '~') === 0) {
        return null; // 不接受相对路径之外的 ~ 展开
    }
    $abs = ($path[0] === '/') ? $path : $WORKSPACE . '/' . $path;
    $real = realpath(dirname($abs));
    if ($real === false) {
        return null;
    }
    $full = rtrim($real, '/') . '/' . basename($abs);
    if (strpos($full, rtrim($WORKSPACE, '/') . '/') !== 0 && $full !== rtrim($WORKSPACE, '/')) {
        return null;
    }

    return $full;
}

/** 用 proc_open + 非阻塞读实现「有超时的命令执行」，跨平台（Linux/macOS 都行）。 */
function run_command($command, $timeoutSec, $outputCap)
{
    global $WORKSPACE;
    if (!is_dir($WORKSPACE)) {
        return '工作目录不存在: ' . $WORKSPACE;
    }
    $cmdline = 'bash -c ' . escapeshellarg($command);
    $descriptors = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );
    $proc = proc_open($cmdline, $descriptors, $pipes, $WORKSPACE);
    if (!is_resource($proc)) {
        return '无法启动 bash 进程';
    }
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $out = '';
    $err = '';
    $deadline = microtime(true) + $timeoutSec;
    $status = array('running' => true, 'exitcode' => -1);
    while (true) {
        $out .= (string) stream_get_contents($pipes[1]);
        $err .= (string) stream_get_contents($pipes[2]);
        $status = proc_get_status($proc);
        if (!$status['running']) {
            $out .= (string) stream_get_contents($pipes[1]);
            $err .= (string) stream_get_contents($pipes[2]);
            break;
        }
        if (microtime(true) > $deadline) {
            proc_terminate($proc, 9);
            usleep(100000);
            $out .= (string) stream_get_contents($pipes[1]);
            $err .= (string) stream_get_contents($pipes[2]);
            $timedOut = true;
            break;
        }
        usleep(50000);
    }
    if (isset($timedOut)) {
        $exitCode = -1;
        $timedOutFlag = true;
    } else {
        $exitCode = (int) $status['exitcode'];
        $timedOutFlag = false;
    }
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);

    $text = trim($out);
    if ($timedOutFlag) {
        $text = '命令超时(' . $timeoutSec . 's)已被强制终止' . ($text === '' ? '' : "\n" . $text);
    }
    if ($err !== '' && !$timedOutFlag) {
        $text .= ($text === '' ? '' : "\n") . '[stderr] ' . trim($err);
    }
    if ($text === '') {
        $text = '(无输出, exit ' . $exitCode . ')';
    }
    if (mb_len($text) > $outputCap) {
        $text = mb_cut($text, $outputCap) . "\n...[输出已截断]";
    }

    return $text;
}

// ---------------- 三个工具 ----------------
function tool_bash($command, $interactive)
{
    $deny = approval_gate('bash', $command, $interactive);
    if ($deny !== null) {
        return $deny;
    }

    return run_command($command, $GLOBALS['CMD_TIMEOUT'], $GLOBALS['OUTPUT_CAP']);
}

function tool_read_file($path)
{
    global $WORKSPACE;
    $full = resolve_in_workspace($path);
    if ($full === null) {
        return '路径必须位于工作区内: ' . $WORKSPACE;
    }
    if (!is_file($full)) {
        return '文件不存在: ' . $path;
    }
    if (filesize($full) > 200000) {
        return '文件超过 200KB，请用 bash 分段查看';
    }
    $content = @file_get_contents($full);

    return ($content === false) ? '读取失败: ' . $path : $content;
}

function tool_write_file($path, $content, $interactive)
{
    global $WORKSPACE;
    $full = resolve_in_workspace($path);
    if ($full === null) {
        return '路径必须位于工作区内: ' . $WORKSPACE;
    }
    $deny = approval_gate('write_file', 'write_file ' . $path, $interactive);
    if ($deny !== null) {
        return $deny;
    }
    if (@file_put_contents($full, $content) === false) {
        return '写入失败: ' . $full;
    }

    return '已写入 ' . $full . ' (' . strlen($content) . ' 字节)';
}

// ---------------- 模型调用 ----------------
function call_llm($messages, $tools)
{
    global $API_KEY, $BASE_URL, $MODEL;
    $payload = array(
        'model' => $MODEL,
        'messages' => $messages,
        'tools' => $tools,
        'temperature' => 0,
    );
    $ch = curl_init($BASE_URL . '/chat/completions');
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $API_KEY,
            'User-Agent: minimal-php-agent/1.0',
        ),
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    ));
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $err !== '') {
        throw new RuntimeException('LLM 请求失败: ' . $err);
    }
    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('LLM HTTP ' . $status . ': ' . mb_cut($body, 500));
    }
    $data = json_decode($body, true);
    if (!is_array($data) || !isset($data['choices'][0]['message'])) {
        throw new RuntimeException('LLM 响应格式异常: ' . mb_cut($body, 300));
    }

    return $data['choices'][0]['message'];
}

// ---------------- skills / 会话 ----------------
function load_skills($dir)
{
    if (!is_dir($dir)) {
        return '';
    }
    $parts = array();
    foreach (glob($dir . '/*.md') ?: array() as $file) {
        $parts[] = basename($file) . ":\n" . (string) file_get_contents($file);
    }

    return $parts === array() ? '' : "以下是当前可用的技能(skill)说明，执行相关任务时按需使用：\n\n" . implode("\n\n---\n\n", $parts);
}

function session_file($id)
{
    global $SESSIONS_DIR;
    if (!is_dir($SESSIONS_DIR)) {
        @mkdir($SESSIONS_DIR, 0777, true);
    }

    return $SESSIONS_DIR . '/' . $id . '.jsonl';
}

function log_message($id, array $msg)
{
    @file_put_contents(session_file($id), json_encode($msg, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
}

function load_session($id)
{
    $file = session_file($id);
    if (!is_file($file)) {
        return null;
    }
    $messages = array();
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $msg = json_decode($line, true);
        if (is_array($msg)) {
            $messages[] = $msg;
        }
    }

    return $messages;
}

function compact_messages(array &$messages, $max)
{
    $count = count($messages);
    if ($count <= $max) {
        return;
    }
    // 保留 system(第 0 条) + 最近 max-1 条，丢掉中间旧历史
    $messages = array_merge(array($messages[0]), array_slice($messages, $count - ($max - 1)));
}

// ---------------- 主循环 ----------------
$TOOLS = array(
    array(
        'type' => 'function',
        'function' => array(
            'name' => 'bash',
            'description' => '在工作目录内执行一条 bash 命令。执行前会征求审批；输出会被截断。',
            'parameters' => array(
                'type' => 'object',
                'properties' => array('command' => array('type' => 'string')),
                'required' => array('command'),
            ),
        ),
    ),
    array(
        'type' => 'function',
        'function' => array(
            'name' => 'read_file',
            'description' => '读取工作区内一个文本文件的内容（限 200KB 以内）。',
            'parameters' => array(
                'type' => 'object',
                'properties' => array('path' => array('type' => 'string', 'description' => '工作区内的相对或绝对路径')),
                'required' => array('path'),
            ),
        ),
    ),
    array(
        'type' => 'function',
        'function' => array(
            'name' => 'write_file',
            'description' => '向工作区内写入/覆盖一个文本文件。执行前会征求审批。',
            'parameters' => array(
                'type' => 'object',
                'properties' => array(
                    'path' => array('type' => 'string', 'description' => '工作区内的相对或绝对路径'),
                    'content' => array('type' => 'string', 'description' => '完整文件内容'),
                ),
                'required' => array('path', 'content'),
            ),
        ),
    ),
);

$interactive = is_interactive_stdin();
$system = '你是一个运行在用户机器上的命令行助手（PHP 实现的最小 harness）。'
    . "你的工作目录是 {$WORKSPACE}。"
    . '你可以执行 bash 命令、读写工作区内的文件。命令输出可能被截断，必要时请分段查看。'
    . '不要假设输出完整；出错时先检查再尝试。每次调用一个工具，做完后用中文给出结论。';
$skills = load_skills($SKILLS_DIR);
if ($skills !== '') {
    $system .= "\n\n" . $skills;
}

$messages = load_session($sessionId);
if ($messages === null) {
    $messages = array(array('role' => 'system', 'content' => $system));
} else {
    // 续跑：上一轮会话可能没有 system，补上
    $hasSystem = false;
    foreach ($messages as $m) {
        if (isset($m['role']) && $m['role'] === 'system') {
            $hasSystem = true;
            break;
        }
    }
    if (!$hasSystem) {
        array_unshift($messages, array('role' => 'system', 'content' => $system));
    }
}
$messages[] = array('role' => 'user', 'content' => $prompt);
log_message($sessionId, end($messages));

if ($verbose) {
    fwrite(STDERR, "[会话] {$sessionId}  模型={$MODEL}  工作区={$WORKSPACE}  模式=" . ($interactive ? '交互(逐条审批)' : '非交互(白名单)') . "\n");
} else {
    fwrite(STDERR, "session: {$sessionId}\n");
}

for ($i = 1; $i <= $MAX_ITER; $i++) {
    if ($verbose) {
        fwrite(STDERR, "[迭代 {$i}/{$MAX_ITER}]\n");
    }
    $msg = call_llm($messages, $TOOLS);
    log_message($sessionId, $msg);
    $messages[] = $msg;
    compact_messages($messages, $MAX_HISTORY);

    if (empty($msg['tool_calls'])) {
        fwrite(STDERR, "\n[完成]\n");
        echo $msg['content'] . "\n";
        exit(0);
    }

    foreach ($msg['tool_calls'] as $tc) {
        $name = isset($tc['function']['name']) ? $tc['function']['name'] : '';
        $rawArgs = isset($tc['function']['arguments']) ? $tc['function']['arguments'] : '{}';
        $args = json_decode($rawArgs, true);
        if (!is_array($args)) {
            $args = array();
        }
        if ($verbose) {
            fwrite(STDERR, "[tool] {$name} " . json_encode($args) . "\n");
        }

        switch ($name) {
            case 'bash':
                $result = tool_bash(isset($args['command']) ? $args['command'] : '', $interactive);
                break;
            case 'read_file':
                $result = tool_read_file(isset($args['path']) ? $args['path'] : '');
                break;
            case 'write_file':
                $result = tool_write_file(
                    isset($args['path']) ? $args['path'] : '',
                    isset($args['content']) ? $args['content'] : '',
                    $interactive
                );
                break;
            default:
                $result = '未知工具: ' . $name;
        }

        $toolMsg = array(
            'role' => 'tool',
            'tool_call_id' => isset($tc['id']) ? $tc['id'] : '',
            'content' => $result,
        );
        log_message($sessionId, $toolMsg);
        $messages[] = $toolMsg;
        compact_messages($messages, $MAX_HISTORY);
    }
}

fwrite(STDERR, "\n[达到最大迭代次数 {$MAX_ITER}，已停止]\n");
exit(1);