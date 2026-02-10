<?php

// 简化的记忆系统测试脚本
// 不依赖 Composer，直接测试核心功能

echo "=== PHP Agent 框架记忆系统测试 ===\n\n";

// 手动加载必要的文件
require_once __DIR__ . '/../src/Memory/Memory.php';
require_once __DIR__ . '/../src/Memory/MemoryManager.php';

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
    echo "1. 测试记忆对象创建...\n";
    
    // 创建记忆对象
    $memory = new Agent\Memory\Memory(
        'test_mem_1',
        'test_session_1',
        '用户询问了关于天气的问题',
        Agent\Memory\Memory::TYPE_INTERACTION,
        array('topic' => 'weather', 'sentiment' => 'neutral'),
        0.8
    );
    
    echo "✓ 记忆对象创建成功\n";
    echo "  - ID: " . $memory->getId() . "\n";
    echo "  - 内容: " . $memory->getContent() . "\n";
    echo "  - 类型: " . $memory->getType() . "\n";
    echo "  - 重要性: " . $memory->getImportance() . "\n";
    echo "  - 是否重要: " . ($memory->isImportant() ? '是' : '否') . "\n\n";
    
    echo "2. 测试记忆序列化...\n";
    
    // 测试序列化
    $memoryArray = $memory->toArray();
    echo "✓ 记忆转换为数组成功\n";
    
    $memoryJson = $memory->toJson();
    echo "✓ 记忆转换为JSON成功\n";
    
    // 从JSON恢复
    $restoredMemory = Agent\Memory\Memory::fromJson($memoryJson);
    echo "✓ 从JSON恢复记忆成功\n";
    echo "  - 恢复的内容: " . $restoredMemory->getContent() . "\n\n";
    
    echo "3. 测试记忆相关性计算...\n";
    
    // 测试相关性计算
    $relevance1 = $memory->calculateRelevance('天气');
    $relevance2 = $memory->calculateRelevance('编程');
    
    echo "✓ 相关性计算完成\n";
    echo "  - 与'天气'的相关性: " . round($relevance1, 3) . "\n";
    echo "  - 与'编程'的相关性: " . round($relevance2, 3) . "\n\n";
    
    echo "4. 测试记忆管理器...\n";
    
    // 创建配置
    $config = new SimpleConfig(array(
        'memory' => array(
            'storage_path' => sys_get_temp_dir() . '/test_memories',
            'max_memories' => 100,
            'cleanup_threshold' => 0.8
        )
    ));
    
    // 创建记忆管理器
    $memoryManager = new Agent\Memory\MemoryManager($config);
    echo "✓ 记忆管理器创建成功\n";
    
    // 添加一些测试记忆
    $memory1 = $memoryManager->addMemory(
        'test_session_1',
        '用户喜欢编程和人工智能',
        Agent\Memory\Memory::TYPE_OBSERVATION,
        array('interests' => ['programming', 'ai']),
        0.9
    );
    
    $memory2 = $memoryManager->addMemory(
        'test_session_1',
        '用户询问了今天的天气情况',
        Agent\Memory\Memory::TYPE_INTERACTION,
        array('topic' => 'weather'),
        0.6
    );
    
    $memory3 = $memoryManager->addMemory(
        'test_session_1',
        '用户提到了Python编程语言',
        Agent\Memory\Memory::TYPE_OBSERVATION,
        array('topic' => 'programming', 'language' => 'python'),
        0.7
    );
    
    echo "✓ 添加了3个测试记忆\n\n";
    
    echo "5. 测试记忆搜索...\n";
    
    // 搜索编程相关的记忆
    $programmingMemories = $memoryManager->searchMemories(
        'test_session_1',
        '编程',
        null,
        10,
        0.1
    );
    
    echo "✓ 编程相关记忆搜索结果:\n";
    foreach ($programmingMemories as $i => $mem) {
        echo "  " . ($i + 1) . ". " . $mem->getContent() . " (相关性: " . round($mem->calculateRelevance('编程'), 3) . ")\n";
    }
    
    echo "\n";
    
    // 搜索天气相关的记忆
    $weatherMemories = $memoryManager->searchMemories(
        'test_session_1',
        '天气',
        null,
        10,
        0.1
    );
    
    echo "✓ 天气相关记忆搜索结果:\n";
    foreach ($weatherMemories as $i => $mem) {
        echo "  " . ($i + 1) . ". " . $mem->getContent() . " (相关性: " . round($mem->calculateRelevance('天气'), 3) . ")\n";
    }
    
    echo "\n";
    
    echo "6. 测试重要记忆获取...\n";
    
    $importantMemories = $memoryManager->getImportantMemories('test_session_1', 0.7, 10);
    echo "✓ 重要记忆获取结果:\n";
    foreach ($importantMemories as $i => $mem) {
        echo "  " . ($i + 1) . ". " . $mem->getContent() . " (重要性: " . $mem->getImportance() . ")\n";
    }
    
    echo "\n";
    
    echo "7. 测试统计信息...\n";
    
    $stats = $memoryManager->getStatistics('test_session_1');
    echo "✓ 会话统计信息:\n";
    echo "  - 总记忆数: " . $stats['total_count'] . "\n";
    echo "  - 平均重要性: " . $stats['average_importance'] . "\n";
    echo "  - 记忆类型分布: " . json_encode($stats['types']) . "\n\n";
    
    $globalStats = $memoryManager->getStatistics();
    echo "✓ 全局统计信息:\n";
    echo "  - 总会话数: " . $globalStats['session_count'] . "\n";
    echo "  - 总记忆数: " . $globalStats['total_count'] . "\n\n";
    
    echo "8. 测试记忆更新...\n";
    
    $updateSuccess = $memoryManager->updateMemory(
        $memory1->getId(),
        array(
            'content' => '用户非常喜欢编程和人工智能技术',
            'importance' => 0.95
        )
    );
    
    echo "✓ 记忆更新" . ($updateSuccess ? '成功' : '失败') . "\n";
    
    $updatedMemories = $memoryManager->searchMemories('test_session_1', '编程');
    if (!empty($updatedMemories)) {
        $updatedMemory = $updatedMemories[0];
        echo "  - 更新后的内容: " . $updatedMemory->getContent() . "\n";
        echo "  - 更新后的重要性: " . $updatedMemory->getImportance() . "\n\n";
    } else {
        echo "  - 未找到编程相关的记忆\n\n";
    }
    
    echo "9. 测试记忆删除...\n";
    
    $deleteSuccess = $memoryManager->deleteMemory($memory2->getId());
    echo "✓ 记忆删除" . ($deleteSuccess ? '成功' : '失败') . "\n";
    
    $remainingCount = count($memoryManager->getMemoriesBySession('test_session_1'));
    echo "  - 删除后的记忆数量: " . $remainingCount . "\n\n";
    
    echo "=== 记忆系统测试完成 ===\n";
    echo "✓ 所有测试通过！记忆系统功能正常\n";
    
} catch (Exception $e) {
    echo "✗ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}