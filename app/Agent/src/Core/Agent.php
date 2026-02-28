<?php

namespace App\Agent\Core;

use App\Agent\Core\LLM\Client as LlmClient;
use App\Agent\Schemas\Message;
use App\Agent\Tools\ToolExecutor;
use App\Agent\Tools\BaseTool;
use App\Agent\Events\EventEmitter;

/**
 * 主要 Agent 类
 * 
 * 封装所有配置和执行逻辑，提供简洁的 API 接口。
 */
class Agent
{
    private LlmClient $llm;
    private Config $config;
    private ToolExecutor $toolExecutor;
    private AgentLoop $loop;
    private AgentState $state;
    private EventEmitter $events;
    private string $name;
    private array $tools;

    /**
     * 构造函数
     *
     * @param LlmClient $llmClient LLM 客户端
     * @param Config $config 配置管理器
     * @param array|null $tools 工具数组
     * @param string $name Agent 名称
     */
    public function __construct(
        LlmClient $llmClient,
        Config $config,
        ?array $tools = null,
        string $name = 'agent'
    ) {
        $this->llm = $llmClient;
        $this->config = $config;
        $this->name = $name;
        $this->tools = $tools ?? [];
        
        // 初始化组件
        $this->initializeComponents();
    }

    /**
     * 初始化组件
     *
     * @return void
     */
    private function initializeComponents(): void
    {
        // 初始化工具执行器
        $this->toolExecutor = new ToolExecutor($this->tools);
        
        // 初始化事件发射器
        $this->events = new EventEmitter();
        
        // 初始化 Agent 状态
        $this->state = new AgentState($this->config->get('AGENT_MAX_STEPS', 50));
        
        // 初始化执行循环
        $loopConfig = new LoopConfig(
            maxSteps: $this->config->get('AGENT_MAX_STEPS', 50),
            parallelTools: false
        );
        
        $this->loop = new AgentLoop(
            $this->llm,
            $this->toolExecutor,
            $this->events,
            $loopConfig
        );
        
        // 设置工具
        $this->loop->setTools($this->tools);
    }

    /**
     * 添加工具
     *
     * @param BaseTool $tool 工具实例
     * @return self
     */
    public function addTool(BaseTool $tool): self
    {
        $this->tools[$tool->getName()] = $tool;
        $this->toolExecutor->addTool($tool);
        $this->loop->setTools($this->tools);
        return $this;
    }

    /**
     * 批量添加工具
     *
     * @param array $tools 工具数组
     * @return self
     */
    public function addTools(array $tools): self
    {
        foreach ($tools as $tool) {
            $this->addTool($tool);
        }
        return $this;
    }

    /**
     * 添加用户消息
     *
     * @param string $content 消息内容
     * @return self
     */
    public function addUserMessage(string $content): self
    {
        $message = Message::createUser($content);
        $this->state->addMessage($message);
        return $this;
    }

    /**
     * 添加系统消息
     *
     * @param string $content 消息内容
     * @return self
     */
    public function addSystemMessage(string $content): self
    {
        $message = Message::createSystem($content);
        $this->state->addMessage($message);
        return $this;
    }

    /**
     * 运行 Agent
     *
     * @param string|null $message 初始消息
     * @param array|null $metadata 元数据
     * @return string 执行结果
     */
    public function run(?string $message = null, ?array $metadata = null): string
    {
        // 如果提供了消息，添加到消息历史
        if ($message !== null) {
            $this->addUserMessage($message);
        }

        // 执行循环
        return $this->loop->run($this->state, $metadata);
    }

    /**
     * 流式运行 Agent
     *
     * @param string|null $message 初始消息
     * @param array|null $metadata 元数据
     * @return \Generator
     */
    public function runStream(?string $message = null, ?array $metadata = null): \Generator
    {
        // 如果提供了消息，添加到消息历史
        if ($message !== null) {
            $this->addUserMessage($message);
        }

        // TODO: 实现流式执行
        // 这需要在 AgentLoop 中添加流式支持
        yield ['type' => 'not_implemented', 'message' => 'Streaming not implemented yet'];
    }

    /**
     * 从用户输入恢复执行
     *
     * @param array $userResponse 用户响应
     * @param array|null $metadata 元数据
     * @return string 执行结果
     */
    public function resumeFromInput(array $userResponse, ?array $metadata = null): string
    {
        return $this->loop->resumeFromInput($this->state, $userResponse, $metadata);
    }

    /**
     * 获取 Agent 状态
     *
     * @return AgentState
     */
    public function getState(): AgentState
    {
        return $this->state;
    }

    /**
     * 获取消息历史
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->state->getMessages();
    }

    /**
     * 清空消息历史
     *
     * @return self
     */
    public function clearMessages(): self
    {
        $this->state->setMessages([]);
        return $this;
    }

    /**
     * 获取工具执行器
     *
     * @return ToolExecutor
     */
    public function getToolExecutor(): ToolExecutor
    {
        return $this->toolExecutor;
    }

    /**
     * 获取事件发射器
     *
     * @return EventEmitter
     */
    public function getEventEmitter(): EventEmitter
    {
        return $this->events;
    }

    /**
     * 注册事件监听器
     *
     * @param string $event 事件名称
     * @param callable $callback 回调函数
     * @return self
     */
    public function on(string $event, callable $callback): self
    {
        $this->events->on($event, $callback);
        return $this;
    }

    /**
     * 获取 Agent 名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 设置 Agent 名称
     *
     * @param string $name 名称
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 获取配置
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 更新配置
     *
     * @param array $config 配置数组
     * @return self
     */
    public function setConfig(array $config): self
    {
        foreach ($config as $key => $value) {
            $this->config->set($key, $value);
        }
        return $this;
    }

    /**
     * 检查是否正在运行
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->state->isRunning();
    }

    /**
     * 检查是否已完成
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->state->isCompleted();
    }

    /**
     * 检查是否出错
     *
     * @return bool
     */
    public function isError(): bool
    {
        return $this->state->isError();
    }

    /**
     * 获取执行统计信息
     *
     * @return array
     */
    public function getStats(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->state->getStatus(),
            'current_step' => $this->state->getCurrentStep(),
            'max_steps' => $this->state->getMaxSteps(),
            'total_input_tokens' => $this->state->getTotalInputTokens(),
            'total_output_tokens' => $this->state->getTotalOutputTokens(),
            'total_tokens' => $this->state->getTotalTokens(),
            'message_count' => count($this->state->getMessages()),
            'tool_count' => $this->toolExecutor->getCount(),
        ];
    }

    /**
     * 重置 Agent
     *
     * @param bool $preserveMessages 是否保留消息历史
     * @return self
     */
    public function reset(bool $preserveMessages = false): self
    {
        $this->state->reset($preserveMessages);
        return $this;
    }

    /**
     * 魔术方法：允许链式调用
     *
     * @param string $method 方法名
     * @param array $args 参数
     * @return self
     */
    public function __call(string $method, array $args): self
    {
        if (str_starts_with($method, 'add')) {
            // 处理 add* 方法
            $property = lcfirst(substr($method, 3));
            if (property_exists($this, $property)) {
                $this->$property = $args[0];
                return $this;
            }
        }
        
        throw new \BadMethodCallException("Method {$method} not found");
    }
}