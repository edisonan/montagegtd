<?php

namespace App\Agent\Tools;

/**
 * 工具执行结果类
 * 
 * 表示工具执行的结果，包含成功状态、内容和错误信息。
 */
class ToolResult
{
    /**
     * 是否执行成功
     *
     * @var bool
     */
    private bool $success;

    /**
     * 执行结果内容
     *
     * @var string
     */
    private string $content;

    /**
     * 错误信息
     *
     * @var string|null
     */
    private ?string $error;

    /**
     * 构造函数
     *
     * @param bool $success 是否成功
     * @param string $content 结果内容
     * @param string|null $error 错误信息
     */
    public function __construct(bool $success, string $content = '', ?string $error = null)
    {
        $this->success = $success;
        $this->content = $content;
        $this->error = $error;
    }

    /**
     * 创建成功的工具结果
     *
     * @param string $content 结果内容
     * @return self
     */
    public static function success(string $content): self
    {
        return new self(true, $content);
    }

    /**
     * 创建失败的工具结果
     *
     * @param string $error 错误信息
     * @return self
     */
    public static function failure(string $error): self
    {
        return new self(false, '', $error);
    }

    /**
     * 检查是否执行成功
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * 检查是否执行失败
     *
     * @return bool
     */
    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * 获取执行结果内容
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * 获取错误信息
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'content' => $this->content,
            'error' => $this->error,
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