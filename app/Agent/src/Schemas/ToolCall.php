<?php

namespace App\Agent\Schemas;

/**
 * 工具调用类
 * 
 * 表示一次工具调用的完整信息，包括调用 ID、函数名称和参数。
 */
class ToolCall
{
    /**
     * 工具调用 ID
     *
     * @var string
     */
    private string $id;

    /**
     * 函数信息
     *
     * @var ToolFunction
     */
    private ToolFunction $function;

    /**
     * 构造函数
     *
     * @param string $id 工具调用 ID
     * @param ToolFunction $function 函数信息
     */
    public function __construct(string $id, ToolFunction $function)
    {
        $this->id = $id;
        $this->function = $function;
    }

    /**
     * 获取工具调用 ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * 获取函数信息
     *
     * @return ToolFunction
     */
    public function getFunction(): ToolFunction
    {
        return $this->function;
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'function' => $this->function->toArray(),
        ];
    }

    /**
     * 从数组创建工具调用实例
     *
     * @param array $data 数组数据
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $function = $data['function'] instanceof ToolFunction 
            ? $data['function'] 
            : ToolFunction::fromArray($data['function']);

        return new self($data['id'], $function);
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
     * 从 JSON 字符串创建工具调用实例
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