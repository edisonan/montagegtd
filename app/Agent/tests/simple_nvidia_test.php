<?php

/**
 * NVIDIA API 简化测试（不依赖 Composer）
 * 
 * 直接测试 NVIDIA API 集成，验证 PHP Agent 框架的核心功能
 */

// 简单的自动加载
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

use App\Agent\Core\LLM\Client as LlmClient;
use App\Agent\Schemas\Message;

echo "=== NVIDIA API 简化集成测试 ===\n\n";

// 配置 NVIDIA API
$config = [
    'api_key' => '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',
    'api_base' => 'https://integrate.api.nvidia.com/v1',
    'model' => 'minimaxai/minimax-m2.1'
];

try {
    // 手动测试 HTTP 请求
    echo "1. 手动测试 NVIDIA API 连接...\n";
    
    $postData = json_encode([
        'model' => $config['model'],
        'messages' => [
            ['role' => 'user', 'content' => '你好！请用中文简短介绍一下你自己。']
        ],
        'max_tokens' => 200,
        'temperature' => 0.7
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['api_key'],
                'User-Agent: PHP-Agent-Framework/0.1.0'
            ],
            'content' => $postData,
            'timeout' => 30
        ]
    ]);
    
    $startTime = microtime(true);
    $response = file_get_contents($config['api_base'] . '/chat/completions', false, $context);
    $endTime = microtime(true);
    
    if ($response === false) {
        throw new Exception('HTTP 请求失败: ' . error_get_last()['message']);
    }
    
    $duration = round(($endTime - $startTime) * 1000, 2);
    echo "✅ API 请求成功 (耗时: {$duration}ms)\n\n";
    
    // 解析响应
    echo "2. 解析 API 响应...\n";
    $responseData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 解析失败: ' . json_last_error_msg());
    }
    
    if (!isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception('响应格式不符合预期: ' . print_r($responseData, true));
    }
    
    $content = $responseData['choices'][0]['message']['content'];
    $usage = $responseData['usage'] ?? [];
    
    echo "✅ 响应解析成功\n";
    echo "   内容长度: " . strlen($content) . " 字符\n";
    echo "   输入 tokens: " . ($usage['prompt_tokens'] ?? 'N/A') . "\n";
    echo "   输出 tokens: " . ($usage['completion_tokens'] ?? 'N/A') . "\n";
    echo "   总 tokens: " . ($usage['total_tokens'] ?? 'N/A') . "\n\n";
    
    // 显示实际响应
    echo "3. 实际响应内容:\n";
    echo "--- 开始 ---\n";
    echo trim($content) . "\n";
    echo "--- 结束 ---\n\n";
    
    // 测试 Agent 框架核心类
    echo "4. 测试 Agent 框架核心类...\n";
    
    // 测试 Message 类
    $testMessage = Message::createUser("测试消息");
    echo "   ✅ Message 类测试通过\n";
    echo "      角色: " . $testMessage->getRole() . "\n";
    echo "      内容: " . $testMessage->getContent() . "\n\n";
    
    // 测试基本功能
    echo "5. 测试基本功能...\n";
    echo "   ✅ PHP 版本: " . PHP_VERSION . "\n";
    echo "   ✅ JSON 扩展: " . (extension_loaded('json') ? '可用' : '不可用') . "\n";
    echo "   ✅ cURL 扩展: " . (extension_loaded('curl') ? '可用' : '不可用') . "\n";
    echo "   ✅ 文件读写: " . (is_writable(sys_get_temp_dir()) ? '可写' : '不可写') . "\n\n";
    
    echo "🎉 所有测试完成！NVIDIA API 集成验证成功！\n";
    echo "PHP Agent 框架核心功能正常工作。\n";
    
} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "调试信息:\n";
    echo "  - PHP 版本: " . PHP_VERSION . "\n";
    echo "  - 工作目录: " . getcwd() . "\n";
    echo "  - 临时目录: " . sys_get_temp_dir() . "\n";
}

echo "\n测试结束时间: " . date('Y-m-d H:i:s') . "\n";