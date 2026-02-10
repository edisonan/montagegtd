# 多用户 Agent 框架适配指南

## 🎯 适配目标

将单用户 Agent 框架改造为支持多用户安全隔离的企业级应用框架。

## 🔧 主要改进点

### 1. 数据隔离机制
- **会话隔离**: 每个用户拥有独立的会话存储目录
- **记忆隔离**: 用户记忆数据完全分离
- **断点隔离**: 执行状态快照用户专用
- **日志隔离**: 审计日志按用户分割

### 2. 配置灵活性
- **用户级配置**: 支持每个用户独立的模型偏好
- **API 密钥管理**: 不同用户的 API 凭据安全隔离
- **运行时配置**: 动态调整用户特定参数

### 3. 安全保障
- **访问控制**: 防止用户间数据交叉访问
- **并发安全**: 文件操作加锁防止竞争条件
- **审计追踪**: 完整的用户行为日志记录

## 📁 目录结构变化

```
app/Agent/
├── src/
│   ├── MultiUser/                 # 新增多用户支持模块
│   │   ├── MultiUserAgent.php     # 多用户 Agent 适配器
│   │   └── MultiUserAgentFactory.php # 工厂类
│   └── ... (原有模块)
├── examples/
│   └── multiuser_demo.php         # 多用户使用示例
└── ... (其他文件)
```

## 🚀 使用方式对比

### 单用户模式（原有）
```php
use App\Agent\Agent;

$agent = Agent::create([
    'model' => 'openai/gpt-4',
    'api_key' => getenv('OPENAI_API_KEY')
]);

$result = $agent->run("Hello world");
```

### 多用户模式（新增）
```php
use App\Agent\MultiUser\MultiUserAgentFactory;

// 为不同用户创建独立实例
$user1Agent = MultiUserAgentFactory::createForUser('user_001', [
    'llm' => [
        'model' => 'openai/gpt-4',
        'api_key' => 'user1-api-key'
    ]
]);

$user2Agent = MultiUserAgentFactory::createForUser('user_002', [
    'llm' => [
        'model' => 'anthropic/claude-3-opus', 
        'api_key' => 'user2-api-key'
    ]
]);

// 各自独立执行，数据完全隔离
$result1 = $user1Agent->run("任务1");
$result2 = $user2Agent->run("任务2");
```

## 🔒 安全特性

### 1. 存储路径隔离
```
原路径: /tmp/agent_sessions/
新路径: /tmp/agent_sessions/user_001/
        /tmp/agent_sessions/user_002/
```

### 2. 配置继承机制
```php
// 全局配置 + 用户配置 = 最终配置
$scopedConfig = array_merge($globalConfig, $userSpecificConfig);
```

### 3. 访问审计
```php
// 自动记录用户活动
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'user_id' => $userId,
    'activity_type' => 'task_execution',
    'details' => ['task' => $taskDescription]
];
```

## 📊 性能考虑

### 1. 实例管理
- 支持单例模式减少重复初始化
- 按需创建和销毁用户实例
- 内存使用优化

### 2. 存储优化
- 用户数据分区存储
- 定期清理过期数据
- 磁盘空间监控

## 🛠️ 部署建议

### 1. 环境配置
```bash
# 设置基础存储目录权限
mkdir -p /var/lib/agent/{sessions,memories,checkpoints}
chmod 755 /var/lib/agent
chown www-data:www-data /var/lib/agent
```

### 2. 配置文件
```php
// config/agent_multiuser.php
return [
    'storage_base_path' => '/var/lib/agent',
    'max_users' => 10000,
    'cleanup_policy' => [
        'session_max_age' => 30, // 天
        'memory_max_age' => 90,  // 天
        'log_max_size' => '100MB'
    ]
];
```

### 3. 监控告警
```php
// 添加健康检查端点
Route::get('/health/agent-multiuser', function() {
    $stats = MultiUserAgentFactory::getStatistics();
    return response()->json([
        'status' => 'healthy',
        'active_users' => $stats['active_users'],
        'disk_usage' => $this->getDiskUsage()
    ]);
});
```

## ⚠️ 注意事项

### 1. 资源限制
- 合理设置每个用户的存储配额
- 监控并发用户数量
- 防止恶意用户消耗过多资源

### 2. 安全防护
- API 密钥加密存储
- 请求频率限制
- 异常行为检测

### 3. 数据备份
- 定期备份用户重要数据
- 实现数据恢复机制
- 灾难恢复预案

## 🎯 适用场景

1. **企业内部 AI 平台** - 为员工提供个性化 AI 助手
2. **SaaS 服务平台** - 多租户 AI 应用服务
3. **教育培训机构** - 学生个性化学习助手
4. **客服系统** - 多客服人员的智能辅助

这个多用户适配方案既保持了原有框架的强大功能，又增加了企业级应用所需的安全性和可管理性。