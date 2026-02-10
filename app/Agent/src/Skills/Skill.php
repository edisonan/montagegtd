<?php

namespace Agent\Skills;

use Agent\Core\AgentContext;
use Exception;

/**
 * 技能基类
 * 所有自定义技能必须继承此类
 */
abstract class Skill
{
    protected $name;
    protected $description;
    protected $version;
    protected $author;
    protected $enabled;
    protected $requiredTools;
    protected $config;

    /**
     * 构造函数
     *
     * @param string $name 技能名称
     * @param string $description 技能描述
     * @param array $config 配置参数
     */
    public function __construct($name, $description = '', $config = array())
    {
        $this->name = $name;
        $this->description = $description;
        $this->version = '1.0.0';
        $this->author = 'Unknown';
        $this->enabled = true;
        $this->requiredTools = array();
        $this->config = $config;
    }

    /**
     * 获取技能名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取技能描述
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * 获取版本号
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 设置版本号
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * 获取作者信息
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * 设置作者信息
     *
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * 检查技能是否启用
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * 启用技能
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * 禁用技能
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * 获取所需工具列表
     *
     * @return array
     */
    public function getRequiredTools()
    {
        return $this->requiredTools;
    }

    /**
     * 设置所需工具
     *
     * @param array $tools
     */
    public function setRequiredTools($tools)
    {
        $this->requiredTools = $tools;
    }

    /**
     * 获取配置参数
     *
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    /**
     * 设置配置参数
     *
     * @param string $key 配置键名
     * @param mixed $value 配置值
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * 技能初始化方法
     * 在技能被加载时调用
     *
     * @param AgentContext $context Agent 上下文
     * @return bool 初始化是否成功
     */
    public function initialize($context)
    {
        // 子类可重写此方法
        return true;
    }

    /**
     * 技能主执行方法
     * 这是技能的核心逻辑
     *
     * @param AgentContext $context Agent 上下文
     * @param array $parameters 执行参数
     * @return mixed 执行结果
     * @throws Exception 执行异常
     */
    abstract public function execute($context, $parameters = array());

    /**
     * 技能清理方法
     * 在技能卸载时调用
     *
     * @param AgentContext $context Agent 上下文
     * @return void
     */
    public function cleanup($context)
    {
        // 子类可重写此方法
    }

    /**
     * 验证执行参数
     *
     * @param array $parameters 参数数组
     * @return bool 验证是否通过
     */
    public function validateParameters($parameters)
    {
        // 子类可重写此方法实现具体验证逻辑
        return is_array($parameters);
    }

    /**
     * 获取技能元数据
     *
     * @return array
     */
    public function getMetadata()
    {
        return array(
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'author' => $this->author,
            'enabled' => $this->enabled,
            'required_tools' => $this->requiredTools,
            'config_keys' => array_keys($this->config)
        );
    }

    /**
     * 检查技能依赖
     *
     * @param array $availableTools 可用工具列表
     * @return array 缺失的依赖工具
     */
    public function checkDependencies($availableTools)
    {
        $missingTools = array();
        
        foreach ($this->requiredTools as $requiredTool) {
            if (!in_array($requiredTool, $availableTools)) {
                $missingTools[] = $requiredTool;
            }
        }
        
        return $missingTools;
    }

    /**
     * 获取技能使用说明
     *
     * @return string
     */
    public function getUsageInstructions()
    {
        return "使用方法请参考具体技能实现";
    }

    /**
     * 记录技能日志
     *
     * @param string $message 日志消息
     * @param string $level 日志级别
     */
    protected function log($message, $level = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$this->name}] [{$level}] {$message}";
        error_log($logMessage);
    }
}