<?php
/**
 * Laravel ERROR 日志看门狗。
 *
 * 每次运行只处理「上次运行之后新增」的 ERROR/CRITICAL/ALERT/EMERGENCY 记录：
 *   1. 通过 Bark 推送告警（复用 scripts/bark_notify.sh）
 *   2. 可选：调用 headless agent（默认 opencode run）分析并尝试最小修复
 *
 * 游标状态保存在 storage/logs/.laravel-error-watch.state.json，因此不会重复
 * 告警历史日志。首次运行默认从「当前文件末尾」开始，只盯新日志；加
 * --since-beginning 才会把已有历史 ERROR 也纳入。
 *
 * 用法：
 *   php scripts/laravel_error_watch.php                 # 检测 + 通知 + 修复
 *   php scripts/laravel_error_watch.php --dry-run       # 只检测/打印，不改代码不发网络
 *   php scripts/laravel_error_watch.php --repair=0      # 只检测 + 通知
 *   php scripts/laravel_error_watch.php --notify=0 --dry-run
 *   php scripts/laravel_error_watch.php --reset         # 重置游标（下次从末尾重新基线）
 *   php scripts/laravel_error_watch.php --help
 *
 * 兼容 PHP 7.0（不使用 PHP 8 语法）。
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$PROJECT_DIR = dirname(__DIR__);

$ERROR_LEVELS = array('ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY');

$defaults = array(
    'log'                  => $PROJECT_DIR . '/storage/logs/laravel.log',
    'state'                => $PROJECT_DIR . '/storage/logs/.laravel-error-watch.state.json',
    'report_dir'           => $PROJECT_DIR . '/storage/logs',
    'bark_script'          => $PROJECT_DIR . '/scripts/bark_notify.sh',
    'notify'               => 1,
    'repair'               => 1,
    'dry_run'              => 0,
    'max_errors'           => 5,
    'cooldown'             => 1800,
    'max_repairs_per_day'  => 6,
    'repair_timeout'       => 900,
    'opencode_bin'         => '',
    'repair_cmd'           => '',
    'since_beginning'      => 0,
    'reset'                => 0,
    'verbose'              => 0,
);

$options = $defaults;

// 环境变量 -> 选项映射（显式命令行参数优先，稍后覆盖）。
$envMap = array(
    'WATCH_LOG'                 => 'log',
    'WATCH_STATE'               => 'state',
    'WATCH_REPORT_DIR'          => 'report_dir',
    'WATCH_BARK_SCRIPT'         => 'bark_script',
    'WATCH_NOTIFY'              => 'notify',
    'WATCH_REPAIR'              => 'repair',
    'WATCH_DRY_RUN'             => 'dry_run',
    'WATCH_MAX_ERRORS'          => 'max_errors',
    'WATCH_COOLDOWN'            => 'cooldown',
    'WATCH_MAX_REPAIRS_PER_DAY' => 'max_repairs_per_day',
    'WATCH_REPAIR_TIMEOUT'      => 'repair_timeout',
    'WATCH_OPENCODE_BIN'        => 'opencode_bin',
    'WATCH_REPAIR_CMD'          => 'repair_cmd',
);
foreach ($envMap as $envName => $optName) {
    $value = getenv($envName);
    if ($value !== false && $value !== '') {
        $options[$optName] = $value;
    }
}

function watch_usage()
{
    echo <<<TXT
Laravel ERROR 日志看门狗

用法:
  php scripts/laravel_error_watch.php [options]

选项:
  --log=PATH              日志路径 (默认 storage/logs/laravel.log)
  --state=PATH            游标状态文件
  --report-dir=PATH       错误报告输出目录
  --notify=0|1            是否 Bark 推送 (默认 1)
  --repair=0|1            是否调用 agent 自动修复 (默认 1)
  --dry-run               只检测打印，不发通知、不改代码
  --max-errors=N          最多交给 agent 的错误条数 (默认 5)
  --cooldown=N            两次自动修复的最小间隔秒数 (默认 1800)
  --max-repairs-per-day=N 每天自动修复次数上限 (默认 6)
  --repair-timeout=N      agent 单次最长运行秒数 (默认 900)
  --opencode-bin=PATH     opencode 可执行文件路径
  --repair-cmd=CMD        完全自定义修复命令，支持 %PROMPT% 与 %REPORT%
  --since-beginning       首次运行从文件开头开始（默认从末尾基线）
  --reset                 重置游标并退出（不清日志）
  --verbose               输出更多信息
  -h, --help              显示帮助

常用环境变量与选项同名: WATCH_LOG/WATCH_STATE/WATCH_NOTIFY/WATCH_REPAIR/
WATCH_MAX_ERRORS/WATCH_COOLDOWN/WATCH_MAX_REPAIRS_PER_DAY/WATCH_REPAIR_TIMEOUT/
WATCH_OPENCODE_BIN/WATCH_REPAIR_CMD/WATCH_BARK_SCRIPT

TXT;
}

// ---- 解析命令行 ----
$argv = $_SERVER['argv'];
for ($i = 1; $i < count($argv); $i++) {
    $arg = $argv[$i];
    if ($arg === '-h' || $arg === '--help') {
        watch_usage();
        exit(0);
    }
    if (strpos($arg, '--') !== 0) {
        fwrite(STDERR, "未知参数: {$arg}\n");
        watch_usage();
        exit(1);
    }
    $body = substr($arg, 2);
    $eq = strpos($body, '=');
    if ($eq === false) {
        $key = $body;
        $val = true;
    } else {
        $key = substr($body, 0, $eq);
        $val = substr($body, $eq + 1);
    }
    $key = str_replace('-', '_', $key);
    if (!array_key_exists($key, $defaults)) {
        fwrite(STDERR, "未知参数: {$arg}\n");
        watch_usage();
        exit(1);
    }
    if ($key === 'since_beginning' || $key === 'reset' || $key === 'verbose' || $key === 'dry_run') {
        $options[$key] = $val === true ? 1 : (int)$val;
    } elseif ($key === 'log' || $key === 'state' || $key === 'report_dir' || $key === 'bark_script'
        || $key === 'opencode_bin' || $key === 'repair_cmd') {
        $options[$key] = (string)$val;
    } else {
        $options[$key] = (int)$val;
    }
}

function wlog($msg, $verboseOnly = false)
{
    global $options;
    if ($verboseOnly && empty($options['verbose'])) {
        return;
    }
    $prefix = '[' . date('Y-m-d H:i:s') . '] ';
    fwrite(STDOUT, $prefix . $msg . "\n");
}

function read_state($path)
{
    if (!is_file($path)) {
        return array();
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return array();
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : array();
}

function write_state($path, array $state)
{
    $state['updated_at'] = date('c');
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $json = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return @file_put_contents($path, $json . "\n") !== false;
}

/**
 * 解析一段日志文本，返回错误记录数组。
 * 每条: ['ts'=>..., 'level'=>..., 'message'=>..., 'raw'=>...]
 */
