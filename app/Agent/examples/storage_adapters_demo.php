<?php

/**
 * 存储策略适配器使用示例
 * 
 * 展示如何在文件存储和数据库存储之间切换
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Agent\Storage\StorageManager;
use App\Agent\Storage\Adapters\FileStorageAdapter;
use App\Agent\Storage\Adapters\DatabaseStorageAdapter;

echo "=== 存储策略适配器演示 ===\n\n";

// 配置1: 纯文件存储（默认）
echo "1. 文件存储适配器演示...\n";

$fileConfig = [
    'default' => 'file',
    'file' => [
        'base_path' => sys_get_temp_dir() . '/demo_file_storage',
        'extension' => '.json'
    ]
];

$fileStorage = new StorageManager($fileConfig);

// 测试基本操作
$testKey = 'user:test_user:greeting';
$testData = ['message' => 'Hello from file storage!', 'timestamp' => time()];

echo "  保存数据...\n";
$fileStorage->save($testKey, $testData);

echo "  读取数据...\n";
$loadedData = $fileStorage->load($testKey);
echo "  读取结果: " . json_encode($loadedData, JSON_UNESCAPED_UNICODE) . "\n";

echo "  存储统计:\n";
$fileStats = $fileStorage->getStats();
foreach ($fileStats as $key => $value) {
    echo "    {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
}

echo "\n";

// 配置2: 数据库存储（需要数据库连接）
echo "2. 数据库存储适配器演示...\n";

try {
    $dbConfig = [
        'default' => 'database',
        'database' => [
            'dsn' => 'sqlite:' . sys_get_temp_dir() . '/demo_storage.db',
            'table_name' => 'demo_agent_storage'
        ]
    ];
    
    $dbStorage = new StorageManager($dbConfig);
    
    // 测试数据库存储
    $dbKey = 'user:test_user:db_greeting';
    $dbData = ['message' => 'Hello from database storage!', 'timestamp' => time()];
    
    echo "  保存数据到数据库...\n";
    $dbStorage->save($dbKey, $dbData);
    
    echo "  从数据库读取数据...\n";
    $dbLoadedData = $dbStorage->load($dbKey);
    echo "  读取结果: " . json_encode($dbLoadedData, JSON_UNESCAPED_UNICODE) . "\n";
    
    echo "  数据库存储统计:\n";
    $dbStats = $dbStorage->getStats();
    foreach ($dbStats as $key => $value) {
        echo "    {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
    }
    
} catch (Exception $e) {
    echo "  数据库存储测试跳过: " . $e->getMessage() . "\n";
}

echo "\n";

// 配置3: 混合存储策略
echo "3. 混合存储策略演示...\n";

$mixedConfig = [
    'default' => 'file',
    'file' => [
        'base_path' => sys_get_temp_dir() . '/mixed_file_storage'
    ],
    'database' => [
        'dsn' => 'sqlite:' . sys_get_temp_dir() . '/mixed_storage.db'
    ]
];

$mixedStorage = new StorageManager($mixedConfig);

// 注册额外的适配器
$mixedStorage->registerAdapter('cache', new FileStorageAdapter(sys_get_temp_dir() . '/cache_storage'));

echo "  可用适配器: " . implode(', ', array_keys($mixedStorage->getAdapters())) . "\n";
echo "  默认适配器: " . $mixedStorage->getDefaultAdapterName() . "\n";

// 使用不同适配器
$mixedStorage->getAdapter('file')->save('file_data', ['type' => 'file']);
$mixedStorage->getAdapter('cache')->save('cache_data', ['type' => 'cache']);

echo "  文件适配器键: " . json_encode($mixedStorage->getAdapter('file')->keys()) . "\n";
echo "  缓存适配器键: " . json_encode($mixedStorage->getAdapter('cache')->keys()) . "\n";

echo "\n";

// 配置4: 存储迁移演示
echo "4. 存储迁移演示...\n";

// 创建源存储（文件）
$sourceStorage = new StorageManager([
    'default' => 'file',
    'file' => ['base_path' => sys_get_temp_dir() . '/migration_source']
]);

// 创建目标存储（不同的文件路径）
$targetStorage = new StorageManager([
    'default' => 'file', 
    'file' => ['base_path' => sys_get_temp_dir() . '/migration_target']
]);

// 添加测试数据
$testKeys = ['user1:data', 'user2:data', 'user3:data'];
foreach ($testKeys as $key) {
    $sourceStorage->save($key, ['content' => "Data for {$key}", 'created' => time()]);
}

echo "  源存储键数量: " . count($sourceStorage->keys()) . "\n";
echo "  目标存储键数量: " . count($targetStorage->keys()) . "\n";

// 执行迁移
$migratedCount = 0;
foreach ($testKeys as $key) {
    $data = $sourceStorage->load($key);
    if ($data !== null) {
        if ($targetStorage->save($key, $data)) {
            $migratedCount++;
        }
    }
}

echo "  迁移完成，共迁移 {$migratedCount} 个键\n";
echo "  迁移后目标存储键数量: " . count($targetStorage->keys()) . "\n";

echo "\n";

// 配置5: 环境变量配置演示
echo "5. 环境变量配置演示...\n";

// 模拟环境变量
putenv('AGENT_STORAGE_DEFAULT=database');
putenv('AGENT_STORAGE_DB_DSN=sqlite:/tmp/env_config.db');

$envConfig = [
    'default' => getenv('AGENT_STORAGE_DEFAULT'),
    'database' => [
        'dsn' => getenv('AGENT_STORAGE_DB_DSN'),
        'table_name' => 'env_agent_storage'
    ]
];

echo "  环境变量配置:\n";
echo "    默认存储: " . $envConfig['default'] . "\n";
echo "    数据库DSN: " . $envConfig['database']['dsn'] . "\n";

echo "\n=== 存储策略演示完成 ===\n";
echo "✓ 支持文件和数据库双重存储\n";
echo "✓ 可动态切换存储策略\n";
echo "✓ 支持数据迁移功能\n";
echo "✓ 环境变量配置支持\n";
echo "✓ 统一的存储接口\n";