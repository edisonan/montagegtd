<?php

namespace App\Agent\MultiUser;

/**
 * 多用户 Agent 工厂类
 * 
 * 简化多用户 Agent 的创建和管理
 */
class MultiUserAgentFactory
{
    private static $instances = [];
    private static $globalConfig = [];

    /**
     * 设置全局配置
     *
     * @param array $config 全局配置
     */
    public static function setGlobalConfig($config)
    {
        self::$globalConfig = $config;
    }

    /**
     * 为用户创建 Agent 实例
     *
     * @param string $userId 用户ID
     * @param array $userPreferences 用户个性化配置
     * @param bool $singleton 是否使用单例模式
     * @return MultiUserAgent
     */
    public static function createForUser($userId, $userPreferences = [], $singleton = true)
    {
        $instanceKey = "user_{$userId}";
        
        if ($singleton && isset(self::$instances[$instanceKey])) {
            return self::$instances[$instanceKey];
        }
        
        $agent = new MultiUserAgent($userId, $userPreferences, self::$globalConfig);
        
        if ($singleton) {
            self::$instances[$instanceKey] = $agent;
        }
        
        return $agent;
    }

    /**
     * 获取已存在的用户 Agent 实例
     *
     * @param string $userId 用户ID
     * @return MultiUserAgent|null
     */
    public static function getForUser($userId)
    {
        $instanceKey = "user_{$userId}";
        return isset(self::$instances[$instanceKey]) ? self::$instances[$instanceKey] : null;
    }

    /**
     * 销毁用户 Agent 实例
     *
     * @param string $userId 用户ID
     */
    public static function destroyForUser($userId)
    {
        $instanceKey = "user_{$userId}";
        if (isset(self::$instances[$instanceKey])) {
            unset(self::$instances[$instanceKey]);
        }
    }

    /**
     * 批量创建用户 Agent 实例
     *
     * @param array $users 用户列表，格式: [['id' => 'user1', 'preferences' => [...]], ...]
     * @return array 用户 Agent 实例映射
     */
    public static function createBatch($users)
    {
        $agents = [];
        foreach ($users as $userData) {
            $userId = $userData['id'];
            $preferences = $userData['preferences'] ?? [];
            $agents[$userId] = self::createForUser($userId, $preferences);
        }
        return $agents;
    }

    /**
     * 获取所有活跃的用户实例
     *
     * @return array
     */
    public static function getAllInstances()
    {
        return self::$instances;
    }

    /**
     * 清理所有实例
     */
    public static function clearAllInstances()
    {
        self::$instances = [];
    }

    /**
     * 获取用户统计数据
     *
     * @return array
     */
    public static function getStatistics()
    {
        $stats = [
            'active_users' => count(self::$instances),
            'user_list' => array_keys(self::$instances),
            'global_config_set' => !empty(self::$globalConfig)
        ];
        
        return $stats;
    }
}