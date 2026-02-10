<?php

namespace Agent\Hooks;

use Agent\Config\Config;
use Agent\Core\AgentContext;
use Exception;

/**
 * Hook 管理器
 * 负责注册、管理和执行各种 Hook
 */
class HookManager
{
    private $config;
    private $hooks;
    private $hookRegistry;
    private $enabledHooks;

    // 预定义的 Hook 点
    const HOOK_AGENT_INITIALIZE = 'agent.initialize';
    const HOOK_AGENT_INITIALIZED = 'agent.initialized';
    const HOOK_LOOP_START = 'loop.start';
    const HOOK_LOOP_END = 'loop.end';
    const HOOK_TOOL_CALL = 'tool.call';
    const HOOK_TOOL_RESULT = 'tool.result';
    const HOOK_LLM_CALL = 'llm.call';
    const HOOK_LLM_RESPONSE = 'llm.response';
    const HOOK_DECISION_MAKING = 'decision.making';
    const HOOK_MEMORY_STORE = 'memory.store';
    const HOOK_EXCEPTION = 'exception';
    const HOOK_AGENT_TERMINATE = 'agent.terminate';

    /**
     * 构造函数
     *
     * @param Config $config 配置对象
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->hooks = array(); // 按 Hook 点分类存储
        $this->hookRegistry = array(); // Hook 注册表
        $this->enabledHooks = $config->get('hooks.enabled', array());
        
        $this->initializeDefaultHooks();
    }

    /**
     * 初始化默认 Hook
     */
    private function initializeDefaultHooks()
    {
        // 可以在这里注册一些内置的 Hook
        $this->registerHookPoint(self::HOOK_AGENT_INITIALIZE);
        $this->registerHookPoint(self::HOOK_AGENT_INITIALIZED);
        $this->registerHookPoint(self::HOOK_LOOP_START);
        $this->registerHookPoint(self::HOOK_LOOP_END);
        $this->registerHookPoint(self::HOOK_TOOL_CALL);
        $this->registerHookPoint(self::HOOK_TOOL_RESULT);
        $this->registerHookPoint(self::HOOK_LLM_CALL);
        $this->registerHookPoint(self::HOOK_LLM_RESPONSE);
        $this->registerHookPoint(self::HOOK_DECISION_MAKING);
        $this->registerHookPoint(self::HOOK_MEMORY_STORE);
        $this->registerHookPoint(self::HOOK_EXCEPTION);
        $this->registerHookPoint(self::HOOK_AGENT_TERMINATE);
    }

    /**
     * 注册 Hook 点
     *
     * @param string $hookPoint Hook 点名称
     */
    public function registerHookPoint($hookPoint)
    {
        if (!isset($this->hooks[$hookPoint])) {
            $this->hooks[$hookPoint] = array();
        }
    }

    /**
     * 注册 Hook
     *
     * @param string $hookPoint Hook 点
     * @param AgentHook $hook Hook 实例
     * @param array $config Hook 配置
     * @return bool 是否注册成功
     */
    public function registerHook($hookPoint, $hook, $config = array())
    {
        // 验证 Hook 点是否存在
        if (!isset($this->hooks[$hookPoint])) {
            throw new Exception("Hook point '{$hookPoint}' not registered");
        }

        // 验证 Hook 实例
        if (!($hook instanceof AgentHook)) {
            throw new Exception("Hook must be an instance of AgentHook");
        }

        // 验证配置
        if (!$hook->validateConfig($config)) {
            throw new Exception("Invalid hook configuration for '{$hook->getName()}'");
        }

        // 检查是否启用
        $hookName = $hook->getName();
        if (!empty($this->enabledHooks) && !in_array($hookName, $this->enabledHooks)) {
            $hook->disable();
        }

        // 添加到注册表
        $this->hookRegistry[$hookName] = array(
            'hook' => $hook,
            'hook_point' => $hookPoint,
            'config' => $config,
            'registered_at' => time()
        );

        // 添加到 Hook 点
        $this->hooks[$hookPoint][] = $hook;

        // 按优先级排序
        $this->sortHooksByPriority($hookPoint);

        return true;
    }

    /**
     * 按优先级对 Hook 排序
     *
     * @param string $hookPoint Hook 点
     */
    private function sortHooksByPriority($hookPoint)
    {
        if (isset($this->hooks[$hookPoint])) {
            usort($this->hooks[$hookPoint], function($a, $b) {
                return $a->getPriority() <=> $b->getPriority();
            });
        }
    }

    /**
     * 移除 Hook
     *
     * @param string $hookName Hook 名称
     * @return bool 是否移除成功
     */
    public function unregisterHook($hookName)
    {
        if (!isset($this->hookRegistry[$hookName])) {
            return false;
        }

        $hookInfo = $this->hookRegistry[$hookName];
        $hookPoint = $hookInfo['hook_point'];

        // 从 Hook 点中移除
        if (isset($this->hooks[$hookPoint])) {
            $index = array_search($hookInfo['hook'], $this->hooks[$hookPoint]);
            if ($index !== false) {
                array_splice($this->hooks[$hookPoint], $index, 1);
            }
        }

        // 从注册表中移除
        unset($this->hookRegistry[$hookName]);

        return true;
    }

