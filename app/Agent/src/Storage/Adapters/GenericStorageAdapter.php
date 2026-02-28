<?php

namespace App\Agent\Storage\Adapters;

use App\Agent\Storage\StorageAdapterInterface;

/**
 * 通用数据源适配器
 * 支持任意PHP类作为数据源实现
 */
class GenericStorageAdapter implements StorageAdapterInterface
{
    private $dataSource;
    private $methods;
    private $name;

    /**
     * 构造函数
     *
     * @param object $dataSource 数据源对象（任意PHP类实例）
     * @param array $methodMapping 方法映射配置
     * @param string $name 适配器名称
     */
    public function __construct($dataSource, $methodMapping = [], $name = 'generic')
    {
        if (!is_object($dataSource)) {
            throw new \InvalidArgumentException('DataSource must be an object');
        }
        
        $this->dataSource = $dataSource;
        $this->name = $name;
        $this->methods = $this->buildMethodMapping($methodMapping);
    }

    /**
     * 构建方法映射
     *
     * @param array $customMapping 自定义映射
     * @return array
     */
    private function buildMethodMapping($customMapping)
    {
        $defaultMapping = [
            'save' => 'save',
            'load' => 'load', 
            'delete' => 'delete',
            'exists' => 'exists',
            'keys' => 'keys',
            'clear' => 'clear',
            'getStats' => 'getStats'
        ];
        
        return array_merge($defaultMapping, $customMapping);
    }

    /**
     * 调用数据源方法
     *
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    private function callDataSourceMethod($method, $args = [])
    {
        $mappedMethod = $this->methods[$method] ?? $method;
        
        if (!method_exists($this->dataSource, $mappedMethod)) {
            throw new \BadMethodCallException(
                "Method '{$mappedMethod}' not found in data source"
            );
        }
        
        return call_user_func_array([$this->dataSource, $mappedMethod], $args);
    }

    public function save($key, $data, $ttl = null)
    {
        return $this->callDataSourceMethod('save', [$key, $data, $ttl]);
    }

    public function load($key)
    {
        return $this->callDataSourceMethod('load', [$key]);
    }

    public function delete($key)
    {
        return $this->callDataSourceMethod('delete', [$key]);
    }

    public function exists($key)
    {
        return $this->callDataSourceMethod('exists', [$key]);
    }

    public function keys()
    {
        return $this->callDataSourceMethod('keys');
    }

    public function clear()
    {
        return $this->callDataSourceMethod('clear');
    }

    public function getStats()
    {
        return $this->callDataSourceMethod('getStats');
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取底层数据源
     *
     * @return object
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }
}