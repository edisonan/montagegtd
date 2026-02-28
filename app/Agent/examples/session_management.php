<?php

/**
 * Agent 会话管理使用示例
 * 
 * 展示如何在 Laravel 项目中使用 PHP Agent 框架的会话管理功能。
 */

// 简单的自动加载实现
define('AGENT_SRC_DIR', __DIR__ . '/../src');
spl_autoload_register(function ($class) {
    $prefix = 'App\\Agent\\';
    if (strpos($class, $prefix) === 0) {
        $relativeClass = substr($class, strlen($prefix));
        $file = AGENT_SRC_DIR . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

use App\Agent\Session\SessionManager;
use App\Agent\Session\AgentRunRecord;

// 示例 1: 基本会话管理使用
echo "=== 示例 1: 基本会话管理 ===\n";

// 创建会话管理器（使用文件存储）
$tempStorage = sys_get_temp_dir() . '/agent_sessions_example.json';
$sessionManager = new SessionManager(
    $tempStorage, // 临时存储路径
    true // 启用文件存储
);

// 获取或创建会话
$session = $sessionManager->getSession(
    'session_123',
    'coding_assistant',
    'user_456'
);

// 添加运行记录
$run1 = new AgentRunRecord(
    uniqid('run_'),
    '帮我写一个快速排序算法',
    '好的，我来为您写一个快速排序算法...',
    true,
    5,
    null,
    ['model' => 'gpt-4', 'tokens' => 1200]
);

$sessionManager->addRun('session_123', $run1);

// 获取历史消息
$historyMessages = $session->getHistoryMessages(2);
echo "历史消息数量: " . count($historyMessages) . "\n";
foreach ($historyMessages as $msg) {
    echo "[{$msg['role']}]: " . mb_substr($msg['content'], 0, 50) . "...\n";
}

echo "\n";

// 示例 2: 会话持久化演示
echo "=== 示例 2: 会话持久化演示 ===\n";

// 创建带文件存储的会话管理器
$tempStorage = sys_get_temp_dir() . '/agent_sessions_test.json';
$fileSessionManager = new SessionManager($tempStorage, true);

// 创建会话并添加记录
$fileSession = $fileSessionManager->getSession('persistent_session', 'test_agent', 'test_user');
$persistentRun = new AgentRunRecord(
    uniqid('run_'),
    '持久化测试任务',
    '这是持久化测试的响应内容',
    true,
    4
);
$fileSessionManager->addRun('persistent_session', $persistentRun);

echo "会话已保存到文件: {$tempStorage}\n";
echo "会话包含 " . $fileSession->getRunsCount() . " 条运行记录\n\n";

// 清理临时文件
if (file_exists($tempStorage)) {
    unlink($tempStorage);
    echo "临时文件已清理\n";
}

// 示例 3: 会话统计和维护
echo "=== 示例 3: 会话统计和维护 ===\n";

// 获取统计信息
$stats = $sessionManager->getStats();
echo "会话统计:\n";
echo "- 总会话数: {$stats['total_sessions']}\n";
echo "- 总运行数: {$stats['total_runs']}\n";
echo "- 最老会话年龄: " . round($stats['oldest_session_age_days'], 2) . " 天\n";
echo "- 最新会话年龄: " . round($stats['newest_session_age_days'], 2) . " 天\n";

// 清理过期会话（保留7天内的会话）
$cleanedCount = $sessionManager->cleanupOldSessions(7);
echo "清理了 {$cleanedCount} 个过期会话\n\n";

// 示例 4: 高级功能演示
echo "=== 示例 4: 高级功能 ===\n";

// 获取历史上下文（用于系统提示）
$historyContext = $session->getHistoryContext(2, 1000);
echo "历史上下文:\n";
echo $historyContext . "\n\n";

// 裁剪会话运行记录（保留最近10条）
$trimmedCount = $sessionManager->trimSessionRuns('session_123', 10);
echo "裁剪了 {$trimmedCount} 条运行记录\n\n";

echo "会话管理示例执行完成!\n";
echo "\n提示：要与 Laravel LLM Agent 系统集成，请使用 AgentSessionService 类。\n";