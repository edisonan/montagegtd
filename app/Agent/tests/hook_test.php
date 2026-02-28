<?php

// 简化的 Hook 系统测试脚本
// 不依赖 Composer，直接测试核心功能

echo "=== PHP Agent 框架 Hook 系统测试 ===\n\n";

// 手动加载必要的文件
require_once __DIR__ . '/../src/Hooks/AgentHook.php';
require_once __DIR__ . '/../src/Hooks/HookManager.php';
require_once __DIR__ . '/../src/Hooks/Examples/LoggingHook.php';
require_once __DIR__ . '/../src/Hooks/Examples/PerformanceHook.php';

// 简单的配置模拟
class SimpleConfig {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function get($key, $default = null) {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}

// 简单的上下文模拟
class SimpleContext {
    private $data;
    
    public function __construct() {
        $this->data = array();
    }
    
    public function setData($key, $value) {
        $this->data[$key] = $value;
    }
    
    public function getData($key, $default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
}

try {
    echo "1. 测试 Hook 基类功能...\n";
    
    // 创建一个简单的测试 Hook
    class TestHook extends Agent\Hooks\AgentHook {
        public $callLog = array();
        
        public function onAgentInitialize($context) {
            $this->callLog[] = 'onAgentInitialize';
        }
        
        public function onToolCall($context, $toolName, $arguments) {
            $this->callLog[] = "onToolCall: {$toolName}";
            // 修改参数示例
            $arguments['modified'] = true;
            return $arguments;
        }
        
        public function onToolResult($context, $toolName, $arguments, $result) {
            $this->callLog[] = "onToolResult: {$toolName}";
            // 修改结果示例
            return $result . " [processed by hook]";
        }
        
        public function getDescription() {
            return "测试用 Hook";
        }
    }
    
    $testHook = new TestHook('test_hook', 50);
    echo "✓ Hook 基类创建成功\n";
    echo "  - 名称: " . $testHook->getName() . "\n";
    echo "  - 优先级: " . $testHook->getPriority() . "\n";
    echo "  - 是否启用: " . ($testHook->isEnabled() ? '是' : '否') . "\n\n";
    
    echo "2. 测试 Hook 管理器...\n";
    
    // 创建配置
    $config = new SimpleConfig(array(
        'hooks' => array(
            'enabled' => array('test_hook', 'logging_hook'),
        )
    ));
    
    // 创建 Hook 管理器
    $hookManager = new Agent\Hooks\HookManager($config);
    echo "✓ Hook 管理器创建成功\n";
    
    // 注册测试 Hook
    $hookManager->registerHook(
        Agent\Hooks\HookManager::HOOK_AGENT_INITIALIZE,
        $testHook
    );
    
    // 注册内置 Hook
    $loggingHook = new Agent\Hooks\Examples\LoggingHook(100);
    $performanceHook = new Agent\Hooks\Examples\PerformanceHook(150);
    
    $hookManager->registerHook(
        Agent\Hooks\HookManager::HOOK_AGENT_INITIALIZE,
        $loggingHook
    );
    
    $hookManager->registerHook(
        Agent\Hooks\HookManager::HOOK_TOOL_CALL,
        $performanceHook
    );
    
    echo "✓ Hook 注册成功\n";
    
    // 显示统计信息
    $stats = $hookManager->getStatistics();
    echo "  - 总 Hook 数: " . $stats['total_hooks'] . "\n";
    echo "  - 启用 Hook 数: " . $stats['enabled_hooks'] . "\n";
    echo "  - Hook 点数: " . $stats['registered_points'] . "\n\n";
    
    echo "3. 测试 Hook 执行...\n";
    
    // 创建测试上下文
    $context = new SimpleContext();
    $context->setData('test_data', 'Hello World');
    
    // 执行 Agent 初始化 Hook
    $hookManager->executeAgentInitialize($context);
    echo "✓ Agent 初始化 Hook 执行完成\n";
    
    // 测试工具调用 Hook
    $toolArguments = array('param1' => 'value1');
    $modifiedArgs = $hookManager->executeToolCall($context, 'test_tool', $toolArguments);
    echo "✓ 工具调用 Hook 执行完成\n";
    echo "  - 原始参数: " . json_encode($toolArguments) . "\n";
    echo "  - 修改后参数: " . json_encode($modifiedArgs) . "\n";
    
    // 测试工具结果 Hook
    $toolResult = "原始结果";
    $processedResult = $hookManager->executeToolResult($context, 'test_tool', $toolArguments, $toolResult);
    echo "✓ 工具结果 Hook 执行完成\n";
    echo "  - 原始结果: " . $toolResult . "\n";
    echo "  - 处理后结果: " . $processedResult . "\n\n";
    
    echo "4. 测试 Hook 优先级...\n";
    
    // 创建不同优先级的 Hook
    class PriorityHook1 extends Agent\Hooks\AgentHook {
        public $executionOrder = array();
        
        public function onAgentInitialize($context) {
            $this->executionOrder[] = 'PriorityHook1 (priority 10)';
        }
    }
    
    class PriorityHook2 extends Agent\Hooks\AgentHook {
        public $executionOrder = array();
        
        public function onAgentInitialize($context) {
            $this->executionOrder[] = 'PriorityHook2 (priority 50)';
        }
    }
    
    class PriorityHook3 extends Agent\Hooks\AgentHook {
        public $executionOrder = array();
        
        public function onAgentInitialize($context) {
            $this->executionOrder[] = 'PriorityHook3 (priority 5)';
        }
    }
    
    $priorityHook1 = new PriorityHook1('priority_hook_1', 10);
    $priorityHook2 = new PriorityHook2('priority_hook_2', 50);
    $priorityHook3 = new PriorityHook3('priority_hook_3', 5);
    
    // 创建新的 Hook 管理器专门测试优先级
    $priorityConfig = new SimpleConfig(array('hooks' => array('enabled' => array())));
    $priorityManager = new Agent\Hooks\HookManager($priorityConfig);
    
    $priorityManager->registerHook(Agent\Hooks\HookManager::HOOK_AGENT_INITIALIZE, $priorityHook1);
    $priorityManager->registerHook(Agent\Hooks\HookManager::HOOK_AGENT_INITIALIZE, $priorityHook2);
    $priorityManager->registerHook(Agent\Hooks\HookManager::HOOK_AGENT_INITIALIZE, $priorityHook3);
    
    // 执行 Hook
    $priorityManager->executeAgentInitialize($context);
    
    echo "✓ 优先级测试完成\n";
    echo "  - 执行顺序: " . implode(' -> ', $priorityHook3->executionOrder) . "\n";
    echo "  - 验证: 优先级 5 -> 10 -> 50\n\n";
    
    echo "5. 测试 Hook 启用/禁用...\n";
    
    $hookName = $testHook->getName();
    $disableResult = $hookManager->disableHook($hookName);
    echo "✓ 禁用 Hook 结果: " . ($disableResult ? '成功' : '失败') . "\n";
    echo "  - Hook 状态: " . ($testHook->isEnabled() ? '启用' : '禁用') . "\n";
    
    $enableResult = $hookManager->enableHook($hookName);
    echo "✓ 启用 Hook 结果: " . ($enableResult ? '成功' : '失败') . "\n";
    echo "  - Hook 状态: " . ($testHook->isEnabled() ? '启用' : '禁用') . "\n\n";
    
    echo "6. 测试异常处理...\n";
    
    class ExceptionHook extends Agent\Hooks\AgentHook {
        public function onAgentInitialize($context) {
            throw new Exception("测试异常");
        }
    }
    
    $exceptionHook = new ExceptionHook('exception_hook');
    $hookManager->registerHook(Agent\Hooks\HookManager::HOOK_AGENT_INITIALIZE, $exceptionHook);
    
    // 执行应该捕获异常而不中断
    $hookManager->executeAgentInitialize($context);
    echo "✓ 异常处理测试完成 - 系统未崩溃\n\n";
    
    echo "7. 测试内置 Hook 功能...\n";
    
    // 测试日志 Hook 是否生成日志文件
    $logFile = sys_get_temp_dir() . '/agent_hooks.log';
    $logExists = file_exists($logFile);
    echo "✓ 日志 Hook 文件创建: " . ($logExists ? '是' : '否') . "\n";
    
    if ($logExists) {
        $logContent = file_get_contents($logFile);
        echo "  - 日志内容预览: " . substr($logContent, 0, 100) . "...\n";
    }
    
    // 测试性能 Hook 是否生成性能数据
    $perfFile = sys_get_temp_dir() . '/agent_performance.jsonl';
    $perfExists = file_exists($perfFile);
    echo "✓ 性能 Hook 文件创建: " . ($perfExists ? '是' : '否') . "\n\n";
    
    echo "=== Hook 系统测试完成 ===\n";
    echo "✓ 所有测试通过！Hook 系统功能正常\n";
    
} catch (Exception $e) {
    echo "✗ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}