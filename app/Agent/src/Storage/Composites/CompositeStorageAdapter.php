<?php

namespace App\Agent\Storage\Composites;

use App\Agent\Storage\StorageAdapterInterface;

/**
 * 组合存储适配器
 * 支持多个存储适配器的组合使用（读写分离、备份等）
 */
class CompositeStorageAdapter implements StorageAdapterInterface
{
    private $adapters;
    private $strategy;
    private $name;

    // 策略常量
    const STRATEGY_CHAIN = 'chain';      // 链式调用
    const STRATEGY_REPLICATE = 'replicate'; // 复制到所有适配器
    const STRATEGY_FAILOVER = 'failover';  // 故障转移
    const STRATEGY_READ_WRITE_SPLIT = 'read_write_split'; // 读写分离

    /**
     * 构造函数
     *
     * @param array $adapters 适配器数组 ['name' => adapter_instance]
     * @param string $strategy 使用策略
     * @param string $name 适配器名称
     */
    public function __construct($adapters, $strategy = self::STRATEGY_CHAIN, $name = 'composite')
    {
        $this->adapters = $adapters;
        $this->strategy = $strategy;
        $this->name = $name;
        
        if (empty($adapters)) {
            throw new \InvalidArgumentException('At least one adapter is required');
        }
    }

    public function save($key, $data, $ttl = null)
    {
        switch ($this->strategy) {
            case self::STRATEGY_REPLICATE:
                return $this->saveToAll($key, $data, $ttl);
                
            case self::STRATEGY_FAILOVER:
                return $this->saveWithFailover($key, $data, $ttl);
                
            case self::STRATEGY_READ_WRITE_SPLIT:
                // 写操作发送到所有写适配器
                return $this->saveToWriteAdapters($key, $data, $ttl);
                
            case self::STRATEGY_CHAIN:
            default:
                return $this->saveChain($key, $data, $ttl);
        }
    }

    public function load($key)
    {
        switch ($this->strategy) {
            case self::STRATEGY_FAILOVER:
                return $this->loadWithFailover($key);
                
            case self::STRATEGY_READ_WRITE_SPLIT:
                // 从读适配器读取
                return $this->loadFromReadAdapters($key);
                
            case self::STRATEGY_CHAIN:
            case self::STRATEGY_REPLICATE:
            default:
                return $this->loadChain($key);
        }
    }

    public function delete($key)
    {
        $success = true;
        foreach ($this->adapters as $adapter) {
            if (!$adapter->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    public function exists($key)
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->exists($key)) {
                return true;
            }
        }
        return false;
    }

    public function keys()
    {
        $allKeys = [];
        foreach ($this->adapters as $adapter) {
            $keys = $adapter->keys();
            $allKeys = array_unique(array_merge($allKeys, $keys));
        }
        return $allKeys;
    }

    public function clear()
    {
        $success = true;
        foreach ($this->adapters as $adapter) {
            if (!$adapter->clear()) {
                $success = false;
            }
        }
        return $success;
    }

    public function getStats()
    {
        $stats = [
            'adapter' => $this->getName(),
            'strategy' => $this->strategy,
            'adapters' => []
        ];
        
        foreach ($this->adapters as $name => $adapter) {
            $stats['adapters'][$name] = $adapter->getStats();
        }
        
        return $stats;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * 链式保存
     */
    private function saveChain($key, $data, $ttl)
    {
        $lastResult = false;
        foreach ($this->adapters as $adapter) {
            $lastResult = $adapter->save($key, $data, $ttl);
        }
        return $lastResult;
    }

    /**
     * 保存到所有适配器
     */
    private function saveToAll($key, $data, $ttl)
    {
        $success = true;
        foreach ($this->adapters as $adapter) {
            if (!$adapter->save($key, $data, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * 带故障转移的保存
     */
    private function saveWithFailover($key, $data, $ttl)
    {
        foreach ($this->adapters as $adapter) {
            try {
                if ($adapter->save($key, $data, $ttl)) {
                    return true;
                }
            } catch (\Exception $e) {
                // 继续尝试下一个适配器
                error_log("CompositeStorage save failed on adapter: " . $e->getMessage());
            }
        }
        return false;
    }

    /**
     * 链式读取
     */
    private function loadChain($key)
    {
        foreach ($this->adapters as $adapter) {
            $data = $adapter->load($key);
            if ($data !== null) {
                return $data;
            }
        }
        return null;
    }

    /**
     * 带故障转移的读取
     */
    private function loadWithFailover($key)
    {
        foreach ($this->adapters as $adapter) {
            try {
                $data = $adapter->load($key);
                if ($data !== null) {
                    return $data;
                }
            } catch (\Exception $e) {
                // 继续尝试下一个适配器
                error_log("CompositeStorage load failed on adapter: " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * 保存到写适配器
     */
    private function saveToWriteAdapters($key, $data, $ttl)
    {
        $success = true;
        foreach ($this->adapters as $name => $adapter) {
            // 假设以 'write_' 开头的适配器是写适配器
            if (strpos($name, 'write_') === 0) {
                if (!$adapter->save($key, $data, $ttl)) {
                    $success = false;
                }
            }
        }
        return $success;
    }

    /**
     * 从读适配器读取
     */
    private function loadFromReadAdapters($key)
    {
        // 假设以 'read_' 开头的适配器是读适配器
        foreach ($this->adapters as $name => $adapter) {
            if (strpos($name, 'read_') === 0) {
                $data = $adapter->load($key);
                if ($data !== null) {
                    return $data;
                }
            }
        }
        return null;
    }

    /**
     * 添加适配器
     */
    public function addAdapter($name, StorageAdapterInterface $adapter)
    {
        $this->adapters[$name] = $adapter;
    }

    /**
     * 移除适配器
     */
    public function removeAdapter($name)
    {
        unset($this->adapters[$name]);
    }

    /**
     * 获取适配器列表
     */
    public function getAdapters()
    {
        return $this->adapters;
    }
}