<?php
/**
 * 修正历史手账记录：子任务完成写入手账时，名称应为「父任务-子任务」。
 *
 * 背景：TaskService::updateTaskByType('finish') 早期只写入子任务自身名称，
 * 现已改为写入「父任务名称-子任务名称」（见 app/Services/TaskService.php）。
 * 本脚本扫描 type=2（待办任务）的手账，若对应的是子任务且名称未按规则
 * 拼接，则统一修正为「父任务名称-子任务名称」。
 *
 * 手账表只存 name 字符串、并没有 task_id 关联，因此这里用
 * (user_id + name) 精确匹配子任务；同一用户存在多个同名子任务时，
 * 用完成时间与任务 updated_at 的接近程度来选择父任务。
 *
 * 用法：
 *   php scripts/fix_journal_subtask_names.php            # 预览，不改数据
 *   php scripts/fix_journal_subtask_names.php --apply    # 实际写入
 *   php scripts/fix_journal_subtask_names.php --verbose  # 预览时列出全部待修复项
 *   php scripts/fix_journal_subtask_names.php --sql-out=FILE  # 导出回滚 SQL
 */

function loadEnv($path)
{
    $map = array();
    if (!is_file($path)) {
        return $map;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $eq = strpos($line, '=');
        if ($eq === false) {
            continue;
        }
        $key = trim(substr($line, 0, $eq));
        $val = trim(substr($line, $eq + 1));
        $val = trim($val, "\"'");
        $map[$key] = $val;
    }
    return $map;
}

$apply = in_array('--apply', $argv, true);
$verbose = in_array('--verbose', $argv, true);
$sqlOut = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--sql-out=') === 0) {
        $sqlOut = substr($arg, strlen('--sql-out='));
    }
}

$root = dirname(__DIR__);
$env = loadEnv($root . '/.env');
if (empty($env['DB_DATABASE'])) {
    fwrite(STDERR, "cannot load .env\n");
    exit(1);
}

$host = isset($env['DB_HOST']) ? $env['DB_HOST'] : '127.0.0.1';
$port = isset($env['DB_PORT']) ? $env['DB_PORT'] : '3306';
$user = isset($env['DB_USERNAME']) ? $env['DB_USERNAME'] : 'root';
$pass = isset($env['DB_PASSWORD']) ? $env['DB_PASSWORD'] : '';

$conn = @mysqli_connect($host, $user, $pass, $env['DB_DATABASE'], (int)$port);
if (!$conn) {
    fwrite(STDERR, 'db connect failed: ' . mysqli_connect_error() . "\n");
    exit(1);
}
mysqli_set_charset($conn, 'utf8mb4');

// 1. 载入所有子任务及其父任务名称，按 (user_id, 子任务名) 建索引。
$subtasks = array();
$sql = "SELECT t.id AS task_id, t.user_id, t.name AS sub_name, t.updated_at, p.name AS parent_name
        FROM tasks t
        JOIN tasks p ON p.id = t.parent_task_id
        WHERE t.parent_task_id IS NOT NULL AND t.parent_task_id > 0
          AND t.name IS NOT NULL AND t.name <> ''
          AND p.name IS NOT NULL AND p.name <> ''";
$res = mysqli_query($conn, $sql);
if (!$res) {
    fwrite(STDERR, 'query subtasks failed: ' . mysqli_error($conn) . "\n");
    exit(1);
}
while ($row = mysqli_fetch_assoc($res)) {
    $uid = (int)$row['user_id'];
    $sub = (string)$row['sub_name'];
    $desired = $row['parent_name'] . '-' . $sub;
    $subtasks[$uid][$sub][] = array(
        'task_id' => (int)$row['task_id'],
        'parent_name' => (string)$row['parent_name'],
        'desired' => $desired,
        'updated_at' => $row['updated_at'],
    );
}

// 2. 扫描 type=2 手账，判断是否需要修正。
$targets = array();
$stats = array(
    'scanned' => 0,
    'already_ok' => 0,
    'not_subtask' => 0,
    'ambiguous_skipped' => 0,
    'too_long_skipped' => 0,
    'to_fix' => 0,
);

$sql = "SELECT id, user_id, name, start_time, end_time, created_at
        FROM journals
        WHERE type = 2 AND name IS NOT NULL AND name <> ''";
