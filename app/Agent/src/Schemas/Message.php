<?php

namespace App\Agent\Schemas;

/**
 * 消息类
 * 
 * 表示 Agent 执行过程中的消息，包括用户消息、助手消息和工具消息。
 */
class Message
{
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM = 'system';
    public const ROLE_TOOL = 'tool';

    /**
     * 消息角色
     *
     * @var string
     */
    private string $role;

    /**
     * 消息内容
     *
     * @var string|null
     */
    private ?string $content;

    /**
     * 思考内容 (Claude 特有)
     *
     * @var string|null
     */
    private ?string $thinking;

    /**
     * 工具调用列表
     *
     * @var array|null
     */
    private $toolCalls;

    /**
     * 工具调用 ID (仅工具消息使用)
     *
     * @var string|null
     */
    private ?string $toolCallId;

    /**
     * 工具名称 (仅工具消息使用)
     *
     * @var string|null
     */
    private ?string $name;

    /**
     * 构造函数
     *
     * @param string $role 消息角色
     * @param string|null $content 消息内容
     * @param string|null $thinking 思考内容
     * @param array|null $toolCalls 工具调用列表
     * @param string|null $toolCallId 工具调用 ID
     * @param string|null $name 工具名称
     */
    public function __construct(
        string $role,
        ?string $content = null,
        ?string $thinking = null,
        ?array $toolCalls = null,
        ?string $toolCallId = null,
        ?string $name = null
    ) {
        $this->role = $role;
        $this->content = $content;
        $this->thinking = $thinking;
        $this->toolCalls = $toolCalls;
        $this->toolCallId = $toolCallId;
        $this->name = $name;
    }

    /**
     * 创建用户消息
     *
     * @param string $content 消息内容
     * @return self
     */
    public static function createUser(string $content): self
    {
        return new self(self::ROLE_USER, $content);
    }

    /**
     * 创建助手消息
     *
     * @param string|null $content 消息内容
     * @param string|null $thinking 思考内容
     * @param array|null $toolCalls 工具调用列表
     * @return self
     */
    public static function createAssistant(
        ?string $content = null,
        ?string $thinking = null,
        ?array $toolCalls = null
    ): self {
        return new self(self::ROLE_ASSISTANT, $content, $thinking, $toolCalls);
    }

    /**
     * 创建系统消息
     *
     * @param string $content 消息内容
     * @return self
     */
    public static function createSystem(string $content): self
    {
        return new self(self::ROLE_SYSTEM, $content);
    }

    /**
     * 创建工具消息
     *
     * @param string $content 消息内容
     * @param string $toolCallId 工具调用 ID
     * @param string $name 工具名称
     * @return self
     */
    public static function createTool(string $content, string $toolCallId, string $name): self
    {
        return new self(self::ROLE_TOOL, $content, null, null, $toolCallId, $name);
    }

    /**
     * 获取消息角色
     *
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * 获取消息内容
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * 获取思考内容
     *
     * @return string|null
     */
    public function getThinking(): ?string
    {
        return $this->thinking;
    }

    /**
     * 获取工具调用列表
     *
     * @return array|null
     */
    public function getToolCalls(): ?array
    {
        return $this->toolCalls;
    }

    /**
     * 获取工具调用 ID
     *
     * @return string|null
     */
    public function getToolCallId(): ?string
    {
        return $this->toolCallId;
    }

    /**
     * 获取工具名称
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * 检查是否有工具调用
     *
     * @return bool
     */
    public function hasToolCalls(): bool
    {
        return !empty($this->toolCalls);
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'role' => $this->role,
        ];

        if ($this->content !== null) {
            $data['content'] = $this->content;
        }

        if ($this->thinking !== null) {
            $data['thinking'] = $this->thinking;
        }

        if ($this->toolCalls !== null) {
            $data['tool_calls'] = array_map(
                fn($call) => $call instanceof ToolCall ? $call->toArray() : $call,
                $this->toolCalls
            );
        }

        if ($this->toolCallId !== null) {
            $data['tool_call_id'] = $this->toolCallId;
        }

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        return $data;
    }

    /**
     * 从数组创建消息实例
     *
     * @param array $data 数组数据
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $toolCalls = null;
        if (isset($data['tool_calls']) && is_array($data['tool_calls'])) {
            $toolCalls = array_map(
                fn($call) => $call instanceof ToolCall ? $call : ToolCall::fromArray($call),
                $data['tool_calls']
            );
        }

        return new self(
            $data['role'],
            $data['content'] ?? null,
            $data['thinking'] ?? null,
            $toolCalls,
            $data['tool_call_id'] ?? null,
            $data['name'] ?? null
        );
    }

    /**
     * 转换为 JSON 字符串
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 从 JSON 字符串创建消息实例
     *
     * @param string $json JSON 字符串
     * @return self
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }
        return self::fromArray($data);
    }
}