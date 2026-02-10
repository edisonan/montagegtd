<?php

namespace App\Agent\Schemas;

/**
 * LLM 响应类
 * 
 * 表示 LLM 生成的完整响应，包括内容、思考、工具调用和用量信息。
 */
class LlmResponse
{
    /**
     * 响应内容
     *
     * @var string|null
     */
    private ?string $content;

    /**
     * 思考内容
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
     * 用量信息
     *
     * @var Usage|null
     */
    private ?Usage $usage;

    /**
     * 构造函数
     *
     * @param string|null $content 响应内容
     * @param string|null $thinking 思考内容
     * @param array|null $toolCalls 工具调用列表
     * @param Usage|null $usage 用量信息
     */
    public function __construct(
        ?string $content = null,
        ?string $thinking = null,
        ?array $toolCalls = null,
        ?Usage $usage = null
    ) {
        $this->content = $content;
        $this->thinking = $thinking;
        $this->toolCalls = $toolCalls;
        $this->usage = $usage;
    }

    /**
     * 获取响应内容
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
     * 获取用量信息
     *
     * @return Usage|null
     */
    public function getUsage(): ?Usage
    {
        return $this->usage;
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
     * 获取工具调用数量
     *
     * @return int
     */
    public function getToolCallCount(): int
    {
        return $this->toolCalls ? count($this->toolCalls) : 0;
    }

    /**
     * 获取输入 token 数量
     *
     * @return int
     */
    public function getInputTokens(): int
    {
        return $this->usage ? $this->usage->getInputTokens() : 0;
    }

    /**
     * 获取输出 token 数量
     *
     * @return int
     */
    public function getOutputTokens(): int
    {
        return $this->usage ? $this->usage->getOutputTokens() : 0;
    }

    /**
     * 获取总 token 数量
     *
     * @return int
     */
    public function getTotalTokens(): int
    {
        return $this->usage ? $this->usage->getTotalTokens() : 0;
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

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

        if ($this->usage !== null) {
            $data['usage'] = $this->usage->toArray();
        }

        return $data;
    }

    /**
     * 从数组创建 LLM 响应实例
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

        $usage = null;
        if (isset($data['usage'])) {
            $usage = $data['usage'] instanceof Usage 
                ? $data['usage'] 
                : Usage::fromArray($data['usage']);
        }

        return new self(
            $data['content'] ?? null,
            $data['thinking'] ?? null,
            $toolCalls,
            $usage
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
     * 从 JSON 字符串创建 LLM 响应实例
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