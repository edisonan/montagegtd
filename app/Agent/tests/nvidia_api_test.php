<?php

/**
 * NVIDIA API 集成测试
 * 
 * 验证 PHP Agent 框架与 NVIDIA AI API 的集成能力
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Agent\Core\LLM\Client as LlmClient;
use App\Agent\Schemas\Message;

echo "=== NVIDIA API 集成测试 ===\n\n";

// 配置 NVIDIA API
$config = [
    'api_key' => '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',
    'api_base' => 'https://integrate.api.nvidia.com/v1',
    'model' => 'minimaxai/minimax-m2.1'
];

try {
    // 创建 LLM 客户端
    echo "1. 创建 LLM 客户端...\n";
    $llmClient = new LlmClient($config);
    echo "✅ LLM 客户端创建成功\n\n";
    
    // 准备测试消息
    echo "2. 准备测试消息...\n";
    $messages = [
        Message::createUser("你好！请用中文简短介绍一下你自己。")
    ];
    
    echo "消息内容: " . $messages[0]->getContent() . "\n\n";
    
    // 执行 API 调用
    echo "3. 调用 NVIDIA API...\n";
    $startTime = microtime(true);
    
    $response = $llmClient->generate($messages);
    
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "✅ API 调用成功 (耗时: {$duration}ms)\n\n";
    
    // 显示响应结果
    echo "4. 响应结果分析:\n";
    echo "   内容长度: " . strlen($response->getContent()) . " 字符\n";
    echo "   输入 tokens: " . $response->getInputTokens() . "\n";
    echo "   输出 tokens: " . $response->getOutputTokens() . "\n";
    echo "   总 tokens: " . $response->getTotalTokens() . "\n";
    echo "   工具调用: " . ($response->hasToolCalls() ? '是 (' . $response->getToolCallCount() . '个)' : '否') . "\n\n";
    
    // 显示实际响应内容
    echo "5. 实际响应内容:\n";
    echo "--- 开始 ---\n";
    echo $response->getContent() . "\n";
    echo "--- 结束 ---\n\n";
    
    // 测试工具调用功能
    echo "6. 测试工具调用功能...\n";
    $tools = [
        [
            'name' => 'get_current_time',
            'description' => '获取当前时间',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'format' => [
                        'type' => 'string',
                        'description' => '时间格式',
                        'enum' => ['full', 'date', 'time']
                    ]
                ]
            ]
        ]
    ];
    
    $toolTestMessages = [
        Message::createUser("现在几点了？请使用 get_current_time 工具查询。")
    ];
    
    try {
        $toolResponse = $llmClient->generate($toolTestMessages, $tools);
        echo "✅ 工具调用测试完成\n";
        echo "   工具调用数量: " . $toolResponse->getToolCallCount() . "\n";
        if ($toolResponse->hasToolCalls()) {
            foreach ($toolResponse->getToolCalls() as $i => $toolCall) {
                echo "   工具调用 " . ($i + 1) . ": " . $toolCall['function']['name'] . "\n";
            }
        }
    } catch (Exception $e) {
        echo "⚠️ 工具调用测试失败: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 所有测试完成！NVIDIA API 集成成功！\n";
    
} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}

echo "\n测试结束时间: " . date('Y-m-d H:i:s') . "\n";