<?php

namespace App\Agent\Events;

/**
 * Agent 事件类
 * 
 * 封装 Agent 执行过程中的各种事件。
 */
class AgentEvent
{
    public const STEP_START = 'step_start';
    public const STEP_END = 'step_end';
    public const LLM_REQUEST = 'llm_request';
    public const LLM_RESPONSE = 'llm_response';
    public const TOOL_START = 'tool_start';
    public const TOOL_END = 'tool_end';
    public const USER_INPUT_REQUIRED = 'user_input_required';
    public const COMPLETION = 'completion';
    public const ERROR = 'error';
    public const TOKEN_SUMMARY = 'token_summary';

    /**
     * 事件类型
     *
     * @var string
     */
    private string $type;

    /**
     * 事件数据
     *
     * @var array
     */
    private array $data;

    /**
     * 步骤编号
     *
     * @var int
     */
    private int $step;

    /**
     * 时间戳
     *
     * @var float
     */
    private float $timestamp;

    /**
     * 构造函数
     *
     * @param string $type 事件类型
     * @param array $data 事件数据
     * @param int $step 步骤编号
     * @param float|null $timestamp 时间戳
     */
    public function __construct(string $type, array $data = [], int $step = 0, ?float $timestamp = null)
    {
        $this->type = $type;
        $this->data = $data;
        $this->step = $step;
        $this->timestamp = $timestamp ?? microtime(true);
    }

    /**
     * 创建步骤开始事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function stepStart(int $step, array $data = []): self
    {
        return new self(self::STEP_START, $data, $step);
    }

    /**
     * 创建步骤结束事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function stepEnd(int $step, array $data = []): self
    {
        return new self(self::STEP_END, $data, $step);
    }

    /**
     * 创建 LLM 请求事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function llmRequest(int $step, array $data = []): self
    {
        return new self(self::LLM_REQUEST, $data, $step);
    }

    /**
     * 创建 LLM 响应事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function llmResponse(int $step, array $data = []): self
    {
        return new self(self::LLM_RESPONSE, $data, $step);
    }

    /**
     * 创建工具开始事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function toolStart(int $step, array $data = []): self
    {
        return new self(self::TOOL_START, $data, $step);
    }

    /**
     * 创建工具结束事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function toolEnd(int $step, array $data = []): self
    {
        return new self(self::TOOL_END, $data, $step);
    }

    /**
     * 创建用户输入请求事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function userInputRequired(int $step, array $data = []): self
    {
        return new self(self::USER_INPUT_REQUIRED, $data, $step);
    }

    /**
     * 创建完成事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function completion(int $step, array $data = []): self
    {
        return new self(self::COMPLETION, $data, $step);
    }

    /**
     * 创建错误事件
     *
     * @param int $step 步骤编号
     * @param array $data 事件数据
     * @return self
     */
    public static function error(int $step, array $data = []): self
    {
        return new self(self::ERROR, $data, $step);
    }

    /**
     * 获取事件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * 获取事件数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 获取步骤编号
     *
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * 获取时间戳
     *
     * @return float
     */
    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    /**
     * 获取格式化的时间
     *
     * @param string $format 时间格式
     * @return string
     */
    public function getFormattedTime(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, (int)$this->timestamp);
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'step' => $this->step,
            'timestamp' => $this->timestamp,
            'formatted_time' => $this->getFormattedTime()
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

    /**
     * 从数组创建事件实例
     *
     * @param array $data 数组数据
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['type'],
            $data['data'] ?? [],
            $data['step'] ?? 0,
            $data['timestamp'] ?? null
        );
    }

    /**
     * 从 JSON 字符串创建事件实例
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