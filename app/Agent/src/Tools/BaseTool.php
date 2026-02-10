<?php

namespace App\Agent\Tools;

use App\Agent\Schemas\ToolResult as ResultSchema;

/**
 * 工具基类
 * 
 * 所有工具都应该继承此类并实现相应的抽象方法。
 */
abstract class BaseTool
{
    /**
     * 工具名称
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * 工具描述
     *
     * @return string
     */
    abstract public function getDescription(): string;

    /**
     * 工具参数定义 (JSON Schema 格式)
     *
     * @return array
     */
    abstract public function getParameters(): array;

    /**
     * 工具使用说明 (可选)
     *
     * @return string|null
     */
    public function getInstructions(): ?string
    {
        return null;
    }

    /**
     * 是否将工具说明添加到系统提示
     *
     * @return bool
     */
    public function shouldAddInstructionsToPrompt(): bool
    {
        return false;
    }

    /**
     * 执行工具
     *
     * @param array $arguments 工具参数
     * @return ToolResult
     */
    abstract public function execute(array $arguments): ToolResult;

    /**
     * 转换为工具 Schema
     *
     * @return array
     */
    public function toSchema(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'input_schema' => $this->getParameters(),
        ];
    }

    /**
     * 验证参数
     *
     * @param array $arguments 参数数组
     * @param array $schema 参数 Schema
     * @return array 验证后的参数
     * @throws \InvalidArgumentException
     */
    protected function validateArguments(array $arguments, array $schema): array
    {
        $required = $schema['required'] ?? [];
        $properties = $schema['properties'] ?? [];

        // 检查必需参数
        foreach ($required as $param) {
            if (!isset($arguments[$param])) {
                throw new \InvalidArgumentException("Missing required parameter: {$param}");
            }
        }

        // 验证参数类型和值
        foreach ($arguments as $key => $value) {
            if (!isset($properties[$key])) {
                continue; // 忽略未定义的参数
            }

            $prop = $properties[$key];
            $this->validateParameter($key, $value, $prop);
        }

        return $arguments;
    }

    /**
     * 验证单个参数
     *
     * @param string $name 参数名称
     * @param mixed $value 参数值
     * @param array $schema 参数 Schema
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateParameter(string $name, $value, array $schema): void
    {
        $type = $schema['type'] ?? 'string';

        // 类型验证
        switch ($type) {
            case 'string':
                if (!is_string($value)) {
                    throw new \InvalidArgumentException("Parameter {$name} must be a string");
                }
                break;
                
            case 'integer':
                if (!is_int($value)) {
                    throw new \InvalidArgumentException("Parameter {$name} must be an integer");
                }
                break;
                
            case 'number':
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException("Parameter {$name} must be a number");
                }
                break;
                
            case 'boolean':
                if (!is_bool($value)) {
                    throw new \InvalidArgumentException("Parameter {$name} must be a boolean");
                }
                break;
                
            case 'array':
                if (!is_array($value)) {
                    throw new \InvalidArgumentException("Parameter {$name} must be an array");
                }
                break;
        }

        // 枚举验证
        if (isset($schema['enum']) && !in_array($value, $schema['enum'])) {
            $allowed = implode(', ', $schema['enum']);
            throw new \InvalidArgumentException("Parameter {$name} must be one of: {$allowed}");
        }

        // 最小/最大值验证
        if (isset($schema['minimum']) && is_numeric($value) && $value < $schema['minimum']) {
            throw new \InvalidArgumentException("Parameter {$name} must be >= {$schema['minimum']}");
        }

        if (isset($schema['maximum']) && is_numeric($value) && $value > $schema['maximum']) {
            throw new \InvalidArgumentException("Parameter {$name} must be <= {$schema['maximum']}");
        }

        // 长度验证
        if (isset($schema['minLength']) && is_string($value) && strlen($value) < $schema['minLength']) {
            throw new \InvalidArgumentException("Parameter {$name} must be at least {$schema['minLength']} characters");
        }

        if (isset($schema['maxLength']) && is_string($value) && strlen($value) > $schema['maxLength']) {
            throw new \InvalidArgumentException("Parameter {$name} must be at most {$schema['maxLength']} characters");
        }
    }

    /**
     * 格式化输出内容
     *
     * @param string $content 原始内容
     * @param int $limit 最大长度限制
     * @return string 格式化后的内容
     */
    protected function formatOutput(string $content, int $limit = 10000): string
    {
        if (strlen($content) > $limit) {
            return substr($content, 0, $limit) . "\n...[truncated, total " . strlen($content) . " chars]";
        }
        return $content;
    }
}