<?php

namespace App\Agent\Schemas;

/**
 * 用户输入请求类
 * 
 * 表示 Agent 需要用户输入时的请求信息。
 */
class UserInputRequest
{
    /**
     * 工具调用 ID
     *
     * @var string
     */
    private string $toolCallId;

    /**
     * 输入字段列表
     *
     * @var array
     */
    private array $fields;

    /**
     * 上下文信息
     *
     * @var string|null
     */
    private ?string $context;

    /**
     * 构造函数
     *
     * @param string $toolCallId 工具调用 ID
     * @param array $fields 输入字段列表
     * @param string|null $context 上下文信息
     */
    public function __construct(string $toolCallId, array $fields = [], ?string $context = null)
    {
        $this->toolCallId = $toolCallId;
        $this->fields = array_map(function ($field) {
            return $field instanceof UserInputField ? $field : UserInputField::fromArray($field);
        }, $fields);
        $this->context = $context;
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
     * 获取输入字段列表
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * 获取上下文信息
     *
     * @return string|null
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * 添加输入字段
     *
     * @param UserInputField $field 输入字段
     * @return void
     */
    public function addField(UserInputField $field): void
    {
        $this->fields[] = $field;
    }

    /**
     * 获取字段数量
     *
     * @return int
     */
    public function getFieldCount(): int
    {
        return count($this->fields);
    }

    /**
     * 检查是否有特定类型的字段
     *
     * @param string $type 字段类型
     * @return bool
     */
    public function hasFieldType(string $type): bool
    {
        foreach ($this->fields as $field) {
            if ($field->getFieldType() === $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取特定类型的字段
     *
     * @param string $type 字段类型
     * @return array
     */
    public function getFieldsByType(string $type): array
    {
        return array_filter($this->fields, function ($field) use ($type) {
            return $field->getFieldType() === $type;
        });
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'tool_call_id' => $this->toolCallId,
            'fields' => array_map(fn($field) => $field->toArray(), $this->fields),
            'context' => $this->context,
        ];
    }

    /**
     * 从数组创建用户输入请求实例
     *
     * @param array $data 数组数据
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['tool_call_id'],
            $data['fields'] ?? [],
            $data['context'] ?? null
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
     * 从 JSON 字符串创建用户输入请求实例
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