function parse_error_records($text, array $errorLevels)
{
    $records = array();
    $lines = explode("\n", $text);
    $current = null;

    foreach ($lines as $line) {
        $isHeader = preg_match('/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?)\]\s*(.*)$/', $line, $m);
        if ($isHeader) {
            if ($current !== null) {
                $records[] = $current;
            }
            $body = $m[2];
            $level = 'UNKNOWN';
            if (preg_match('/^([A-Za-z0-9_]+)\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\b/', $body, $lm)) {
                $level = $lm[2];
            } elseif (preg_match('/\b(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\b/', $body, $lm2)) {
                $level = $lm2[1];
            }
            $message = preg_replace('/^\s*[A-Za-z0-9_]+\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\b:?\s*/', '', $body);
            $message = trim((string)$message);
            $current = array(
                'ts'      => $m[1],
                'level'   => $level,
                'message' => $message,
                'raw'     => $line,
            );
        } elseif ($current !== null) {
            $current['raw'] .= "\n" . $line;
        }
    }
    if ($current !== null) {
        $records[] = $current;
    }

    $errors = array();
    foreach ($records as $record) {
        if (in_array($record['level'], $errorLevels, true)) {
            $errors[] = $record;
        }
    }
    return $errors;
}

function truncate_text($text, $max)
{
    if (function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') > $max) {
            return mb_substr($text, 0, $max, 'UTF-8') . '…';
        }
        return $text;
    }
    if (strlen($text) > $max) {
        return substr($text, 0, $max) . '...';
    }
    return $text;
}