    /**
     * 启用 Hook
     *
     * @param string $hookName Hook 名称
     * @return bool 是否启用成功
     */
    public function enableHook($hookName)
    {
        if (!isset($this->hookRegistry[$hookName])) {
            return false;
        }

        $this->hookRegistry[$hookName]['hook']->enable();
        return true;
    }

    /**
     * 禁用 Hook
     *
     * @param string $hookName Hook 名称
     * @return bool 是否禁用成功
     */
    public function disableHook($hookName)
    {
        if (!isset($this->hookRegistry[$hookName])) {
            return false;
        }

        $this->hookRegistry[$hookName]['hook']->disable();
        return true;
    }

    /**
     * 执行 Agent 初始化 Hook
     *
     * @param AgentContext $context
     */
    public function executeAgentInitialize($context)
    {
        $this->executeHooks(self::HOOK_AGENT_INITIALIZE, array($context));
    }

    /**
     * 执行 Agent 初始化完成 Hook
     *
     * @param AgentContext $context
     */
    public function executeAgentInitialized($context)
    {
        $this->executeHooks(self::HOOK_AGENT_INITIALIZED, array($context));
    }

    /**
     * 执行循环开始 Hook
     *
     * @param AgentContext $context
     * @param int $step
     */
    public function executeLoopStart($context, $step)
    {
        $this->executeHooks(self::HOOK_LOOP_START, array($context, $step));
    }

    /**
     * 执行循环结束 Hook
     *
     * @param AgentContext $context
     * @param int $step
     * @param mixed $result
     */
    public function executeLoopEnd($context, $step, $result)
    {
        $this->executeHooks(self::HOOK_LOOP_END, array($context, $step, $result));
    }

    /**
     * 执行工具调用 Hook
     *
     * @param AgentContext $context
     * @param string $toolName
     * @param array $arguments
     * @return array 修改后的参数
     */
    public function executeToolCall($context, $toolName, $arguments)
    {
        $modifiedArgs = $arguments;
        
        $results = $this->executeHooks(self::HOOK_TOOL_CALL, array($context, $toolName, $arguments));
        
        // 合并所有 Hook 的修改结果
        foreach ($results as $result) {
            if (is_array($result)) {
                $modifiedArgs = array_merge($modifiedArgs, $result);
            }
        }
        
        return $modifiedArgs;
    }

    /**
     * 执行工具结果 Hook
     *
     * @param AgentContext $context
     * @param string $toolName
     * @param array $arguments
     * @param mixed $result
     * @return mixed 修改后的结果
     */
    public function executeToolResult($context, $toolName, $arguments, $result)
    {
        $modifiedResult = $result;
        
        $results = $this->executeHooks(self::HOOK_TOOL_RESULT, array($context, $toolName, $arguments, $result));
        
        // 使用最后一个非 null 的结果
        foreach (array_reverse($results) as $hookResult) {
            if ($hookResult !== null) {
                $modifiedResult = $hookResult;
                break;
            }
        }
        
        return $modifiedResult;
    }

    /**
     * 执行 LLM 调用 Hook
     *
     * @param AgentContext $context
     * @param array $messages
     * @param array $options
     * @return array 修改后的消息
     */
    public function executeLlmCall($context, $messages, $options)
    {
        $modifiedMessages = $messages;
        
        $results = $this->executeHooks(self::HOOK_LLM_CALL, array($context, $messages, $options));
        
        // 使用最后一个非 null 的结果
        foreach (array_reverse($results) as $result) {
            if (is_array($result)) {
                $modifiedMessages = $result;
                break;
            }
        }
        
        return $modifiedMessages;
    }

    /**
     * 执行 LLM 响应 Hook
     *
     * @param AgentContext $context
     * @param array $messages
     * @param array $options
     * @param mixed $response
     * @return mixed 修改后的响应
     */
    public function executeLlmResponse($context, $messages, $options, $response)
    {
        $modifiedResponse = $response;
        
        $results = $this->executeHooks(self::HOOK_LLM_RESPONSE, array($context, $messages, $options, $response));
        
        // 使用最后一个非 null 的结果
        foreach (array_reverse($results) as $result) {
            if ($result !== null) {
                $modifiedResponse = $result;
                break;
            }
        }
        
        return $modifiedResponse;
    }

    /**
     * 执行决策制定 Hook
     *
     * @param AgentContext $context
     * @param array $availableActions
     * @return array 修改后的动作列表
     */
    public function executeDecisionMaking($context, $availableActions)
    {
        $modifiedActions = $availableActions;
        
        $results = $this->executeHooks(self::HOOK_DECISION_MAKING, array($context, $availableActions));
        
        // 合并所有 Hook 的修改结果
        foreach ($results as $result) {
            if (is_array($result)) {
                $modifiedActions = array_merge($modifiedActions, $result);
            }
        }
        
        return $modifiedActions;
    }

