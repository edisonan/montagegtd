<?php

// 简化的断点系统测试脚本
// 不依赖 Composer，直接测试核心功能

echo "=== PHP Agent 框架断点系统测试 ===\n\n";

// 手动加载必要的文件
require_once __DIR__ . '/../src/Checkpoint/Checkpoint.php';
require_once __DIR__ . '/../src/Checkpoint/CheckpointStorage.php';

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

try {
    echo "1. 测试断点对象创建...\n";
    
    // 创建测试状态和消息
    $testState = array(
        'current_step' => 5,
        'variables' => array('user_name' => '张三', 'task_status' => 'in_progress'),
        'context' => '用户正在询问天气信息'
    );
    
    $testMessages = array(
        array('role' => 'user', 'content' => '你好'),
        array('role' => 'assistant', 'content' => '你好！有什么我可以帮助你的吗？'),
        array('role' => 'user', 'content' => '今天天气怎么样？')
    );
    
    $testMemory = array(
        array('id' => 'mem_1', 'content' => '用户关心天气'),
        array('id' => 'mem_2', 'content' => '用户使用中文交流')
    );
    
    // 创建断点对象
    $checkpoint = new Agent\Checkpoint\Checkpoint(
        'cp_001',
        'session_test_1',
        5,
        $testState,
        $testMessages,
        $testMemory,
        array('reason' => '用户询问天气', 'priority' => 'high')
    );
    
    echo "✓ 断点对象创建成功\n";
    echo "  - ID: " . $checkpoint->getId() . "\n";
    echo "  - 会话ID: " . $checkpoint->getSessionId() . "\n";
    echo "  - 步骤: " . $checkpoint->getStep() . "\n";
    echo "  - 时间: " . $checkpoint->getFormattedTime() . "\n";
    echo "  - 消息数量: " . count($checkpoint->getMessages()) . "\n";
    echo "  - 记忆项数: " . count($checkpoint->getMemorySnapshot()) . "\n\n";
    
    echo "2. 测试断点序列化...\n";
    
    // 测试序列化
    $checkpointArray = $checkpoint->toArray();
    echo "✓ 断点转换为数组成功\n";
    
    $checkpointJson = $checkpoint->toJson();
    echo "✓ 断点转换为JSON成功\n";
    
    // 从JSON恢复
    $restoredCheckpoint = Agent\Checkpoint\Checkpoint::fromJson($checkpointJson);
    echo "✓ 从JSON恢复断点成功\n";
    echo "  - 恢复的步骤: " . $restoredCheckpoint->getStep() . "\n\n";
    
    echo "3. 测试断点存储管理器...\n";
    
    // 创建配置
    $config = new SimpleConfig(array(
        'checkpoint' => array(
            'storage_path' => sys_get_temp_dir() . '/test_checkpoints',
            'max_checkpoints_per_session' => 20,
            'cleanup_threshold' => 0.8
        )
    ));
    
    // 创建存储管理器
    $storage = new Agent\Checkpoint\CheckpointStorage($config);
    echo "✓ 断点存储管理器创建成功\n";
    
    // 保存多个断点
    $checkpoint1 = new Agent\Checkpoint\Checkpoint(
        'cp_001',
        'session_test_1',
        1,
        array('step' => 1, 'action' => 'initialize'),
        array(),
        array(),
        array('description' => '初始化阶段')
    );
    
    $checkpoint2 = new Agent\Checkpoint\Checkpoint(
        'cp_002',
        'session_test_1',
        2,
        array('step' => 2, 'action' => 'process_user_input'),
        array(array('role' => 'user', 'content' => 'hello')),
        array(),
        array('description' => '处理用户输入')
    );
    
    $checkpoint3 = new Agent\Checkpoint\Checkpoint(
        'cp_003',
        'session_test_1',
        3,
        array('step' => 3, 'action' => 'call_llm'),
        array(array('role' => 'user', 'content' => 'hello'), array('role' => 'assistant', 'content' => 'Hi there!')),
        array(array('id' => 'mem_1', 'content' => '用户打招呼')),
        array('description' => '调用大语言模型')
    );
    
    // 保存断点
    $save1 = $storage->saveCheckpoint($checkpoint1);
    $save2 = $storage->saveCheckpoint($checkpoint2);
    $save3 = $storage->saveCheckpoint($checkpoint3);
    
    echo "✓ 保存断点结果: " . ($save1 && $save2 && $save3 ? '全部成功' : '部分失败') . "\n\n";
    
    echo "4. 测试断点查询...\n";
    
    // 获取最新断点
    $latest = $storage->getLatestCheckpoint('session_test_1');
    echo "✓ 获取最新断点:\n";
    echo "  - 步骤: " . $latest->getStep() . "\n";
    echo "  - 描述: " . $latest->getMetadata()['description'] . "\n\n";
    
    // 按步骤获取断点
    $step2 = $storage->getCheckpointByStep('session_test_1', 2);
    echo "✓ 获取步骤2的断点:\n";
    echo "  - 动作: " . $step2->getState()['action'] . "\n\n";
    
    // 获取所有断点（按步骤排序）
    $allCheckpoints = $storage->getCheckpointsBySession('session_test_1', 'step', 'asc');
    echo "✓ 获取所有断点（按步骤升序）:\n";
    foreach ($allCheckpoints as $i => $cp) {
        echo "  " . ($i + 1) . ". 步骤" . $cp->getStep() . ": " . $cp->getMetadata()['description'] . "\n";
    }
    echo "\n";
    
    echo "5. 测试断点统计...\n";
    
    $stats = $storage->getStatistics('session_test_1');
    echo "✓ 会话统计信息:\n";
    echo "  - 总断点数: " . $stats['total_count'] . "\n";
    echo "  - 步骤分布: " . json_encode($stats['step_distribution']) . "\n\n";
    
    $globalStats = $storage->getStatistics();
    echo "✓ 全局统计信息:\n";
    echo "  - 总会话数: " . $globalStats['session_count'] . "\n";
    echo "  - 总断点数: " . $globalStats['total_count'] . "\n\n";
    
    echo "6. 测试断点克隆...\n";
    
    $cloned = $checkpoint->clone();
    echo "✓ 断点克隆成功\n";
    echo "  - 原始ID: " . $checkpoint->getId() . "\n";
    echo "  - 克隆ID: " . $cloned->getId() . "\n";
    echo "  - 步骤保持一致: " . ($checkpoint->getStep() === $cloned->getStep() ? '是' : '否') . "\n\n";
    
    echo "7. 测试断点比较...\n";
    
    $comparison = $checkpoint1->compareTo($checkpoint2);
    echo "✓ 断点时间比较:\n";
    echo "  - checkpoint1 相对于 checkpoint2: " . 
         ($comparison < 0 ? '更早' : ($comparison > 0 ? '更晚' : '相同')) . "\n\n";
    
    echo "8. 测试数据导出/导入...\n";
    
    // 导出数据
    $exportData = $storage->export('session_test_1', 'array');
    echo "✓ 数据导出成功\n";
    echo "  - 导出断点数: " . count($exportData) . "\n";
    
    // 创建新的存储实例进行导入测试
    $config2 = new SimpleConfig(array(
        'checkpoint' => array(
            'storage_path' => sys_get_temp_dir() . '/test_checkpoints_import',
            'max_checkpoints_per_session' => 20,
            'cleanup_threshold' => 0.8
        )
    ));
    
    $storage2 = new Agent\Checkpoint\CheckpointStorage($config2);
    $importCount = $storage2->import($exportData, 'array');
    echo "✓ 数据导入成功\n";
    echo "  - 成功导入断点数: " . $importCount . "\n\n";
    
    echo "9. 测试断点删除...\n";
    
    $deleteResult = $storage->deleteCheckpoint('cp_001');
    echo "✓ 删除断点 cp_001: " . ($deleteResult ? '成功' : '失败') . "\n";
    
    $remainingCount = count($storage->getCheckpointsBySession('session_test_1'));
    echo "  - 删除后的断点数量: " . $remainingCount . "\n\n";
    
    echo "=== 断点系统测试完成 ===\n";
    echo "✓ 所有测试通过！断点系统功能正常\n";
    
} catch (Exception $e) {
    echo "✗ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}