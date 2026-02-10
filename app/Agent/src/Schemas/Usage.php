<?php

namespace App\Agent\Schemas;

/**
 * 用量信息类
 * 
 * 表示 LLM 调用的 token 用量统计。
 */
class Usage
{
    /**
     * 输入 token 数量
     *
     * @var int
     */
    private int $inputTokens;

    /**
     * 输出 token 数量
     *
     * @var int
     */
    private int $outputTokens;

    /**
     * 构造函数
     *
     * @param int $inputTokens 输入 token 数量
     * @param int $outputTokens 输出 token 数量
     */
    public function __construct(int $inputTokens = 0, int $outputTokens = 0)
    {
        $this->inputTokens = $inputTokens;
        $this->outputTokens = $outputTokens;
    }

    /**
     * 获取输入 token 数量
     *
     * @return int
     */
    public function getInputTokens(): int
    {
        return $this->inputTokens;
    }

    /**
     * 获取输出 token 数量
     *
     * @return int
     */
    public function getOutputTokens(): int
    {
        return $this->outputTokens;
    }

    /**
     * 获取总 token 数量
     *
     * @return int
     */
    public function getTotalTokens(): int
    {
        return $this->inputTokens + $this->outputTokens;
    }

    /**
     * 添加用量统计
     *
     * @param int $inputTokens 输入 token 数量
     * @param int $outputTokens 输出 token 数量
     * @return void
     */
    public function add(int $inputTokens, int $outputTokens): void
    {
        $this->inputTokens += $inputTokens;
        $this->outputTokens += $outputTokens;
    }

    /**
     * 合并另一个用量对象
     *
     * @param Usage $other 另一个用量对象
     * @return void
     */
    public function merge(Usage $other): void
    {
        $this->inputTokens += $other->getInputTokens();
        $this->outputTokens += $other->getOutputTokens();
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'total_tokens' => $this->getTotalTokens(),
        ];
    }

    /**
     * 从数组创建用量实例
     *
     * @param array $data 数组数据
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['input_tokens'] ?? 0,
            $data['output_tokens'] ?? 0
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
     * 从 JSON 字符串创建用量实例
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