    /**
     * 执行记忆存储 Hook
     *
     * @param AgentContext $context
     * @param string $content
     * @param string $type
     * @param array $metadata
     * @return array 修改后的内容数组
     */
    public function executeMemoryStore($context, $content, $type, $metadata)
    {
        $modifiedData = array(
            'content' => $content,
            'type' => $type,
            'metadata' => $metadata
        );
        
        $results = $this->executeHooks(self::HOOK_MEMORY_STORE, array($context, $content, $type, $metadata));
        
        // 合并所有 Hook 的修改结果
        foreach ($results as $result) {
            if (is_array($result)) {
                $modifiedData = array_merge($modifiedData, $result);
            }
        }
        
        return $modifiedData;
    }

    /**
     * 执行异常处理 Hook
     *
     * @param AgentContext $context
     * @param Exception $exception
     * @return bool 是否继续抛出异常
     */
    public function executeException($context, $exception)
    {
        $continueThrow = true;
        
        $results = $this->executeHooks(self::HOOK_EXCEPTION, array($context, $exception));
        
        // 如果任何 Hook 返回 false，则不继续抛出异常
        foreach ($results as $result) {
            if ($result === false) {
                $continueThrow = false;
                break;
            }
        }
        
        return $continueThrow;
    }

    /**
     * 执行 Agent 终止 Hook
     *
     * @param AgentContext $context
     * @param string $reason
     */
    public function executeAgentTerminate($context, $reason)
    {
        $this->executeHooks(self::HOOK_AGENT_TERMINATE, array($context, $reason));
    }

    /**
     * 执行指定 Hook 点的所有 Hook
     *
     * @param string $hookPoint Hook 点
     * @param array $args 参数数组
     * @return array 所有 Hook 的返回结果
     */
    private function executeHooks($hookPoint, $args)
    {
        $results = array();
        
        if (!isset($this->hooks[$hookPoint])) {
            return $results;
        }
        
        foreach ($this->hooks[$hookPoint] as $hook) {
            // 只执行启用的 Hook
            if (!$hook->isEnabled()) {
                continue;
            }
            
            try {
                $methodName = $this->getHookMethodName($hookPoint);
                if (method_exists($hook, $methodName)) {
                    $result = call_user_func_array(array($hook, $methodName), $args);
                    $results[] = $result;
                }
            } catch (Exception $e) {
                error_log("Hook execution failed: " . $e->getMessage());
                // 继续执行其他 Hook
            }
        }
        
        return $results;
    }

    /**
     * 根据 Hook 点获取对应的方法名
     *
     * @param string $hookPoint
     * @return string
     */
    private function getHookMethodName($hookPoint)
    {
        $methodMap = array(
            self::HOOK_AGENT_INITIALIZE => 'onAgentInitialize',
            self::HOOK_AGENT_INITIALIZED => 'onAgentInitialized',
            self::HOOK_LOOP_START => 'onLoopStart',
            self::HOOK_LOOP_END => 'onLoopEnd',
            self::HOOK_TOOL_CALL => 'onToolCall',
            self::HOOK_TOOL_RESULT => 'onToolResult',
            self::HOOK_LLM_CALL => 'onLlmCall',
            self::HOOK_LLM_RESPONSE => 'onLlmResponse',
            self::HOOK_DECISION_MAKING => 'onDecisionMaking',
            self::HOOK_MEMORY_STORE => 'onMemoryStore',
            self::HOOK_EXCEPTION => 'onException',
            self::HOOK_AGENT_TERMINATE => 'onAgentTerminate'
        );
        
        return isset($methodMap[$hookPoint]) ? $methodMap[$hookPoint] : '';
    }

    /**
     * 获取所有已注册的 Hook
     *
     * @return array
     */
    public function getRegisteredHooks()
    {
        return $this->hookRegistry;
    }

    /**
     * 获取指定 Hook 点的 Hook 列表
     *
     * @param string $hookPoint
     * @return array
     */
    public function getHooksByPoint($hookPoint)
    {
        return isset($this->hooks[$hookPoint]) ? $this->hooks[$hookPoint] : array();
    }

    /**
     * 获取 Hook 统计信息
     *
     * @return array
     */
    public function getStatistics()
    {
        $totalHooks = count($this->hookRegistry);
        $enabledHooks = 0;
        $hookPoints = array();
        
        foreach ($this->hookRegistry as $hookInfo) {
            if ($hookInfo['hook']->isEnabled()) {
                $enabledHooks++;
            }
            
            $hookPoint = $hookInfo['hook_point'];
            if (!isset($hookPoints[$hookPoint])) {
                $hookPoints[$hookPoint] = 0;
            }
            $hookPoints[$hookPoint]++;
        }
        
        return array(
            'total_hooks' => $totalHooks,
            'enabled_hooks' => $enabledHooks,
            'disabled_hooks' => $totalHooks - $enabledHooks,
            'hook_points' => $hookPoints,
            'registered_points' => count($this->hooks)
        );
    }
}