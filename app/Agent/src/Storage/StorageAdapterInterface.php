<?php

namespace App\Agent\Storage;

/**
 * 存储适配器接口
 * 定义所有存储适配器必须实现的方法
 */
interface StorageAdapterInterface
{
    /**
     * 保存数据
     *
     * @param string $key 键名
     * @param mixed $data 数据
     * @param int|null $ttl 过期时间（秒）
     * @return bool 是否成功
     */
    public function save($key, $data, $ttl = null);

    /**
     * 读取数据
     *
     * @param string $key 键名
     * @return mixed|null 数据，不存在时返回null
     */
    public function load($key);

    /**
     * 删除数据
     *
     * @param string $key 键名
     * @return bool 是否成功
     */
    public function delete($key);

    /**
     * 检查键是否存在
     *
     * @param string $key 键名
     * @return bool
     */
    public function exists($key);

    /**
     * 获取所有键
     *
     * @return array
     */
    public function keys();

    /**
     * 清空所有数据
     *
     * @return bool 是否成功
     */
    public function clear();

    /**
     * 获取存储统计信息
     *
     * @return array
     */
    public function getStats();

    /**
     * 获取适配器名称
     *
     * @return string
     */
    public function getName();
}