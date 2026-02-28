<?php

namespace Agent\Hooks;

use Agent\Core\AgentContext;
use Exception;

/**
 * Agent Hook 基类
 * 所有自定义 Hook 必须继承此类
 */
abstract class AgentHook
{
    protected $name;
    protected $priority;
    protected $enabled;

    /**
     * 构造函数
     *
     * @param string $name Hook 名称
     * @param int $priority 优先级（数字越小优先级越高）
     */
    public function __construct($name, $priority = 100)
    {
        $this->name = $name;
        $this->priority = $priority;
        $this->enabled = true;
    }

    /**
     * 获取 Hook 名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取优先级
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * 设置优先级
     *
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * 启用 Hook
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * 禁用 Hook
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * 检查是否启用
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Agent 初始化前调用
     *
     * @param AgentContext $context Agent 上下文
     * @return void
     */
    public function onAgentInitialize($context)
    {
        // 子类可重写此方法
    }

    /**
     * Agent 初始化后调用
     *
     * @param AgentContext $context Agent 上下文
     * @return void
     */
    public function onAgentInitialized($context)
    {
        // 子类可重写此方法
    }

    /**
     * 执行循环开始前调用
     *
     * @param AgentContext $context Agent 上下文
     * @param int $step 当前步骤
     * @return void
     */
    public function onLoopStart($context, $step)
    {
        // 子类可重写此方法
    }

    /**
     * 执行循环结束后调用
     *
     * @param AgentContext $context Agent 上下文
     * @param int $step 当前步骤
     * @param mixed $result 执行结果
     * @return void
     */
    public function onLoopEnd($context, $step, $result)
    {
        // 子类可重写此方法
    }

    /**
     * 工具调用前调用
     *
     * @param AgentContext $context Agent 上下文
     * @param string $toolName 工具名称
     * @param array $arguments 工具参数
     * @return array|null 修改后的参数，或 null 不修改
     */
    public function onToolCall($context, $toolName, $arguments)
    {
        // 子类可重写此方法
        return null; // 返回 null 表示不修改参数
    }

    /**
     * 工具调用后调用
     *
     * @param AgentContext $context Agent 上下文
     * @param string $toolName 工具名称
     * @param array $arguments 工具参数
     * @param mixed $result 工具执行结果
     * @return mixed 修改后的结果，或原结果
     */
    public function onToolResult($context, $toolName, $arguments, $result)
    {
        // 子类可重写此方法
        return $result;
    }

    /**
     * LLM 调用前调用
     *
     * @param AgentContext $context Agent 上下文
     * @param array $messages 消息列表
     * @param array $options LLM 选项
     * @return array|null 修改后的消息，或 null 不修改
     */
    public function onLlmCall($context, $messages, $options)
    {
        // 子类可重写此方法
        return null; // 返回 null 表示不修改消息
    }

    /**
     * LLM 调用后调用
     *
     * @param AgentContext $context Agent 上下文
     * @param array $messages 原始消息
     * @param array $options LLM 选项
     * @param mixed $response LLM 响应
     * @return mixed 修改后的响应，或原响应
     */
    public function onLlmResponse($context, $messages, $options, $response)
    {
        // 子类可重写此方法
        return $response;
    }

    /**
     * 决策制定前调用
     *
     * @param AgentContext $context Agent 上下文
     * @param array $availableActions 可用动作
     * @return array|null 修改后的动作列表，或 null 不修改
     */
    public function onDecisionMaking($context, $availableActions)
    {
        // 子类可重写此方法
        return null;
    }

    /**
     * 记忆存储前调用
     *
     * @param AgentContext $context Agent 上下文
     * @param string $content 记忆内容
     * @param string $type 记忆类型
     * @param array $metadata 元数据
     * @return array|null 修改后的内容数组，或 null 不修改
     */
    public function onMemoryStore($context, $content, $type, $metadata)
    {
        // 子类可重写此方法
        return null;
    }

    /**
     * 异常处理 Hook
     *
     * @param AgentContext $context Agent 上下文
     * @param Exception $exception 异常对象
     * @return bool 是否继续抛出异常（false 表示已处理）
     */
    public function onException($context, $exception)
    {
        // 子类可重写此方法
        return true; // 默认继续抛出异常
    }

    /**
     * Agent 结束前调用
     *
     * @param AgentContext $context Agent 上下文
     * @param string $reason 结束原因
     * @return void
     */
    public function onAgentTerminate($context, $reason)
    {
        // 子类可重写此方法
    }

    /**
     * 获取 Hook 描述信息
     *
     * @return string
     */
    public function getDescription()
    {
        return "Base Agent Hook";
    }

    /**
     * 验证 Hook 配置
     *
     * @param array $config 配置数组
     * @return bool
     */
    public function validateConfig($config)
    {
        return true; // 默认验证通过
    }
}