<?php

namespace App\Agent;

/**
 * 主入口类 - PHP Agent Framework
 * 
 * 这是整个 Agent 框架的核心入口点，提供了便捷的静态方法来创建和使用 Agent。
 * 
 * 使用示例:
 * ```php
 * use App\Agent\Agent;
 * 
 * // 快速创建 Agent
 * $agent = Agent::create([
 *     'model' => 'openai/gpt-4',
 *     'api_key' => getenv('OPENAI_API_KEY')
 * ]);
 * 
 * // 执行任务
 * $result = $agent->run('帮我写一个快速排序算法');
 * echo $result;
 * ```
 */
class Agent
{
    /**
     * 框架版本
     */
    public const VERSION = '0.1.0';

    /**
     * 快速创建 Agent 实例
     *
     * @param array $config 配置参数
     * @return Core\Agent
     */
    public static function create(array $config = []): Core\Agent
    {
        $llmClient = new Core\LLM\Client($config);
        $configManager = new Core\Config($config);
        
        return new Core\Agent($llmClient, $configManager);
    }

    /**
     * 创建默认配置的 Agent
     *
     * @return Core\Agent
     */
    public static function createDefault(): Core\Agent
    {
        return self::create();
    }

    /**
     * 获取框架版本
     *
     * @return string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }
}