# 存储策略适配器系统

## 🎯 设计理念

实现**存储策略与业务逻辑解耦**，支持多种存储后端的无缝切换，包括文件系统、数据库、缓存等。

## 🏗️ 架构设计

### 核心组件

```
Storage/
├── StorageAdapterInterface.php     # 存储适配器接口
├── StorageManager.php              # 存储管理器
└── Adapters/
    ├── FileStorageAdapter.php      # 文件系统适配器
    └── DatabaseStorageAdapter.php  # 数据库存储适配器
```

### 设计模式

- **适配器模式**: 统一不同存储后端的接口
- **策略模式**: 运行时切换存储策略
- **工厂模式**: 动态创建适配器实例

## 🔧 使用方式

### 1. 基础使用（默认文件存储）

```php
use App\Agent\Storage\StorageManager;

// 使用默认配置（文件存储）
$storage = new StorageManager();

// 基本操作
$storage->save('user:123:profile', ['name' => '张三', 'age' => 25]);
$profile = $storage->load('user:123:profile');
$exists = $storage->exists('user:123:profile');
$storage->delete('user:123:profile');
```

### 2. 数据库存储配置

```php
$dbConfig = [
    'default' => 'database',
    'database' => [
        'dsn' => 'mysql:host=localhost;dbname=agent_db',
        'username' => 'db_user',
        'password' => 'db_password',
        'table_name' => 'agent_storage'
    ]
];

$storage = new StorageManager($dbConfig);
```

### 3. 混合存储策略

```php
$mixedConfig = [
    'default' => 'file',
    'file' => [
        'base_path' => '/var/lib/agent/file_storage'
    ],
    'database' => [
        'dsn' => 'sqlite:/var/lib/agent/db_storage.db'
    ]
];

$storage = new StorageManager($mixedConfig);

// 使用不同适配器
$storage->getAdapter('file')->save('cache_data', $cacheData);
$storage->getAdapter('database')->save('persistent_data', $persistentData);
```

### 4. 动态切换默认适配器

```php
$storage = new StorageManager($config);

// 查看当前默认适配器
echo "当前默认适配器: " . $storage->getDefaultAdapterName();

// 切换到数据库适配器
$storage->setDefaultAdapter('database');

// 后续操作都将使用数据库存储
$storage->save('key', $data); // 存储到数据库
```

## ⚙️ 配置选项

### 环境变量配置

```env
# 存储策略配置
AGENT_STORAGE_DEFAULT=file
AGENT_STORAGE_FILE_PATH=/var/lib/agent/storage

# 数据库存储配置
AGENT_STORAGE_DB_DSN=mysql:host=localhost;dbname=agent_db
AGENT_STORAGE_DB_USERNAME=db_user
AGENT_STORAGE_DB_PASSWORD=db_pass
```

### 配置文件格式

```php
'storage' => [
    'default' => 'file',  // 默认存储策略
    
    // 文件存储配置
    'file' => [
        'base_path' => '/tmp/agent_storage',
        'extension' => '.json'
    ],
    
    // 数据库存储配置
    'database' => [
        'dsn' => 'sqlite:/tmp/agent.db',
        'username' => null,
        'password' => null,
        'table_name' => 'agent_storage'
    ]
]
```

## 🔄 数据迁移

### 在适配器间迁移数据

```php
// 创建源和目标存储
$sourceStorage = new StorageManager(['default' => 'file']);
$targetStorage = new StorageManager(['default' => 'database']);

// 迁移所有数据
$migratedCount = $sourceStorage->migrate('file', 'database');

// 迁移指定键
$specificKeys = ['user:123:data', 'user:456:data'];
$migratedCount = $sourceStorage->migrate('file', 'database', $specificKeys);
```

### 存储管理器内置迁移

```php
$storageManager = new StorageManager($config);

// 迁移数据
$migrated = $storageManager->migrate('file', 'database', ['user:*']);
echo "迁移了 {$migrated} 个键";
```

## 📊 存储统计

### 获取存储使用情况

```php
$storage = new StorageManager($config);

// 获取默认适配器统计
$stats = $storage->getStats();
print_r($stats);

// 获取特定适配器统计
$fileStats = $storage->getStats('file');
$dbStats = $storage->getStats('database');

// 获取所有适配器统计
$allStats = $storage->getStats(null);
```

### 统计信息示例

**文件存储统计:**
```php
[
    'adapter' => 'file',
    'file_count' => 150,
    'total_size' => 1048576,
    'human_readable_size' => '1.00 MB',
    'base_path' => '/tmp/agent_storage'
]
```

**数据库存储统计:**
```php
[
    'adapter' => 'database',
    'total_records' => 200,
    'valid_records' => 180,
    'expired_records' => 20,
    'table_size_mb' => 2.5,
    'table_name' => 'agent_storage'
]
```

## 🔒 多用户场景集成

### 用户数据隔离

```php
use App\Agent\MultiUser\MultiUserAgent;

// 为不同用户创建隔离的存储
$user1Agent = new MultiUserAgent('user_001', $preferences);
$user2Agent = new MultiUserAgent('user_002', $preferences);

// 各自的存储完全隔离
$user1Agent->getStorageManager()->save('personal_data', $data1);
$user2Agent->getStorageManager()->save('personal_data', $data2); // 不会冲突
```

### 存储策略个性化

```php
// 不同用户使用不同的存储策略
$userConfig1 = [
    'storage' => ['default' => 'file']  // 用户1使用文件存储
];

$userConfig2 = [
    'storage' => ['default' => 'database']  // 用户2使用数据库存储
];

$user1Agent = new MultiUserAgent('user_001', $userConfig1);
$user2Agent = new MultiUserAgent('user_002', $userConfig2);
```

## 🛠️ 扩展自定义适配器

### 创建新的存储适配器

```php
use App\Agent\Storage\StorageAdapterInterface;

class RedisStorageAdapter implements StorageAdapterInterface
{
    private $redis;
    
    public function __construct($host = '127.0.0.1', $port = 6379)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);
    }
    
    public function save($key, $data, $ttl = null)
    {
        $jsonData = json_encode($data);
        if ($ttl) {
            return $this->redis->setex($key, $ttl, $jsonData);
        }
        return $this->redis->set($key, $jsonData);
    }
    
    public function load($key)
    {
        $data = $this->redis->get($key);
        return $data ? json_decode($data, true) : null;
    }
    
    // ... 实现其他接口方法
    
    public function getName()
    {
        return 'redis';
    }
}

// 注册自定义适配器
$storage = new StorageManager($config);
$storage->registerAdapter('redis', new RedisStorageAdapter());
```

## 🎯 最佳实践

### 1. 性能优化
- **热数据**: 使用文件或Redis存储
- **冷数据**: 使用数据库长期存储
- **缓存层**: 在文件存储前增加内存缓存

### 2. 数据安全
- 敏感数据加密存储
- 定期备份重要数据
- 实施访问控制策略

### 3. 监控告警
```php
// 定期检查存储健康状态
$stats = $storage->getStats();
if ($stats['file_count'] > 10000) {
    // 发送告警：文件数量过多
}

if ($stats['total_size'] > 1073741824) {  // 1GB
    // 发送告警：存储空间不足
}
```

### 4. 故障恢复
```php
// 实现存储降级策略
try {
    $storage->getAdapter('database')->save($key, $data);
} catch (Exception $e) {
    // 降级到文件存储
    $storage->getAdapter('file')->save($key, $data);
    // 记录降级日志
}
```

这个存储策略系统为 Agent 框架提供了企业级的存储灵活性，既保持了开发的简便性，又具备了生产环境所需的可靠性和可扩展性。