<?php

namespace App\Agent\Schemas;

/**
 * 用户输入字段类
 * 
 * 表示用户输入请求中的单个字段定义。
 */
class UserInputField
{
    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NUMBER = 'number';

    /**
     * 字段名称
     *
     * @var string
     */
    private string $fieldName;

    /**
     * 字段类型
     *
     * @var string
     */
    private string $fieldType;

    /**
     * 字段描述
     *
     * @var string
     */
    private string $fieldDescription;

    /**
     * 构造函数
     *
     * @param string $fieldName 字段名称
     * @param string $fieldType 字段类型
     * @param string $fieldDescription 字段描述
     */
    public function __construct(string $fieldName, string $fieldType, string $fieldDescription)
    {
        $this->fieldName = $fieldName;
        $this->fieldType = $fieldType;
        $this->fieldDescription = $fieldDescription;
    }

    /**
     * 获取字段名称
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * 获取字段类型
     *
     * @return string
     */
    public function getFieldType(): string
    {
        return $this->fieldType;
    }

    /**
     * 获取字段描述
     *
     * @return string
     */
    public function getFieldDescription(): string
    {
        return $this->fieldDescription;
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'field_name' => $this->fieldName,
            'field_type' => $this->fieldType,
            'field_description' => $this->fieldDescription,
        ];
    }

    /**
     * 从数组创建用户输入字段实例
     *
     * @param array $data 数组数据
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['field_name'],
            $data['field_type'],
            $data['field_description']
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
     * 从 JSON 字符串创建用户输入字段实例
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