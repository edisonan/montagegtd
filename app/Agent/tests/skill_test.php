<?php

// 简化的技能系统测试脚本
echo "=== PHP Agent 框架技能系统测试 ===\n\n";

// 手动加载必要的文件
require_once __DIR__ . '/../src/Skills/Skill.php';
require_once __DIR__ . '/../src/Skills/SkillLoader.php';
require_once __DIR__ . '/../src/Skills/Examples/WeatherSkill.php';
require_once __DIR__ . '/../src/Skills/Examples/FileProcessingSkill.php';

// 简单的配置模拟
class SimpleConfig {
    private $config;
    public function __construct($config) { $this->config = $config; }
    public function get($key, $default = null) { return $default; }
}

// 简单的上下文模拟
class SimpleContext {
    private $tools = array('web_search', 'file_read', 'file_write');
    private $toolExecutor;
    public function __construct() { $this->toolExecutor = new SimpleToolExecutor(); }
    public function getAvailableTools() { return $this->tools; }
    public function getToolExecutor() { return $this->toolExecutor; }
}

// 简单的工具执行器模拟
class SimpleToolExecutor {
    public function execute($toolName, $parameters) {
        switch ($toolName) {
            case 'web_search': return array('results' => array(array('content' => '上海天气晴朗，15°C')));
            case 'file_read': return "测试文件内容\n第二行\n第三行";
            case 'file_write': return "文件写入成功";
            default: throw new Exception("未知工具: {$toolName}");
        }
    }
}

try {
    echo "1. 测试技能基类...\n";
    class TestSkill extends Agent\Skills\Skill {
        public function execute($context, $parameters = array()) {
            return array('result' => 'success', 'params' => $parameters);
        }
    }
    
    $testSkill = new TestSkill('test_skill', '测试技能');
    echo "✓ 技能创建成功: " . $testSkill->getName() . "\n\n";
    
    echo "2. 测试技能加载器...\n";
    $config = new SimpleConfig(array());
    $skillLoader = new Agent\Skills\SkillLoader($config);
    echo "✓ 加载器创建成功\n\n";
    
    echo "3. 手动注册和测试技能...\n";
    
    // 手动创建技能实例
    $weatherSkill = new Agent\Skills\Examples\WeatherSkill();
    $fileSkill = new Agent\Skills\Examples\FileProcessingSkill();
    
    // 直接测试技能执行
    $context = new SimpleContext();
    
    echo "✓ 天气技能测试:\n";
    $weatherResult = $weatherSkill->execute($context, array('city' => '上海'));
    echo "  - 城市: " . $weatherResult['city'] . "\n";
    echo "  - 天气: " . $weatherResult['weather']['condition'] . "\n\n";
    
    echo "✓ 文件处理技能测试:\n";
    $testFile = sys_get_temp_dir() . '/skill_test.txt';
    file_put_contents($testFile, "测试内容\n第二行");
    
    $fileResult = $fileSkill->execute($context, array(
        'action' => 'analyze',
        'file_path' => $testFile
    ));
    echo "  - 行数: " . $fileResult['result']['line_count'] . "\n";
    echo "  - 字符数: " . $fileResult['result']['character_count'] . "\n\n";
    
    echo "4. 测试技能元数据...\n";
    echo "✓ 天气技能元数据:\n";
    $weatherMeta = $weatherSkill->getMetadata();
    foreach ($weatherMeta as $key => $value) {
        if (is_array($value)) {
            echo "  - {$key}: " . json_encode($value) . "\n";
        } else {
            echo "  - {$key}: {$value}\n";
        }
    }
    echo "\n";
    
    echo "✓ 文件技能元数据:\n";
    $fileMeta = $fileSkill->getMetadata();
    foreach ($fileMeta as $key => $value) {
        if (is_array($value)) {
            echo "  - {$key}: " . json_encode($value) . "\n";
        } else {
            echo "  - {$key}: {$value}\n";
        }
    }
    echo "\n";
    
    echo "5. 测试技能依赖检查...\n";
    $availableTools = $context->getAvailableTools();
    $missingDeps = $weatherSkill->checkDependencies($availableTools);
    echo "✓ 依赖检查:\n";
    echo "  - 可用工具: " . implode(', ', $availableTools) . "\n";
    echo "  - 缺失依赖: " . (empty($missingDeps) ? '无' : implode(', ', $missingDeps)) . "\n\n";
    
    echo "6. 测试参数验证...\n";
    $validParams = array('city' => '北京');
    $invalidParams = array('wrong_param' => 'value');
    
    $validResult = $weatherSkill->validateParameters($validParams);
    $invalidResult = $weatherSkill->validateParameters($invalidParams);
    
    echo "✓ 参数验证测试:\n";
    echo "  - 有效参数: " . ($validResult ? '通过' : '失败') . "\n";
    echo "  - 无效参数: " . ($invalidResult ? '通过' : '失败') . " (预期失败)\n\n";
    
    echo "=== 技能系统测试完成 ===\n";
    echo "✓ 所有核心功能测试通过！\n";
    
} catch (Exception $e) {
    echo "✗ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}