$res = mysqli_query($conn, $sql);
if (!$res) {
    fwrite(STDERR, 'query journals failed: ' . mysqli_error($conn) . "\n");
    exit(1);
}
while ($row = mysqli_fetch_assoc($res)) {
    $stats['scanned']++;
    $uid = (int)$row['user_id'];
    $name = (string)$row['name'];

    if (empty($subtasks[$uid][$name])) {
        $stats['not_subtask']++;
        continue;
    }

    $list = $subtasks[$uid][$name];

    // 同名子任务可能对应不同父任务，先看期望名称是否唯一。
    $desiredSet = array();
    foreach ($list as $c) {
        $desiredSet[$c['desired']] = true;
    }
    if (count($desiredSet) === 1) {
        $desired = array_keys($desiredSet)[0];
    } else {
        // 多个不同父任务：用完成时间与 updated_at 的接近程度消歧。
        $journalTime = strtotime($row['start_time'] !== null ? $row['start_time'] : $row['created_at']);
        $best = null;
        $bestDiff = null;
        foreach ($list as $c) {
            $candTime = strtotime($c['updated_at']);
            if ($candTime === false || $journalTime === false) {
                continue;
            }
            $diff = abs($candTime - $journalTime);
            if ($bestDiff === null || $diff < $bestDiff) {
                $bestDiff = $diff;
                $best = $c;
            }
        }
        if ($best === null) {
            $stats['ambiguous_skipped']++;
            echo "  SKIP ambiguous id={$row['id']} user={$uid} name={$name}（无法消歧）\n";
            continue;
        }
        $desired = $best['desired'];
    }

    if ($name === $desired) {
        $stats['already_ok']++;
        continue;
    }

    if (mb_strlen($desired, 'UTF-8') > 200) {
        $stats['too_long_skipped']++;
        echo "  SKIP too-long id={$row['id']} user={$uid} 目标名称超过 200 字符\n";
        continue;
    }

    $stats['to_fix']++;
    $targets[] = array(
        'id' => (int)$row['id'],
        'user_id' => $uid,
        'old' => $name,
        'new' => $desired,
    );
}
mysqli_free_result($res);

echo "scanned={$stats['scanned']} to_fix={$stats['to_fix']} already_ok={$stats['already_ok']} "
    . "not_subtask={$stats['not_subtask']} ambiguous_skipped={$stats['ambiguous_skipped']} "
    . "too_long_skipped={$stats['too_long_skipped']}\n";

if ($sqlOut !== null) {
    $fh = fopen($sqlOut, 'w');
    if (!$fh) {
        fwrite(STDERR, "cannot write sql file: {$sqlOut}\n");
        exit(1);
    }
    fwrite($fh, "-- rollback for fix_journal_subtask_names.php (" . date('Y-m-d H:i:s') . ")\n");
    foreach ($targets as $t) {
        $escaped = mysqli_real_escape_string($conn, $t['old']);
        fwrite($fh, "UPDATE journals SET name='{$escaped}' WHERE id={$t['id']};\n");
    }
    fclose($fh);
    echo "rollback SQL written: {$sqlOut} ({$stats['to_fix']} statements)\n";
}

if ($apply) {
    $n = 0;
    foreach ($targets as $t) {
        $stmt = mysqli_prepare($conn, 'UPDATE journals SET name=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'si', $t['new'], $t['id']);
        if (mysqli_stmt_execute($stmt)) {
            $n++;
            echo "  updated id={$t['id']} user={$t['user_id']}: {$t['old']} => {$t['new']}\n";
        } else {
            echo "  FAILED id={$t['id']}: " . mysqli_stmt_error($stmt) . "\n";
        }
        mysqli_stmt_close($stmt);
    }
    echo "applied={$n}\n";
} else {
    $shown = 0;
    foreach ($targets as $t) {
        if ($verbose || $shown < 30) {
            echo "  would-change id={$t['id']} user={$t['user_id']}: {$t['old']} => {$t['new']}\n";
        }
        $shown++;
    }
    if (!$verbose && $shown > 30) {
        echo "  ... 其余 " . ($shown - 30) . " 项已省略（加 --verbose 查看全部）\n";
    }
    echo "（预览模式，未写入。加 --apply 实际修改）\n";
}

mysqli_close($conn);
