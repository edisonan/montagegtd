<?php

namespace App\Agent\Session;

/**
 * Agent 会话类 (PHP 7.3 兼容版本)
 * 
 * 管理单个 Agent 的所有运行记录和状态。
 */
class AgentSession
{
    /**
     * 会话 ID
     *
     * @var string
     */
    private $sessionId;

    /**
     * Agent 名称
     *
     * @var string
     */
    private $agentName;

    /**
     * 用户 ID
     *
     * @var string|null
     */
    private $userId;

    /**
     * 运行记录列表
     *
     * @var array
     */
    private $runs = array();

    /**
     * 会话状态（可用于存储自定义数据）
     *
     * @var array
     */
    private $state = array();

    /**
     * 创建时间戳
     *
     * @var float
     */
    private $createdAt;

    /**
     * 更新时间戳
     *
     * @var float
     */
    private $updatedAt;

    /**
     * 构造函数
     *
     * @param string $sessionId 会话 ID
     * @param string $agentName Agent 名称
     * @param string|null $userId 用户 ID
     * @param array $runs 运行记录列表
     * @param array $state 会话状态
     * @param float|null $createdAt 创建时间戳
     * @param float|null $updatedAt 更新时间戳
     */
    public function __construct(
        $sessionId,
        $agentName,
        $userId = null,
        $runs = array(),
        $state = array(),
        $createdAt = null,
        $updatedAt = null
    ) {
        $this->sessionId = $sessionId;
        $this->agentName = $agentName;
        $this->userId = $userId;
        $this->runs = array_map(function ($run) {
            return $run instanceof AgentRunRecord ? $run : AgentRunRecord::fromArray($run);
        }, $runs);
        $this->state = $state;
        $this->createdAt = $createdAt ? $createdAt : microtime(true);
        $this->updatedAt = $updatedAt ? $updatedAt : $this->createdAt;
    }

    /**
     * 添加运行记录
     *
     * @param AgentRunRecord $run 运行记录
     * @return void
     */
    public function addRun($run)
    {
        $this->runs[] = $run;
        $this->updatedAt = microtime(true);
    }

    /**
     * 获取历史消息，用于注入到 Agent messages 中
     *
     * @param int|null $numRuns 返回最近 N 轮运行，null 表示全部
     * @param int $maxResponseChars 每个响应的最大字符数
     * @param bool $smartCompress 是否智能压缩
     * @return array 历史消息列表
     */
    public function getHistoryMessages($numRuns = 3, $maxResponseChars = 800, $smartCompress = true)
    {
        $recentRuns = $numRuns !== null ? array_slice($this->runs, -$numRuns) : $this->runs;
        
        $messages = array();
        foreach ($recentRuns as $run) {
            // 用户消息保持原样
            $messages[] = array(
                'role' => 'user',
                'content' => $run->getTask()
            );
            
            // 智能压缩助手响应
            $response = $run->getResponse();
            if ($smartCompress && strlen($response) > $maxResponseChars) {
                $headChars = (int)($maxResponseChars * 0.7);
                $tailChars = (int)($maxResponseChars * 0.2);
                $response = substr($response, 0, $headChars) .
                    "\n\n[... 中间内容已省略，共 " . strlen($run->getResponse()) . " 字符 ...]\n\n" .
                    substr($response, -$tailChars);
            }
            
            $messages[] = array(
                'role' => 'assistant',
                'content' => $response
            );
        }
        
        return $messages;
    }

    /**
     * 获取历史上下文（用于系统提示）
     *
     * @param int|null $numRuns 返回最近 N 轮运行，null 表示全部
     * @param int|null $maxChars 最大字符数限制
     * @param bool $truncateResponse 是否截断过长的响应
     * @return string 格式化的历史上下文
     */
    public function getHistoryContext($numRuns = 3, $maxChars = null, $truncateResponse = true)
    {
        $recentRuns = $numRuns !== null ? array_slice($this->runs, -$numRuns) : $this->runs;
        
        if (empty($recentRuns)) {
            return "";
        }
        
        $contextParts = array("<conversation_history>");
        $totalChars = strlen("<conversation_history>\n</conversation_history>");
        
        foreach ($recentRuns as $i => $run) {
            $task = $run->getTask();
            $response = $run->getResponse();
            
            $roundText = "[Round " . ($i + 1) . "]\nUser: {$task}\nAssistant: {$response}\n";
            
            // 检查字符数限制
            if ($maxChars !== null && $totalChars + strlen($roundText) > $maxChars) {
                if ($i === 0) {
                    $available = $maxChars - $totalChars - 50;
                    if ($available > 100) {
                        $roundText = substr($roundText, 0, $available) . "... [truncated]";
                        $contextParts[] = $roundText;
                    }
                }
                break;
            }
            
            $contextParts[] = $roundText;
            $totalChars += strlen($roundText);
        }
        
        $contextParts[] = "</conversation_history>";
        return implode("\n", $contextParts);
    }

    /**
     * 获取运行次数
     *
     * @return int
     */
    public function getRunsCount()
    {
        return count($this->runs);
    }

    /**
     * 获取所有运行记录
     *
     * @return array
     */
    public function getRuns()
    {
        return $this->runs;
    }

    /**
     * 获取会话 ID
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * 获取 Agent 名称
     *
     * @return string
     */
    public function getAgentName()
    {
        return $this->agentName;
    }

    /**
     * 获取用户 ID
     *
     * @return string|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 获取会话状态
     *
     * @return array
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * 设置会话状态
     *
     * @param array $state 会话状态
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
        $this->updatedAt = microtime(true);
    }

    /**
     * 获取创建时间戳
     *
     * @return float
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * 获取更新时间戳
     *
     * @return float
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * 获取格式化创建时间
     *
     * @param string $format 时间格式
     * @return string
     */
    public function getFormattedCreatedAt($format = 'Y-m-d H:i:s')
    {
        return date($format, (int)$this->createdAt);
    }

    /**
     * 获取格式化更新时间
     *
     * @param string $format 时间格式
     * @return string
     */
    public function getFormattedUpdatedAt($format = 'Y-m-d H:i:s')
    {
        return date($format, (int)$this->updatedAt);
    }

    /**
     * 转换为数组格式
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'session_id' => $this->sessionId,
            'agent_name' => $this->agentName,
            'user_id' => $this->userId,
            'runs' => array_map(function($run) { return $run->toArray(); }, $this->runs),
            'state' => $this->state,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
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
            $data['session_id'],
            $data['agent_name'],
            isset($data['user_id']) ? $data['user_id'] : null,
            isset($data['runs']) ? $data['runs'] : array(),
            isset($data['state']) ? $data['state'] : array(),
            isset($data['created_at']) ? $data['created_at'] : null,
            isset($data['updated_at']) ? $data['updated_at'] : null
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