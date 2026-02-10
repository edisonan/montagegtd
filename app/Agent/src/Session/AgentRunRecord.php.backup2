<?php

namespace App\Agent\Session;

/**
 * Agent 运行记录类 (PHP 7.3 兼容版本)
 * 
 * 记录 Agent 的单次运行结果，用于历史上下文追踪。
 */
class AgentRunRecord
{
    /**
     * 运行 ID
     *
     * @var string
     */
    private $runId;

    /**
     * 用户任务
     *
     * @var string
     */
    private $task;

    /**
     * Agent 响应
     *
     * @var string
     */
    private $response;

    /**
     * 是否成功
     *
     * @var bool
     */
    private $success;

    /**
     * 执行步数
     *
     * @var int
     */
    private $steps;

    /**
     * 时间戳
     *
     * @var float
     */
    private $timestamp;

    /**
     * 元数据
     *
     * @var array
     */
    private $metadata;

    /**
     * 构造函数
     *
     * @param string $runId 运行 ID
     * @param string $task 用户任务
     * @param string $response Agent 响应
     * @param bool $success 是否成功
     * @param int $steps 执行步数
     * @param float|null $timestamp 时间戳
     * @param array $metadata 元数据
     */
    public function __construct(
        $runId,
        $task,
        $response,
        $success,
        $steps,
        $timestamp = null,
        $metadata = array()
    ) {
        $this->runId = $runId;
        $this->task = $task;
        $this->response = $response;
        $this->success = $success;
        $this->steps = $steps;
        $this->timestamp = $timestamp ? $timestamp : microtime(true);
        $this->metadata = $metadata;
    }

    /**
     * 获取运行 ID
     *
     * @return string
     */
    public function getRunId()
    {
        return $this->runId;
    }

    /**
     * 获取用户任务
     *
     * @return string
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * 获取 Agent 响应
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 检查是否成功
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * 获取执行步数
     *
     * @return int
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * 获取时间戳
     *
     * @return float
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * 获取元数据
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * 获取格式化时间
     *
     * @param string $format 时间格式
     * @return string
     */
    public function getFormattedTime($format = 'Y-m-d H:i:s')
    {
        return date($format, (int)$this->timestamp);
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'run_id' => $this->runId,
            'task' => $this->task,
            'response' => $this->response,
            'success' => $this->success,
            'steps' => $this->steps,
            'timestamp' => $this->timestamp,
            'formatted_time' => $this->getFormattedTime(),
            'metadata' => $this->metadata
        );
    }

    /**
     * 从数组创建实例
     *
     * @param array $data 数组数据
     * @return self
     */
    public static function fromArray($data)
    {
        return new self(
            $data['run_id'],
            $data['task'],
            $data['response'],
            $data['success'],
            $data['steps'],
            isset($data['timestamp']) ? $data['timestamp'] : null,
            isset($data['metadata']) ? $data['metadata'] : array()
        );
    }

    /**
     * 转换为 JSON 字符串
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 从 JSON 字符串创建实例
     *
     * @param string $json JSON 字符串
     * @return self
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }
        return self::fromArray($data);
    }
}