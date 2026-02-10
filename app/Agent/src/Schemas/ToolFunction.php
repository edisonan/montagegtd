<?php

namespace App\Agent\Schemas;

/**
 * 工具函数类
 * 
 * 表示工具函数的信息，包括名称和参数。
 */
class ToolFunction
{
    /**
     * 函数名称
     *
     * @var string
     */
    private string $name;

    /**
     * 函数参数
     *
     * @var array
     */
    private array $arguments;

    /**
     * 构造函数
     *
     * @param string $name 函数名称
     * @param array $arguments 函数参数
     */
    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * 获取函数名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取函数参数
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * 获取指定参数值
     *
     * @param string $key 参数键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getArgument(string $key, $default = null)
    {
        return $this->arguments[$key] ?? $default;
    }

    /**
     * 检查参数是否存在
     *
     * @param string $key 参数键
     * @return bool
     */
    public function hasArgument(string $key): bool
    {
        return array_key_exists($key, $this->arguments);
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'arguments' => $this->arguments,
        ];
    }

    /**
     * 从数组创建工具函数实例
     *
     * @param array $data 数组数据
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['arguments'] ?? []
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
     * 从 JSON 字符串创建工具函数实例
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