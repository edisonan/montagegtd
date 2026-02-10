<?php

namespace App\Agent\MultiUser;

use App\Agent\Agent as BaseAgent;
use App\Agent\Core\Config;
use App\Agent\Core\LLM\Client as LlmClient;
use App\Agent\Session\SessionManager;
use App\Agent\Memory\MemoryManager;
use App\Agent\Checkpoint\CheckpointStorage;

/**
 * 多用户 Agent 适配器
 * 
 * 为多用户场景提供安全的隔离机制
 */
class MultiUserAgent
{
    private $userId;
    private $agent;
    private $userConfig;
    private $scopedPaths;

    /**
     * 构造函数
     *
     * @param string $userId 用户唯一标识
     * @param array $userPreferences 用户个性化配置
     * @param array $globalConfig 全局配置（可选）
     */
    public function __construct($userId, $userPreferences = [], $globalConfig = [])
    {
        $this->userId = $userId;
        $this->userConfig = $userPreferences;
        
        // 创建用户作用域的配置
        $scopedConfig = $this->createScopedConfiguration($globalConfig);
        
        // 初始化各个管理器
        $this->initializeManagers($scopedConfig);
        
        // 创建基础 Agent
        $config = new Config($scopedConfig);
        $llmClient = new LlmClient($config->getLlmConfig());
        $this->agent = new \App\Agent\Core\Agent($llmClient, $config);
    }

    /**
     * 创建用户作用域的配置
     *
     * @param array $globalConfig 全局配置
     * @return array 作用域配置
     */
    private function createScopedConfiguration($globalConfig)
    {
        // 基础配置继承
        $scopedConfig = $globalConfig;
        
        // 用户特定的 LLM 配置
        if (isset($this->userConfig['llm'])) {
            $scopedConfig['llm'] = array_merge(
                $scopedConfig['llm'] ?? [],
                $this->userConfig['llm']
            );
        }
        
        // 用户隔离的存储路径
        $userScope = "user_{$this->userId}";
        
        $scopedConfig['session']['storage_path'] = 
            ($globalConfig['session']['storage_path'] ?? sys_get_temp_dir() . '/agent_sessions') 
            . '/' . $userScope;
            
        $scopedConfig['memory']['storage_path'] = 
            ($globalConfig['memory']['storage_path'] ?? sys_get_temp_dir() . '/agent_memories') 
            . '/' . $userScope;
            
        $scopedConfig['checkpoint']['storage_path'] = 
            ($globalConfig['checkpoint']['storage_path'] ?? sys_get_temp_dir() . '/agent_checkpoints') 
            . '/' . $userScope;
        
        // 用户特定的日志路径
        $scopedConfig['logging']['file_path'] = 
            ($globalConfig['logging']['file_path'] ?? sys_get_temp_dir() . '/agent.log')
            . '.' . $userScope;
            
        return $scopedConfig;
    }

