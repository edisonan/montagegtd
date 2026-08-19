<?php
/**
 * 清理 article_ai_renders.html_content 开头的大标题横幅（双标题问题）。
 *
 * 与 app/Services/ArticleAiRenderService.php::stripLeadingTitleBanner 逻辑保持一致：
 * 只去掉最开头的标题块（wrapper div 内含 h2(+可选 subtitle p)，或裸 h2），
 * 卡片自身的 h3 表头与正文不被改动。
 *
 * 用法：
 *   php scripts/cleanup_ai_render_titles.php            # 预览，只统计不动数据
 *   php scripts/cleanup_ai_render_titles.php --apply    # 实际写入
 *   php scripts/cleanup_ai_render_titles.php --id 5     # 只处理指定 id（预览）
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

function stripLeadingTitleBanner($html)
{
    $html = trim((string)$html);
    $consumedSection = false;

    if (preg_match('/\A\s*<section\b[^>]*>\s*(?:<div\b[^>]*>\s*<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?\s*<\/div>|<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?)/is', $html)) {
        $html = preg_replace('/\A\s*<section\b[^>]*>\s*(?:<div\b[^>]*>\s*<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?\s*<\/div>|<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?)\s*/is', '', $html, 1);
        $consumedSection = true;
    } else {
        $html = preg_replace('/\A\s*<div\b[^>]*>\s*<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?\s*<\/div>\s*/is', '', $html, 1);
        $html = preg_replace('/\A\s*<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?\s*/is', '', $html, 1);
    }

    if ($consumedSection) {
        $html = preg_replace('/<\/section>\s*\z/is', '', $html, 1);
    }

    return trim((string)$html);
}

$apply = in_array('--apply', $argv, true);
$onlyId = null;
foreach ($argv as $arg) {
    if (preg_match('/^--id=(\d+)$/', $arg, $m)) {
        $onlyId = (int)$m[1];
    }
}

$root = dirname(__DIR__);
$env = loadEnv($root . '/.env');
if (empty($env['DB_DATABASE'])) {
    fwrite(STDERR, "cannot load .env\n");
    exit(1);
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

$conn = @mysqli_connect($host, $user, $pass, $env['DB_DATABASE'], (int)$port);
if (!$conn) {
    fwrite(STDERR, 'db connect failed: ' . mysqli_connect_error() . "\n");
    exit(1);
}
mysqli_set_charset($conn, 'utf8mb4');

$where = "status='success' AND html_content IS NOT NULL AND html_content <> ''";
if ($onlyId !== null) {
    $where .= ' AND id=' . (int)$onlyId;
}

$sql = "SELECT id, html_content FROM article_ai_renders WHERE {$where}";
$res = mysqli_query($conn, $sql);
if (!$res) {
    fwrite(STDERR, 'query failed: ' . mysqli_error($conn) . "\n");
    exit(1);
}

$changed = 0;
$unchanged = 0;
$rows = array();
while ($row = mysqli_fetch_assoc($res)) {
    $new = stripLeadingTitleBanner($row['html_content']);
    if ($new !== $row['html_content']) {
        $changed++;
        $rows[] = array('id' => (int)$row['id'], 'new' => $new);
    } else {
        $unchanged++;
    }
}

echo "analyzed=" . ($changed + $unchanged) . " changed=" . $changed . " unchanged=" . $unchanged . "\n";

if ($apply) {
    $n = 0;
    foreach ($rows as $r) {
        $stmt = mysqli_prepare($conn, 'UPDATE article_ai_renders SET html_content=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'si', $r['new'], $r['id']);
        if (mysqli_stmt_execute($stmt)) {
            $n++;
            echo "  updated id={$r['id']}\n";
        } else {
            echo "  FAILED id={$r['id']}: " . mysqli_stmt_error($stmt) . "\n";
        }
        mysqli_stmt_close($stmt);
    }
    echo "applied={$n}\n";
} else {
    foreach ($rows as $r) {
        echo "  would-change id={$r['id']}: new_head=" . mb_substr(strip_tags($r['new']), 0, 40) . "\n";
    }
}

mysqli_close($conn);
