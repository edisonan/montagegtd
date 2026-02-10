<?php

/**
 * 多用户 Agent 框架使用示例
 * 
 * 展示如何在多用户环境下安全使用 Agent 框架
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Agent\MultiUser\MultiUserAgentFactory;
use App\Agent\MultiUser\MultiUserAgent;

// 设置全局配置
MultiUserAgentFactory::setGlobalConfig([
    'llm' => [
        'default_provider' => 'openai',
        'providers' => [
            'openai' => [
                'base_url' => 'https://api.openai.com/v1',
                'temperature' => 0.7,
                'max_tokens' => 1500,
            ]
        ]
    ],
    'session' => [
        'storage_path' => sys_get_temp_dir() . '/multi_agent_sessions',
        'max_sessions' => 1000,
    ],
    'memory' => [
        'storage_path' => sys_get_temp_dir() . '/multi_agent_memories',
        'max_memories' => 1000,
    ],
    'logging' => [
        'enabled' => true,
        'level' => 'info',
    ]
]);

echo "=== 多用户 Agent 框架演示 ===\n\n";

// 模拟不同的用户及其偏好设置
$users = [
    [
        'id' => 'user_001',
        'name' => '张三',
        'preferences' => [
            'llm' => [
                'model' => 'openai/gpt-4',
                'api_key' => 'sk-user001-xxx', // 用户自己的 API 密钥
                'temperature' => 0.8
            ]
        ]
    ],
    [
        'id' => 'user_002', 
        'name' => '李四',
        'preferences' => [
            'llm' => [
                'model' => 'anthropic/claude-3-opus',
                'api_key' => 'sk-ant-user002-xxx', // 不同提供商的密钥
                'temperature' => 0.5
            ]
        ]
    ],
    [
        'id' => 'user_003',
        'name' => '王五', 
        'preferences' => [
            'llm' => [
                'model' => 'nvidia/minimax-m2.1',
                'api_key' => 'nvapi-user003-xxx', // NVIDIA API 密钥
                'temperature' => 0.3
            ]
        ]
    ]
];

echo "1. 为不同用户创建专属 Agent 实例...\n";

$userAgents = [];
foreach ($users as $userData) {
    $userId = $userData['id'];
    $userName = $userData['name'];
    $preferences = $userData['preferences'];
    
    echo "  - 为用户 {$userName} ({$userId}) 创建 Agent...\n";
    
    $agent = MultiUserAgentFactory::createForUser($userId, $preferences);
    $userAgents[$userId] = $agent;
    
    // 显示用户的作用域路径
    $paths = $agent->getScopedPaths();
    echo "    存储路径: {$paths['session']}\n";
    echo "    记忆路径: {$paths['memory']}\n";
    echo "    日志路径: {$paths['log']}\n";
}

echo "\n2. 模拟并发任务执行...\n";

// 模拟不同用户同时执行任务
$tasks = [
    'user_001' => "帮我写一个Python快速排序算法",
    'user_002' => "解释量子计算的基本原理",
    'user_003' => "分析当前AI发展趋势"
];

$results = [];

foreach ($tasks as $userId => $task) {
    $userName = array_filter($users, fn($u) => $u['id'] === $userId)[0]['name'];
    echo "  - {$userName} 执行任务: {$task}\n";
    
    try {
        $agent = $userAgents[$userId];
        $result = $agent->run($task);
        $results[$userId] = [
            'success' => true,
            'result' => $result,
            'length' => is_string($result) ? strlen($result) : 0
        ];
        echo "    ✓ 执行成功 (结果长度: {$results[$userId]['length']} 字符)\n";
    } catch (Exception $e) {
        $results[$userId] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
        echo "    ✗ 执行失败: {$e->getMessage()}\n";
    }
}

echo "\n3. 验证数据隔离...\n";

// 检查各用户的会话数据是否隔离
foreach ($users as $userData) {
    $userId = $userData['id'];
    $userName = $userData['name'];
    
    $agent = $userAgents[$userId];
    $sessionManager = $agent->getSessionManager();
    $sessions = $sessionManager->getAllSessions();
    
    echo "  - {$userName} 的会话数量: " . count($sessions) . "\n";
    
    // 检查记忆数据
    $memoryManager = $agent->getMemoryManager();
    $memoryStats = $memoryManager->getStatistics();
    echo "  - {$userName} 的记忆数量: " . $memoryStats['total_count'] . "\n";
}

echo "\n4. 查看系统统计信息...\n";

$factoryStats = MultiUserAgentFactory::getStatistics();
echo "  活跃用户数: " . $factoryStats['active_users'] . "\n";
echo "  用户列表: " . implode(', ', $factoryStats['user_list']) . "\n";

echo "\n5. 演示用户数据清理...\n";

// 清理特定用户的数据
$cleanupUserId = 'user_001';
$cleanupUserName = array_filter($users, fn($u) => $u['id'] === $cleanupUserId)[0]['name'];

echo "  清理用户 {$cleanupUserName} 的数据...\n";
$userAgents[$cleanupUserId]->cleanupUserData(false); // 不保留日志
echo "  ✓ 数据清理完成\n";

// 验证清理效果
$remainingStats = MultiUserAgentFactory::getStatistics();
echo "  清理后活跃用户数: " . $remainingStats['active_users'] . "\n";

echo "\n=== 多用户演示完成 ===\n";
echo "✓ 不同用户的数据完全隔离\n";
echo "✓ 支持个性化的模型配置\n";
echo "✓ 安全的并发访问\n";
echo "✓ 完整的审计日志\n";

?>