/**
 * 运行外部命令，带超时。返回 [exitCode, output, timedOut]。
 */
function run_with_timeout($cmd, $cwd, $timeout, $env = null)
{
    $descriptors = array(
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );
    $pipes = array();
    $process = proc_open($cmd, $descriptors, $pipes, $cwd, $env);
    if (!is_resource($process)) {
        return array(-1, 'proc_open failed', false);
    }
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $output = '';
    $start = time();
    $timedOut = false;
    $open = array(1, 2);

    while (true) {
        $read = array();
        foreach ($open as $idx) {
            if (isset($pipes[$idx]) && is_resource($pipes[$idx])) {
                $read[] = $pipes[$idx];
            }
        }
        if (empty($read)) {
            break;
        }
        $write = null;
        $except = null;
        $ready = @stream_select($read, $write, $except, 1);
        if ($ready > 0) {
            foreach ($read as $stream) {
                $chunk = fread($stream, 8192);
                if ($chunk !== false && $chunk !== '') {
                    $output .= $chunk;
                }
                if (feof($stream)) {
                    foreach ($open as $k => $idx) {
                        if (isset($pipes[$idx]) && $pipes[$idx] === $stream) {
                            unset($open[$k]);
                        }
                    }
                }
            }
        }
        if (time() - $start >= $timeout) {
            $timedOut = true;
            @proc_terminate($process, 9);
            break;
        }
    }

    foreach ($open as $idx) {
        if (isset($pipes[$idx]) && is_resource($pipes[$idx])) {
            $chunk = stream_get_contents($pipes[$idx]);
            if ($chunk !== false) {
                $output .= $chunk;
            }
        }
    }
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    $exitCode = proc_close($process);
    return array($exitCode, $output, $timedOut);
}

function find_opencode_bin($override)
{
    if ($override !== '') {
        return $override;
    }
    $candidates = array();
    $home = getenv('HOME');
    if ($home) {
        $candidates[] = $home . '/bin/opencode';
    }
    $candidates[] = '/opt/local/bin/opencode';
    $candidates[] = '/usr/local/bin/opencode';
    $candidates[] = '/opt/homebrew/bin/opencode';
    foreach ($candidates as $candidate) {
        if (is_file($candidate) && is_executable($candidate)) {
            return $candidate;
        }
    }
    $which = trim((string)@shell_exec('command -v opencode 2>/dev/null'));
    if ($which !== '') {
        return $which;
    }
    return '';
}

function bark_notify($script, $status, $detail, $dryRun, $verbose)
{
    if ($dryRun) {
        wlog("Bark(dry-run): {$status} | {$detail}");
        return true;
    }
    if (!is_file($script)) {
        wlog("Bark 脚本不存在，跳过: {$script}");
        return false;
    }
    $cmd = 'bash ' . escapeshellarg($script) . ' ' . escapeshellarg($status) . ' ' . escapeshellarg($detail);
    $out = array();
    $rc = 0;
    @exec($cmd, $out, $rc);
    if ($verbose) {
        wlog("Bark rc={$rc} " . implode(' ', $out));
    }
    return $rc === 0;
}

// ------------------- 主流程 -------------------

$logPath     = (string)$options['log'];
$statePath   = (string)$options['state'];
$reportDir   = (string)$options['report_dir'];
$dryRun      = !empty($options['dry_run']);
$notify      = !empty($options['notify']);
$repair      = !empty($options['repair']);
$verbose     = !empty($options['verbose']);
$maxErrors   = max(1, (int)$options['max_errors']);
$cooldown    = max(0, (int)$options['cooldown']);
$maxPerDay   = max(0, (int)$options['max_repairs_per_day']);
$repairTimeout = max(30, (int)$options['repair_timeout']);

if (!empty($options['reset'])) {
    $state = read_state($statePath);
    $state['offset'] = 0;
    $state['baseline_done'] = false;
    write_state($statePath, $state);
    wlog("游标已重置（下次运行重新基线）。state={$statePath}");
    exit(0);
}

