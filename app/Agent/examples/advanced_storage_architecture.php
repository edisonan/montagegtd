<?php

/**
 * 高级存储架构示例
 * 展示如何使用任意PHP类和复杂组合策略
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Agent\Storage\Factories\StorageAdapterFactory;
use App\Agent\Storage\Adapters\GenericStorageAdapter;
use App\Agent\Storage\Composites\CompositeStorageAdapter;

echo "=== 高级存储架构演示 ===\n\n";

// 1. 使用任意PHP类作为存储后端
echo "1. 通用适配器 - 使用任意PHP类...\n";

// 创建一个自定义的存储类
class CustomCacheStorage {
    private $cache = [];
    private $stats = ['hits' => 0, 'misses' => 0];
    
    public function save($key, $data, $ttl = null) {
        $this->cache[$key] = [
            'data' => $data,
            'expires' => $ttl ? time() + $ttl : null
        ];
        return true;
    }
    
    public function load($key) {
        if (!isset($this->cache[$key])) {
            $this->stats['misses']++;
            return null;
        }
        
        $item = $this->cache[$key];
        if ($item['expires'] && time() > $item['expires']) {
            unset($this->cache[$key]);
            $this->stats['misses']++;
            return null;
        }
        
        $this->stats['hits']++;
        return $item['data'];
    }
    
    public function delete($key) {
        unset($this->cache[$key]);
        return true;
    }
    
    public function exists($key) {
        return isset($this->cache[$key]) && 
               (!$this->cache[$key]['expires'] || time() <= $this->cache[$key]['expires']);
    }
    
    public function keys() {
        $validKeys = [];
        foreach ($this->cache as $key => $item) {
            if (!$item['expires'] || time() <= $item['expires']) {
                $validKeys[] = $key;
            }
        }
        return $validKeys;
    }
    
    public function clear() {
        $this->cache = [];
        return true;
    }
    
    public function getStats() {
        return [
            'adapter' => 'custom_cache',
            'cache_size' => count($this->cache),
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'hit_rate' => $this->stats['hits'] + $this->stats['misses'] > 0 ? 
                         round($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses']) * 100, 2) . '%' : '0%'
        ];
    }
}

// 使用通用适配器包装自定义类
$customStorage = new CustomCacheStorage();
$genericAdapter = StorageAdapterFactory::createGenericAdapter(
    $customStorage,
    [], // 使用默认方法映射
    'custom_cache'
);

$genericAdapter->save('test_key', ['message' => 'Hello from custom storage!']);
$data = $genericAdapter->load('test_key');
echo "  自定义存储读取: " . json_encode($data) . "\n";
echo "  自定义存储统计: " . json_encode($genericAdapter->getStats()) . "\n\n";

// 2. 读写分离架构
echo "2. 读写分离架构...\n";

$rwsConfig = [
    'read' => [
        ['type' => 'file', 'base_path' => sys_get_temp_dir() . '/read_cache'],
        ['type' => 'generic', 'data_source' => new CustomCacheStorage()]
    ],
    'write' => [
        ['type' => 'file', 'base_path' => sys_get_temp_dir() . '/persistent_storage'],
        ['type' => 'database', 'dsn' => 'sqlite:' . sys_get_temp_dir() . '/backup.db']
    ]
];

try {
    $rwsAdapter = StorageAdapterFactory::createReadWriteSplitArchitecture($rwsConfig);
    
    // 写操作会发送到所有写适配器
    $rwsAdapter->save('user:123:profile', ['name' => '张三', 'role' => 'admin']);
    
    // 读操作会从读适配器获取
    $profile = $rwsAdapter->load('user:123:profile');
    echo "  读写分离读取: " . json_encode($profile) . "\n";
    
    echo "  架构统计: " . json_encode($rwsAdapter->getStats(), JSON_UNESCAPED_UNICODE) . "\n\n";
    
} catch (Exception $e) {
    echo "  读写分离演示跳过: " . $e->getMessage() . "\n\n";
}

// 3. 高可用备份架构
echo "3. 高可用备份架构...\n";

$haConfig = [
    'primary' => [
        'type' => 'file',
        'base_path' => sys_get_temp_dir() . '/primary_storage'
    ],
    'backups' => [
        [
            'type' => 'file',
            'base_path' => sys_get_temp_dir() . '/backup_storage_1'
        ],
        [
            'type' => 'generic',
            'data_source' => new CustomCacheStorage()
        ]
    ]
];

try {
    $primaryConfig = $haConfig['primary'];
    $backupConfigs = $haConfig['backups'];
    
    $haAdapter = StorageAdapterFactory::createHighAvailabilityArchitecture($primaryConfig, $backupConfigs);
    
    // 数据会先尝试保存到主存储，失败时自动切换到备份
    $haAdapter->save('critical:data', ['important' => 'business data']);
    
    $data = $haAdapter->load('critical:data');
    echo "  高可用读取: " . json_encode($data) . "\n";
    
    echo "  高可用统计: " . json_encode($haAdapter->getStats(), JSON_UNESCAPED_UNICODE) . "\n\n";
    
} catch (Exception $e) {
    echo "  高可用演示跳过: " . $e->getMessage() . "\n\n";
}

// 4. 分层存储架构
echo "4. 分层存储架构（热→温→冷）...\n";

$tieredConfig = [
    'hot' => [
        'type' => 'generic',
        'data_source' => new CustomCacheStorage()  // 内存缓存
    ],
    'warm' => [
        'type' => 'file',
        'base_path' => sys_get_temp_dir() . '/ssd_storage'  // SSD存储
    ],
    'cold' => [
        'type' => 'file',
        'base_path' => sys_get_temp_dir() . '/hdd_storage'  // HDD存储
    ]
];

$tieredAdapter = StorageAdapterFactory::createTieredStorageArchitecture(
    $tieredConfig['hot'],
    $tieredConfig['warm'], 
    $tieredConfig['cold']
);

// 数据会按层级存储：热数据→温数据→冷数据
$tieredAdapter->save('frequently_accessed', ['data' => 'hot data'], 3600);  // 1小时过期
$tieredAdapter->save('occasionally_accessed', ['data' => 'warm data'], 86400); // 1天过期
$tieredAdapter->save('rarely_accessed', ['data' => 'cold data']); // 永不过期

echo "  分层存储键: " . json_encode($tieredAdapter->keys()) . "\n";
echo "  分层存储统计: " . json_encode($tieredAdapter->getStats(), JSON_UNESCAPED_UNICODE) . "\n\n";

// 5. 复杂组合示例
echo "5. 复杂组合 - 自定义业务逻辑适配器...\n";

// 创建一个带有业务逻辑的存储装饰器
class BusinessLogicDecorator {
    private $wrappedAdapter;
    private $businessRules;
    
    public function __construct($adapter, $rules = []) {
        $this->wrappedAdapter = $adapter;
        $this->businessRules = $rules;
    }
    
    public function save($key, $data, $ttl = null) {
        // 应用业务规则
        if (isset($this->businessRules['pre_save'])) {
            $data = call_user_func($this->businessRules['pre_save'], $key, $data);
        }
        
        $result = $this->wrappedAdapter->save($key, $data, $ttl);
        
        // 保存后处理
        if (isset($this->businessRules['post_save'])) {
            call_user_func($this->businessRules['post_save'], $key, $data, $result);
        }
        
        return $result;
    }
    
    public function load($key) {
        $data = $this->wrappedAdapter->load($key);
        
        // 应用读取规则
        if ($data && isset($this->businessRules['post_load'])) {
            $data = call_user_func($this->businessRules['post_load'], $key, $data);
        }
        
        return $data;
    }
    
    // 代理其他方法
    public function __call($method, $args) {
        return call_user_func_array([$this->wrappedAdapter, $method], $args);
    }
    
    public function getStats() {
        $stats = $this->wrappedAdapter->getStats();
        $stats['decorator'] = 'business_logic';
        return $stats;
    }
}

// 创建基础适配器
$baseAdapter = StorageAdapterFactory::createFileAdapter(sys_get_temp_dir() . '/business_storage');

// 添加业务逻辑装饰器
$businessAdapter = new BusinessLogicDecorator($baseAdapter, [
    'pre_save' => function($key, $data) {
        // 数据加密
        $data['encrypted'] = base64_encode(json_encode($data));
        return $data;
    },
    'post_load' => function($key, $data) {
        // 数据解密
        if (isset($data['encrypted'])) {
            $decrypted = json_decode(base64_decode($data['encrypted']), true);
            return $decrypted ?: $data;
        }
        return $data;
    }
]);

// 使用装饰后的适配器
$businessAdapter->save('secure:user_data', ['sensitive' => 'secret information']);
$secureData = $businessAdapter->load('secure:user_data');

echo "  业务逻辑适配器读取: " . json_encode($secureData) . "\n";
echo "  适配器统计: " . json_encode($businessAdapter->getStats()) . "\n\n";

echo "=== 高级存储架构演示完成 ===\n";
echo "✓ 支持任意PHP类作为存储后端\n";
echo "✓ 灵活的组合策略（读写分离、高可用、分层存储）\n";
echo "✓ 业务逻辑装饰器模式\n";
echo "✓ 统一的存储接口抽象\n";