    /**
     * 初始化用户作用域的管理器
     *
     * @param array $scopedConfig 作用域配置
     */
    private function initializeManagers($scopedConfig)
    {
        // 确保存储目录存在
        $directories = [
            $scopedConfig['session']['storage_path'],
            $scopedConfig['memory']['storage_path'],
            $scopedConfig['checkpoint']['storage_path'],
            dirname($scopedConfig['logging']['file_path'])
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        $this->scopedPaths = [
            'session' => $scopedConfig['session']['storage_path'],
            'memory' => $scopedConfig['memory']['storage_path'],
            'checkpoint' => $scopedConfig['checkpoint']['storage_path'],
            'log' => $scopedConfig['logging']['file_path']
        ];
    }

    /**
     * 初始化存储管理器
     *
     * @param array $scopedConfig 作用域配置
     */
    private function initializeStorageManager($scopedConfig)
    {
        // 创建用户专属的存储配置
        $userStorageConfig = [
            'default' => $scopedConfig['storage']['default'] ?? 'file',
            'file' => [
                'base_path' => $this->scopedPaths['memory'], // 使用记忆路径作为文件存储根目录
                'extension' => '.json'
            ]
        ];
        
        // 如果配置了数据库存储，添加数据库配置
        if (isset($scopedConfig['storage']['database'])) {
            $userStorageConfig['database'] = $scopedConfig['storage']['database'];
            // 修改表名包含用户ID
            $userStorageConfig['database']['table_name'] = 
                ($scopedConfig['storage']['database']['table_name'] ?? 'agent_storage') . '_' . $this->userId;
        }
        
        $this->storageManager = new StorageManager($userStorageConfig);
    }

    /**
     * 执行 Agent 任务
     *
     * @param string $task 任务描述
     * @param array $options 执行选项
     * @return mixed 执行结果
     */
    public function run($task, $options = [])
    {
        // 添加用户上下文到会话
        $sessionContext = [
            'user_id' => $this->userId,
            'user_preferences' => $this->userConfig,
            'execution_time' => time()
        ];
        
        // 可以在这里添加审计日志
        $this->logUserActivity('task_execution', [
            'task' => $task,
            'options' => $options
        ]);
        
        try {
            $result = $this->agent->run($task, $options);
            
            // 记录成功执行
            $this->logUserActivity('task_success', [
                'task' => $task,
                'result_length' => is_string($result) ? strlen($result) : 'non-string'
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            // 记录执行失败
            $this->logUserActivity('task_failure', [
                'task' => $task,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * 获取用户会话管理器
     *
     * @return SessionManager
     */
    public function getSessionManager()
    {
        return new SessionManager(
            $this->scopedPaths['session'] . '/sessions.json',
            true
        );
    }

    /**
     * 获取用户存储管理器
     *
     * @return StorageManager
     */
    public function getStorageManager()
    {
        return $this->storageManager;
    }

    /**
     * 获取用户记忆管理器
     *
     * @return MemoryManager
     */
    public function getMemoryManager()
    {
        $config = new Config(['memory' => ['storage_path' => $this->scopedPaths['memory']]]);
        return new MemoryManager($config);
    }

    /**
     * 获取用户断点存储
     *
     * @return CheckpointStorage
     */
    public function getCheckpointStorage()
    {
        $config = new Config(['checkpoint' => ['storage_path' => $this->scopedPaths['checkpoint']]]);
        return new CheckpointStorage($config);
    }

    /**
     * 获取用户ID
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 获取用户配置信息
     *
     * @return array
     */
    public function getUserConfig()
    {
        return $this->userConfig;
    }

    /**
     * 获取作用域路径信息
     *
     * @return array
     */
    public function getScopedPaths()
    {
        return $this->scopedPaths;
    }

    /**
     * 记录用户活动日志
     *
     * @param string $activityType 活动类型
     * @param array $details 活动详情
     */
    private function logUserActivity($activityType, $details = [])
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $this->userId,
            'activity_type' => $activityType,
            'details' => $details
        ];
        
        $logMessage = json_encode($logEntry, JSON_UNESCAPED_UNICODE);
        file_put_contents(
            $this->scopedPaths['log'], 
            $logMessage . PHP_EOL, 
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * 清理用户数据
     *
     * @param bool $preserveLogs 是否保留日志
     */
    public function cleanupUserData($preserveLogs = true)
    {
        $directories = [
            $this->scopedPaths['session'],
            $this->scopedPaths['memory'],
            $this->scopedPaths['checkpoint']
        ];
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->removeDirectory($dir);
            }
        }
        
        // 可选地清理日志
        if (!$preserveLogs && file_exists($this->scopedPaths['log'])) {
            unlink($this->scopedPaths['log']);
        }
    }

    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * 魔术方法代理到基础 Agent
     *
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->agent, $method)) {
            return call_user_func_array([$this->agent, $method], $args);
        }
        
        throw new \BadMethodCallException("Method {$method} not found");
    }
}