if (!is_file($logPath)) {
    wlog("日志不存在，跳过: {$logPath}", true);
    exit(0);
}

$size = filesize($logPath);
if ($size === false) {
    wlog("无法读取日志大小: {$logPath}");
    exit(1);
}

$state = read_state($statePath);
$offset = isset($state['offset']) ? (int)$state['offset'] : 0;
$baselineDone = !empty($state['baseline_done']);

// 日志被截断/轮转：游标回退到 0，但为避免把历史全部当新错误，直接重新基线到末尾。
if ($offset > $size) {
    wlog("检测到日志轮转/截断，重新基线到末尾。", true);
    $offset = $size;
}

// 首次运行：默认从末尾开始，只盯新日志。
if (!$baselineDone && empty($options['since_beginning'])) {
    $state['offset'] = $size;
    $state['baseline_done'] = true;
    if (!isset($state['last_repair_ts'])) {
        $state['last_repair_ts'] = 0;
    }
    write_state($statePath, $state);
    wlog("首次运行，基线到文件末尾 offset={$size}，仅监控后续新增 ERROR。");
    exit(0);
}

if ($offset === 0 && $size > 0 && empty($options['since_beginning']) && empty($state['baseline_done'])) {
    $offset = $size;
}

$handle = @fopen($logPath, 'rb');
if ($handle === false) {
    wlog("无法打开日志: {$logPath}");
    exit(1);
}
fseek($handle, $offset);
$chunk = stream_get_contents($handle);
fclose($handle);
if ($chunk === false) {
    $chunk = '';
}

// 只处理完整的行，避免读到写了一半的记录。
$consumed = 0;
$processed = '';
if ($chunk !== '') {
    $lastNewline = strrpos($chunk, "\n");
    if ($lastNewline === false) {
        $processed = '';
    } else {
        $processed = substr($chunk, 0, $lastNewline + 1);
        $consumed = $lastNewline + 1;
    }
}
$newOffset = $offset + $consumed;

$errors = parse_error_records($processed, $ERROR_LEVELS);

$state['offset'] = $newOffset;
$state['baseline_done'] = true;

if (empty($errors)) {
    write_state($statePath, $state);
    wlog("无新增 ERROR。offset={$newOffset}", true);
    exit(0);
}

$count = count($errors);
$first = $errors[0];
$firstMessage = truncate_text(preg_replace('/\s+/', ' ', $first['message']), 120);

wlog("发现 {$count} 条新增 ERROR。首条: {$firstMessage}");

// 生成错误报告文件（供 agent 读取，也用于留档）。
if (!is_dir($reportDir)) {
    @mkdir($reportDir, 0775, true);
}
$reportPath = $reportDir . '/error-watch-report-' . date('Ymd-His') . '.log';
$reportLines = array();
$reportLines[] = 'Laravel 新增 ERROR 报告';
$reportLines[] = '生成时间: ' . date('c');
$reportLines[] = '日志文件: ' . $logPath;
$reportLines[] = '新增条数: ' . $count;
$reportLines[] = str_repeat('=', 60);
$limit = min($count, $maxErrors);
for ($i = 0; $i < $limit; $i++) {
    $reportLines[] = '';
    $reportLines[] = '--- #' . ($i + 1) . ' [' . $errors[$i]['ts'] . '] ' . $errors[$i]['level'] . ' ---';
    $reportLines[] = truncate_text(rtrim($errors[$i]['raw']), 4000);
}
if ($count > $limit) {
    $reportLines[] = '';
    $reportLines[] = '（其余 ' . ($count - $limit) . ' 条已省略，详见原始日志。）';
}
$reportBody = implode("\n", $reportLines);
if (!$dryRun) {
    @file_put_contents($reportPath, $reportBody . "\n");
    wlog("报告已写入: {$reportPath}");
} else {
    wlog("报告(dry-run) 将写入: {$reportPath}", true);
}

// 1) Bark 告警
$alertDetail = $count . ' 条新ERROR; ' . $firstMessage;
if ($notify) {
    bark_notify((string)$options['bark_script'], 'Laravel错误', $alertDetail, $dryRun, $verbose);
}

