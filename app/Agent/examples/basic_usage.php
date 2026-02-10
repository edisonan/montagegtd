<?php

/**
 * Agent 框架使用示例
 * 
 * 展示如何使用 PHP Agent Framework 创建和运行 Agent。
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Agent\Agent;
use App\Agent\Core\Config;
use App\Agent\Core\LLM\Client as LlmClient;
use App\Agent\Tools\ReadFileTool;
use App\Agent\Tools\WriteFileTool;
use App\Agent\Tools\BashTool;

// 示例 1: 基本使用
echo "=== 示例 1: 基本 Agent 使用 ===\n";

$config = new Config([
    'LLM_MODEL' => 'openai/gpt-4',
    'LLM_API_KEY' => getenv('OPENAI_API_KEY') ?: 'your-api-key-here',
    'AGENT_MAX_STEPS' => 20,
    'AGENT_WORKSPACE_DIR' => './workspace'
]);

$llmClient = new LlmClient($config->getLlmConfig());

$agent = new \App\Agent\Core\Agent($llmClient, $config);

// 添加系统提示
$agent->addSystemMessage("You are a helpful programming assistant.");

// 运行简单任务
try {
    $result = $agent->run("What is 2+2?");
    echo "结果: " . $result . "\n\n";
} catch (Exception $e) {
    echo "执行出错: " . $e->getMessage() . "\n\n";
}

// 示例 2: 带工具的 Agent
echo "=== 示例 2: 带工具的 Agent ===\n";

$tools = [
    new ReadFileTool('./workspace'),
    new WriteFileTool('./workspace'),
    new BashTool('./workspace')
];

$toolAgent = new \App\Agent\Core\Agent($llmClient, $config, $tools);

// 添加事件监听器
$toolAgent->on('step_start', function ($event, $data) {
    echo "[步骤 {$data['step']}] 开始执行\n";
});

$toolAgent->on('llm_response', function ($event, $data) {
    echo "[LLM] 生成响应，包含 " . ($data['has_tool_calls'] ? $data['tool_count'] . " 个工具调用" : "纯文本") . "\n";
});

$toolAgent->on('tool_end', function ($event, $data) {
    $status = $data['success'] ? '成功' : '失败';
    echo "[工具] {$data['tool']} 执行{$status} (耗时: " . round($data['execution_time'], 3) . "秒)\n";
});

// 创建一个简单的文件任务
try {
    $result = $toolAgent->run("Create a file called hello.txt with the content 'Hello, World!'");
    echo "任务结果: " . $result . "\n\n";
} catch (Exception $e) {
    echo "执行出错: " . $e->getMessage() . "\n\n";
}

// 示例 3: 查看 Agent 状态
echo "=== 示例 3: Agent 状态信息 ===\n";

$stats = $toolAgent->getStats();
echo "Agent 统计信息:\n";
foreach ($stats as $key => $value) {
    echo "  {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
}
echo "\n";

// 示例 4: 事件系统演示
echo "=== 示例 4: 事件系统 ===\n";

$emitter = new \App\Agent\Events\EventEmitter();

// 注册事件监听器
$emitter->on('user_action', function ($event, $data) {
    echo "用户执行了操作: {$data['action']}\n";
});

$emitter->on('system_event', function ($event, $data) {
    echo "系统事件: {$data['message']}\n";
});

// 发射事件
$emitter->emit('user_action', ['action' => '点击按钮']);
$emitter->emit('system_event', ['message' => '系统启动完成']);

echo "\n";

// 示例 5: 工具执行器独立使用
echo "=== 示例 5: 独立工具执行 ===\n";

$executor = new \App\Agent\Tools\ToolExecutor($tools);

// 直接执行工具
$result = $executor->executeSingle(
    'manual_call_1',
    'write_file',
    ['path' => 'manual_test.txt', 'content' => '这是手动执行的结果']
);

if ($result->isSuccess()) {
    echo "文件写入成功: " . $result->getContent() . "\n";
} else {
    echo "文件写入失败: " . $result->getError() . "\n";
}

echo "\n框架示例执行完成!\n";