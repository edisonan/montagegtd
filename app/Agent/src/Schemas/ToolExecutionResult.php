<?php

namespace App\Agent\Schemas;

use App\Agent\Tools\ToolResult;

/**
 * 工具执行结果类
 * 
 * 表示单次工具执行的完整结果，包括执行时间和参数信息。
 */
class ToolExecutionResult
{
    /**
     * 工具名称
     *
     * @var string
     */
    private string $toolName;

    /**
     * 工具调用 ID
     *
     * @var string
     */
    private string $toolCallId;

    /**
     * 执行结果
     *
     * @var ToolResult
     */
    private ToolResult $result;

    /**
     * 执行时间（秒）
     *
     * @var float
     */
    private float $executionTime;

    /**
     * 执行参数
     *
     * @var array
     */
    private array $arguments;

    /**
     * 构造函数
     *
     * @param string $toolName 工具名称
     * @param string $toolCallId 工具调用 ID
     * @param ToolResult $result 执行结果
     * @param float $executionTime 执行时间
     * @param array $arguments 执行参数
     */
    public function __construct(
        string $toolName,
        string $toolCallId,
        ToolResult $result,
        float $executionTime,
        array $arguments
    ) {
        $this->toolName = $toolName;
        $this->toolCallId = $toolCallId;
        $this->result = $result;
        $this->executionTime = $executionTime;
        $this->arguments = $arguments;
    }

    /**
     * 获取工具名称
     *
     * @return string
     */
    public function getToolName(): string
    {
        return $this->toolName;
    }

    /**
     * 获取工具调用 ID
     *
     * @return string
     */
    public function getToolCallId(): string
    {
        return $this->toolCallId;
    }

    /**
     * 获取执行结果
     *
     * @return ToolResult
     */
    public function getResult(): ToolResult
    {
        return $this->result;
    }

    /**
     * 获取执行时间
     *
     * @return float
     */
    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    /**
     * 获取执行参数
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * 检查执行是否成功
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->result->isSuccess();
    }

    /**
     * 获取执行结果内容
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->result->getContent();
    }

    /**
     * 获取错误信息
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->result->getError();
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'tool_name' => $this->toolName,
            'tool_call_id' => $this->toolCallId,
            'result' => $this->result->toArray(),
            'execution_time' => $this->executionTime,
            'arguments' => $this->arguments,
        ];
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
}