// 2) 自动修复（带冷却与每日上限）
$canRepair = $repair;
$skipReason = '';
$now = time();
$lastRepair = isset($state['last_repair_ts']) ? (int)$state['last_repair_ts'] : 0;
$today = date('Y-m-d');
if (isset($state['repairs_day']) && $state['repairs_day'] === $today) {
    $repairsToday = isset($state['repairs_count']) ? (int)$state['repairs_count'] : 0;
} else {
    $repairsToday = 0;
}
if ($canRepair && $cooldown > 0 && ($now - $lastRepair) < $cooldown) {
    $canRepair = false;
    $skipReason = '冷却中（剩余 ' . ($cooldown - ($now - $lastRepair)) . 's）';
}
if ($canRepair && $maxPerDay > 0 && $repairsToday >= $maxPerDay) {
    $canRepair = false;
    $skipReason = '已达当日修复上限 ' . $maxPerDay;
}

if (!$repair) {
    wlog("自动修复已关闭 (--repair=0)。", true);
} elseif (!$canRepair) {
    wlog("跳过自动修复: {$skipReason}");
} else {
    $opencodeBin = find_opencode_bin((string)$options['opencode_bin']);
    $prompt = '项目 storage/logs/laravel.log 出现了新的 ERROR（完整报告见附件文件）。'
        . '请阅读报告定位根因；仅当能明确判断是应用代码 bug 时，做最小改动修复；'
        . '不要 git commit、不要部署、不要修改 .env；'
        . '如果无法安全修复或属于环境/历史问题，就不要改代码，直接说明原因。完成后用中文简要总结。';

    $customCmd = (string)$options['repair_cmd'];
    if ($customCmd !== '') {
        $cmd = str_replace(array('%PROMPT%', '%REPORT%'), array(escapeshellarg($prompt), escapeshellarg($reportPath)), $customCmd);
    } elseif ($opencodeBin !== '') {
        $cmd = escapeshellarg($opencodeBin)
            . ' run --title ' . escapeshellarg('laravel-error-auto-fix')
            . ' ' . escapeshellarg($prompt)
            . ' --file ' . escapeshellarg($reportPath);
    } else {
        $cmd = '';
    }

    if ($cmd === '') {
        wlog("未找到 opencode，可执行文件不可用，跳过自动修复。");
        bark_notify((string)$options['bark_script'], 'Laravel错误', '检测到 ' . $count . ' 条ERROR，但未找到 opencode 无法自动修复', $dryRun, $verbose);
    } else {
        wlog("启动自动修复: {$cmd}");
        if ($dryRun) {
            wlog("dry-run：不实际执行修复命令。");
        } else {
            $repairLog = $reportDir . '/error-watch-repair-' . date('Ymd-His') . '.log';
            $env = null; // 继承当前环境
            $result = run_with_timeout($cmd, $PROJECT_DIR, $repairTimeout, $env);
            $exitCode = $result[0];
            $output = $result[1];
            $timedOut = $result[2];
            @file_put_contents($repairLog, $output . "\n");
            $state['last_repair_ts'] = $now;
            $state['repairs_day'] = $today;
            $state['repairs_count'] = $repairsToday + 1;

            if ($timedOut) {
                wlog("自动修复超时（{$repairTimeout}s），已终止。日志: {$repairLog}");
                bark_notify((string)$options['bark_script'], '修复超时', '有 ' . $count . ' 条ERROR，agent 超时未完成', $dryRun, $verbose);
            } elseif ($exitCode === 0) {
                wlog("自动修复进程结束 exit=0。日志: {$repairLog}");
                bark_notify((string)$options['bark_script'], '修复完成', '有 ' . $count . ' 条ERROR，agent 已结束，请查看仓库改动', $dryRun, $verbose);
            } else {
                wlog("自动修复进程结束 exit={$exitCode}。日志: {$repairLog}");
                bark_notify((string)$options['bark_script'], '修复异常', 'agent exit=' . $exitCode . '，详见 ' . basename($repairLog), $dryRun, $verbose);
            }
        }
    }
}

write_state($statePath, $state);
wlog("本次处理完成，游标推进到 offset={$newOffset}。");
exit(0);
