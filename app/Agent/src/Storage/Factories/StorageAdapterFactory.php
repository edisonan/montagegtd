<?php

namespace App\Agent\Storage\Factories;

use App\Agent\Storage\StorageAdapterInterface;
use App\Agent\Storage\Adapters\FileStorageAdapter;
use App\Agent\Storage\Adapters\DatabaseStorageAdapter;
use App\Agent\Storage\Adapters\GenericStorageAdapter;
use App\Agent\Storage\Composites\CompositeStorageAdapter;

/**
 * 存储适配器工厂
 * 简化适配器创建过程
 */
class StorageAdapterFactory
{
    /**
     * 创建文件存储适配器
     *
     * @param string|null $basePath 基础路径
     * @param string $extension 文件扩展名
     * @return FileStorageAdapter
     */
    public static function createFileAdapter($basePath = null, $extension = '.json')
    {
        return new FileStorageAdapter($basePath, $extension);
    }

    /**
     * 创建数据库存储适配器
     *
     * @param string $dsn 数据源名称
     * @param string|null $username 用户名
     * @param string|null $password 密码
     * @param string $tableName 表名
     * @return DatabaseStorageAdapter
     */
    public static function createDatabaseAdapter($dsn, $username = null, $password = null, $tableName = 'agent_storage')
    {
        return new DatabaseStorageAdapter($dsn, $username, $password, $tableName);
    }

    /**
     * 创建通用存储适配器
     *
     * @param object $dataSource 数据源对象
     * @param array $methodMapping 方法映射
     * @param string $name 适配器名称
     * @return GenericStorageAdapter
     */
    public static function createGenericAdapter($dataSource, $methodMapping = [], $name = 'generic')
    {
        return new GenericStorageAdapter($dataSource, $methodMapping, $name);
    }

    /**
     * 创建组合存储适配器
     *
     * @param array $adapters 适配器数组
     * @param string $strategy 策略
     * @param string $name 适配器名称
     * @return CompositeStorageAdapter
     */
    public static function createCompositeAdapter($adapters, $strategy = CompositeStorageAdapter::STRATEGY_CHAIN, $name = 'composite')
    {
        return new CompositeStorageAdapter($adapters, $strategy, $name);
    }

    /**
     * 从配置创建适配器
     *
     * @param array $config 配置数组
     * @return StorageAdapterInterface
     */
    public static function createFromConfig($config)
    {
        $type = $config['type'] ?? 'file';
        
        switch ($type) {
            case 'file':
                return self::createFileAdapter(
                    $config['base_path'] ?? null,
                    $config['extension'] ?? '.json'
                );
                
            case 'database':
                return self::createDatabaseAdapter(
                    $config['dsn'],
                    $config['username'] ?? null,
                    $config['password'] ?? null,
                    $config['table_name'] ?? 'agent_storage'
                );
                
            case 'generic':
                return self::createGenericAdapter(
                    $config['data_source'],
                    $config['method_mapping'] ?? [],
                    $config['name'] ?? 'generic'
                );
                
            case 'composite':
                $adapters = [];
                foreach ($config['adapters'] as $name => $adapterConfig) {
                    $adapters[$name] = self::createFromConfig($adapterConfig);
                }
                return self::createCompositeAdapter(
                    $adapters,
                    $config['strategy'] ?? CompositeStorageAdapter::STRATEGY_CHAIN,
                    $config['name'] ?? 'composite'
                );
                
            default:
                throw new \InvalidArgumentException("Unsupported storage type: {$type}");
        }
    }

    /**
     * 创建高性能读写分离架构
     *
     * @param array $config 配置
     * @return CompositeStorageAdapter
     */
    public static function createReadWriteSplitArchitecture($config)
    {
        $readAdapters = [];
        $writeAdapters = [];
        
        // 创建读适配器（通常是高速缓存）
        if (isset($config['read'])) {
            foreach ($config['read'] as $i => $readConfig) {
                $readAdapters["read_{$i}"] = self::createFromConfig($readConfig);
            }
        }
        
        // 创建写适配器（通常是持久化存储）
        if (isset($config['write'])) {
            foreach ($config['write'] as $i => $writeConfig) {
                $writeAdapters["write_{$i}"] = self::createFromConfig($writeConfig);
            }
        }
        
        // 合并所有适配器
        $allAdapters = array_merge($readAdapters, $writeAdapters);
        
        return new CompositeStorageAdapter(
            $allAdapters,
            CompositeStorageAdapter::STRATEGY_READ_WRITE_SPLIT,
            'read_write_split'
        );
    }

    /**
     * 创建高可用备份架构
     *
     * @param array $primaryConfig 主存储配置
     * @param array $backupConfigs 备份存储配置数组
     * @return CompositeStorageAdapter
     */
    public static function createHighAvailabilityArchitecture($primaryConfig, $backupConfigs)
    {
        $adapters = [
            'primary' => self::createFromConfig($primaryConfig)
        ];
        
        foreach ($backupConfigs as $i => $backupConfig) {
            $adapters["backup_{$i}"] = self::createFromConfig($backupConfig);
        }
        
        return new CompositeStorageAdapter(
            $adapters,
            CompositeStorageAdapter::STRATEGY_FAILOVER,
            'high_availability'
        );
    }

    /**
     * 创建分层存储架构（热数据→温数据→冷数据）
     *
     * @param array $hotConfig 热数据存储配置（最快）
     * @param array $warmConfig 温数据存储配置（中等速度）
     * @param array $coldConfig 冷数据存储配置（最慢但便宜）
     * @return CompositeStorageAdapter
     */
    public static function createTieredStorageArchitecture($hotConfig, $warmConfig, $coldConfig)
    {
        $adapters = [
            'hot' => self::createFromConfig($hotConfig),    // 内存/SSD
            'warm' => self::createFromConfig($warmConfig),  // 普通硬盘
            'cold' => self::createFromConfig($coldConfig)   // 归档存储
        ];
        
        return new CompositeStorageAdapter(
            $adapters,
            CompositeStorageAdapter::STRATEGY_CHAIN,
            'tiered_storage'
        );
    }
}