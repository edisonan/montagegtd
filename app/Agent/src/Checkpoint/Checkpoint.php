<?php

namespace Agent\Checkpoint;

use Agent\Core\Message;
use Agent\Session\AgentSession;
use DateTime;

/**
 * 断点类
 * 保存 Agent 执行状态的快照，支持断点续传
 */
class Checkpoint
{
    private $id;
    private $sessionId;
    private $timestamp;
    private $step;
    private $state;
    private $messages;
    private $memorySnapshot;
    private $metadata;
    private $version;

    /**
     * 构造函数
     *
     * @param string $id 断点ID
     * @param string $sessionId 会话ID
     * @param int $step 执行步骤
     * @param array $state 当前状态
     * @param array $messages 消息历史
     * @param array $memorySnapshot 记忆快照
     * @param array $metadata 元数据
     */
    public function __construct($id, $sessionId, $step, $state, $messages, $memorySnapshot = array(), $metadata = array())
    {
        $this->id = $id;
        $this->sessionId = $sessionId;
        $this->timestamp = time();
        $this->step = $step;
        $this->state = $state;
        $this->messages = $messages;
        $this->memorySnapshot = $memorySnapshot;
        $this->metadata = $metadata;
        $this->version = '1.0';
    }

    /**
     * 获取断点ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 获取会话ID
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * 获取时间戳
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * 获取执行步骤
     *
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * 获取当前状态
     *
     * @return array
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * 获取消息历史
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * 获取记忆快照
     *
     * @return array
     */
    public function getMemorySnapshot()
    {
        return $this->memorySnapshot;
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
     * 获取版本号
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 添加元数据
     *
     * @param string $key 键名
     * @param mixed $value 值
     */
    public function addMetadata($key, $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * 获取格式化的时间
     *
     * @param string $format 时间格式
     * @return string
     */
    public function getFormattedTime($format = 'Y-m-d H:i:s')
    {
        return date($format, $this->timestamp);
    }

    /**
     * 检查断点是否有效
     *
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->id) && 
               !empty($this->sessionId) && 
               is_numeric($this->step) && 
               is_array($this->state);
    }

    /**
     * 序列化为数组
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'session_id' => $this->sessionId,
            'timestamp' => $this->timestamp,
            'step' => $this->step,
            'state' => $this->state,
            'messages' => $this->messages,
            'memory_snapshot' => $this->memorySnapshot,
            'metadata' => $this->metadata,
            'version' => $this->version
        );
    }

    /**
     * 从数组创建断点实例
     *
     * @param array $data
     * @return Checkpoint
     */
    public static function fromArray($data)
    {
        $checkpoint = new self(
            $data['id'],
            $data['session_id'],
            $data['step'],
            $data['state'],
            $data['messages'],
            isset($data['memory_snapshot']) ? $data['memory_snapshot'] : array(),
            isset($data['metadata']) ? $data['metadata'] : array()
        );

        // 恢复时间戳
        if (isset($data['timestamp'])) {
            $checkpoint->timestamp = $data['timestamp'];
        }

        // 恢复版本
        if (isset($data['version'])) {
            $checkpoint->version = $data['version'];
        }

        return $checkpoint;
    }

    /**
     * 转换为JSON
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * 从JSON创建断点实例
     *
     * @param string $json
     * @return Checkpoint
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);
        return self::fromArray($data);
    }

    /**
     * 创建当前状态的深拷贝
     *
     * @return Checkpoint
     */
    public function clone()
    {
        return new self(
            $this->id . '_clone_' . uniqid(),
            $this->sessionId,
            $this->step,
            unserialize(serialize($this->state)),
            unserialize(serialize($this->messages)),
            unserialize(serialize($this->memorySnapshot)),
            unserialize(serialize($this->metadata))
        );
    }

    /**
     * 比较两个断点的时间顺序
     *
     * @param Checkpoint $other 另一个断点
     * @return int -1(早于), 0(相同), 1(晚于)
     */
    public function compareTo($other)
    {
        if ($this->timestamp < $other->timestamp) {
            return -1;
        } elseif ($this->timestamp > $other->timestamp) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 获取断点摘要信息
     *
     * @return array
     */
    public function getSummary()
    {
        return array(
            'id' => $this->id,
            'session_id' => $this->sessionId,
            'timestamp' => $this->timestamp,
            'formatted_time' => $this->getFormattedTime(),
            'step' => $this->step,
            'message_count' => count($this->messages),
            'memory_items' => count($this->memorySnapshot),
            'metadata_keys' => array_keys($this->metadata)
        );
    }
}