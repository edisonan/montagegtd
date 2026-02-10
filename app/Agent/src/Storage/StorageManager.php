<?php

namespace App\Agent\Storage;

use App\Agent\Storage\Adapters\FileStorageAdapter;
use App\Agent\Storage\Adapters\DatabaseStorageAdapter;
use App\Agent\Storage\Adapters\GenericStorageAdapter;
use App\Agent\Storage\Composites\CompositeStorageAdapter;
use App\Agent\Storage\Factories\StorageAdapterFactory;

/**
 * 存储管理器
 * 负责管理不同的存储适配器，提供统一的存储接口
 */
class StorageManager
{
    private $adapters = [];
    private $defaultAdapter;
    private $config;

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->initializeDefaultAdapter();
        $this->registerBuiltInAdapters();
    }

    /**
     * 初始化默认适配器
     */
    private function initializeDefaultAdapter()
    {
        $defaultType = $this->config['default'] ?? 'file';
        
        switch ($defaultType) {
            case 'database':
                $this->defaultAdapter = $this->createDatabaseAdapter();
                break;
            case 'file':
            default:
                $this->defaultAdapter = $this->createFileAdapter();
                break;
        }
    }

    /**
     * 注册内置适配器
     */
    private function registerBuiltInAdapters()
    {
        // 文件适配器始终可用
        $this->registerAdapter('file', $this->createFileAdapter());
        
        // 数据库适配器（如果配置了）
        if (isset($this->config['database'])) {
            $this->registerAdapter('database', $this->createDatabaseAdapter());
        }
        
        // 通用适配器（如果配置了）
        if (isset($this->config['generic'])) {
            $this->registerAdapter('generic', $this->createGenericAdapter());
        }
        
        // 组合适配器（如果配置了）
        if (isset($this->config['composite'])) {
            $this->registerAdapter('composite', $this->createCompositeAdapter());
        }
    }

    /**
     * 创建文件适配器
     *
     * @return FileStorageAdapter
     */
    private function createFileAdapter()
    {
        $basePath = $this->config['file']['base_path'] ?? sys_get_temp_dir() . '/agent_storage';
        $extension = $this->config['file']['extension'] ?? '.json';
        
        return new FileStorageAdapter($basePath, $extension);
    }

    /**
     * 创建数据库适配器
     *
     * @return DatabaseStorageAdapter
     */
    private function createDatabaseAdapter()
    {
        $dbConfig = $this->config['database'] ?? [];
        
        if (empty($dbConfig['dsn'])) {
            throw new \Exception("Database DSN is required for database storage adapter");
        }
        
        return new DatabaseStorageAdapter(
            $dbConfig['dsn'],
            $dbConfig['username'] ?? null,
            $dbConfig['password'] ?? null,
            $dbConfig['table_name'] ?? 'agent_storage'
        );
    }

    /**
     * 创建通用适配器
     *
     * @return GenericStorageAdapter
     */
    private function createGenericAdapter()
    {
        $genericConfig = $this->config['generic'] ?? [];
        
        if (empty($genericConfig['data_source'])) {
            throw new \Exception("Data source is required for generic storage adapter");
        }
        
        return new GenericStorageAdapter(
            $genericConfig['data_source'],
            $genericConfig['method_mapping'] ?? [],
            $genericConfig['name'] ?? 'generic'
        );
    }

    /**
     * 创建组合适配器
     *
     * @return CompositeStorageAdapter
     */
    private function createCompositeAdapter()
    {
        $compositeConfig = $this->config['composite'] ?? [];
        
        if (empty($compositeConfig['adapters'])) {
            throw new \Exception("Adapters configuration is required for composite storage adapter");
        }
        
        $adapters = [];
        foreach ($compositeConfig['adapters'] as $name => $adapterConfig) {
            $adapters[$name] = StorageAdapterFactory::createFromConfig($adapterConfig);
        }
        
        return new CompositeStorageAdapter(
            $adapters,
            $compositeConfig['strategy'] ?? CompositeStorageAdapter::STRATEGY_CHAIN,
            $compositeConfig['name'] ?? 'composite'
        );
    }

    /**
     * 注册存储适配器
     *
     * @param string $name 适配器名称
     * @param StorageAdapterInterface $adapter 适配器实例
     */
    public function registerAdapter($name, StorageAdapterInterface $adapter)
    {
        $this->adapters[$name] = $adapter;
    }

    /**
     * 获取存储适配器
     *
     * @param string|null $name 适配器名称，null表示使用默认适配器
     * @return StorageAdapterInterface
     */
    public function getAdapter($name = null)
    {
        if ($name === null) {
            return $this->defaultAdapter;
        }
        
        if (!isset($this->adapters[$name])) {
            throw new \Exception("Storage adapter '{$name}' not found");
        }
        
        return $this->adapters[$name];
    }

    /**
     * 检查适配器是否存在
     *
     * @param string $name 适配器名称
     * @return bool
     */
    public function hasAdapter($name)
    {
        return isset($this->adapters[$name]);
    }

    /**
     * 获取所有已注册的适配器
     *
     * @return array
     */
    public function getAdapters()
    {
        return $this->adapters;
    }

    /**
     * 保存数据（使用默认适配器）
     *
     * @param string $key 键名
     * @param mixed $data 数据
     * @param int|null $ttl 过期时间
     * @return bool
     */
    public function save($key, $data, $ttl = null)
    {
        return $this->defaultAdapter->save($key, $data, $ttl);
    }

    /**
     * 读取数据（使用默认适配器）
     *
     * @param string $key 键名
     * @return mixed|null
     */
    public function load($key)
    {
        return $this->defaultAdapter->load($key);
    }

    /**
     * 删除数据（使用默认适配器）
     *
     * @param string $key 键名
     * @return bool
     */
    public function delete($key)
    {
        return $this->defaultAdapter->delete($key);
    }

    /**
     * 检查键是否存在（使用默认适配器）
     *
     * @param string $key 键名
     * @return bool
     */
    public function exists($key)
    {
        return $this->defaultAdapter->exists($key);
    }

    /**
     * 获取所有键（使用默认适配器）
     *
     * @return array
     */
    public function keys()
    {
        return $this->defaultAdapter->keys();
    }

    /**
     * 清空所有数据（使用默认适配器）
     *
     * @return bool
     */
    public function clear()
    {
        return $this->defaultAdapter->clear();
    }

    /**
     * 获取存储统计信息
     *
     * @param string|null $adapterName 指定适配器，null表示所有适配器
     * @return array
     */
    public function getStats($adapterName = null)
    {
        if ($adapterName !== null) {
            $adapter = $this->getAdapter($adapterName);
            return $adapter->getStats();
        }
        
        // 获取所有适配器的统计信息
        $stats = [];
        foreach ($this->adapters as $name => $adapter) {
            $stats[$name] = $adapter->getStats();
        }
        
        return $stats;
    }

    /**
     * 获取默认适配器名称
     *
     * @return string
     */
    public function getDefaultAdapterName()
    {
        foreach ($this->adapters as $name => $adapter) {
            if ($adapter === $this->defaultAdapter) {
                return $name;
            }
        }
        return 'unknown';
    }

    /**
     * 切换默认适配器
     *
     * @param string $adapterName 适配器名称
     */
    public function setDefaultAdapter($adapterName)
    {
        if (!isset($this->adapters[$adapterName])) {
            throw new \Exception("Adapter '{$adapterName}' not registered");
        }
        
        $this->defaultAdapter = $this->adapters[$adapterName];
    }

    /**
     * 迁移数据从一个适配器到另一个适配器
     *
     * @param string $fromAdapter 源适配器
     * @param string $toAdapter 目标适配器
     * @param array|null $keys 指定键列表，null表示迁移所有键
     * @return int 迁移的键数量
     */
    public function migrate($fromAdapter, $toAdapter, $keys = null)
    {
        $from = $this->getAdapter($fromAdapter);
        $to = $this->getAdapter($toAdapter);
        
        if ($keys === null) {
            $keys = $from->keys();
        }
        
        $migratedCount = 0;
        foreach ($keys as $key) {
            $data = $from->load($key);
            if ($data !== null) {
                if ($to->save($key, $data)) {
                    $migratedCount++;
                }
            }
        }
        
        return $migratedCount;
    }
}