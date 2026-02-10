<?php

namespace Agent\Memory;

/**
 * 记忆单元类
 * 表示单个记忆条目
 */
class Memory
{
    private $id;
    private $sessionId;
    private $content;
    private $type;
    private $metadata;
    private $createdAt;
    private $accessedAt;
    private $importance;

    const TYPE_OBSERVATION = 'observation';
    const TYPE_REFLECTION = 'reflection';
    const TYPE_INTERACTION = 'interaction';
    const TYPE_TOOL_RESULT = 'tool_result';

    /**
     * 构造函数
     *
     * @param string $id 记忆ID
     * @param string $sessionId 会话ID
     * @param string $content 记忆内容
     * @param string $type 记忆类型
     * @param array $metadata 元数据
     * @param float $importance 重要性评分 (0-1)
     */
    public function __construct($id, $sessionId, $content, $type = self::TYPE_OBSERVATION, $metadata = array(), $importance = 0.5)
    {
        $this->id = $id;
        $this->sessionId = $sessionId;
        $this->content = $content;
        $this->type = $type;
        $this->metadata = $metadata;
        $this->importance = max(0, min(1, $importance)); // 限制在0-1之间
        $this->createdAt = time();
        $this->accessedAt = time();
    }

    /**
     * 获取记忆ID
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
     * 获取记忆内容
     *
     * @return string
     */
    public function getContent()
    {
        $this->accessedAt = time();
        return $this->content;
    }

    /**
     * 设置记忆内容
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
        $this->accessedAt = time();
    }

    /**
     * 获取记忆类型
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 获取元数据
     *
     * @return array
     */
    public function getMetadata()
    {
        $this->accessedAt = time();
        return $this->metadata;
    }

    /**
     * 设置元数据
     *
     * @param array $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        $this->accessedAt = time();
    }

    /**
     * 更新元数据
     *
     * @param array $updates
     */
    public function updateMetadata($updates)
    {
        $this->metadata = array_merge($this->metadata, $updates);
        $this->accessedAt = time();
    }

    /**
     * 获取创建时间
     *
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * 获取最后访问时间
     *
     * @return int
     */
    public function getAccessedAt()
    {
        return $this->accessedAt;
    }

    /**
     * 获取重要性评分
     *
     * @return float
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * 设置重要性评分
     *
     * @param float $importance
     */
    public function setImportance($importance)
    {
        $this->importance = max(0, min(1, $importance));
    }

    /**
     * 判断是否是重要记忆
     *
     * @param float $threshold 阈值，默认0.7
     * @return bool
     */
    public function isImportant($threshold = 0.7)
    {
        return $this->importance >= $threshold;
    }

    /**
     * 获取记忆年龄（秒）
     *
     * @return int
     */
    public function getAge()
    {
        return time() - $this->createdAt;
    }

    /**
     * 获取记忆新鲜度评分 (0-1)
     *
     * @param int $halfLife 半衰期（秒），默认24小时
     * @return float
     */
    public function getFreshness($halfLife = 86400)
    {
        $age = $this->getAge();
        return pow(0.5, $age / $halfLife);
    }

    /**
     * 计算相关性得分
     *
     * @param string $query 查询文本
     * @return float 相关性得分 (0-1)
     */
    public function calculateRelevance($query)
    {
        $content = $this->content;
        $query = trim($query);
        
        if (empty($query) || empty($content)) {
            return 0;
        }
        
        // 对于中文，使用字符级别的匹配
        $queryChars = preg_split('//u', $query, -1, PREG_SPLIT_NO_EMPTY);
        $contentChars = preg_split('//u', $content, -1, PREG_SPLIT_NO_EMPTY);
        
        // 计算精确匹配
        $exactMatches = 0;
        foreach ($queryChars as $char) {
            if (in_array($char, $contentChars)) {
                $exactMatches++;
            }
        }
        
        // 计算子字符串匹配
        $substringMatches = 0;
        $queryLength = mb_strlen($query);
        for ($i = 0; $i < $queryLength; $i++) {
            for ($j = $i + 1; $j <= $queryLength; $j++) {
                $substring = mb_substr($query, $i, $j - $i);
                if (mb_strpos($content, $substring) !== false) {
                    $substringMatches += mb_strlen($substring);
                }
            }
        }
        
        // 基础相关性分数
        $exactScore = $queryChars ? $exactMatches / count($queryChars) : 0;
        $substringScore = $queryLength ? $substringMatches / ($queryLength * $queryLength) : 0;
        
        // 综合得分（精确匹配权重更高）
        $relevance = $exactScore * 0.7 + $substringScore * 0.3;
        
        // 结合重要性和新鲜度
        $freshness = $this->getFreshness();
        $importanceFactor = $this->importance;
        
        return min(1, $relevance * $importanceFactor * $freshness);
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
            'content' => $this->content,
            'type' => $this->type,
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt,
            'accessed_at' => $this->accessedAt,
            'importance' => $this->importance
        );
    }

    /**
     * 从数组创建记忆实例
     *
     * @param array $data
     * @return Memory
     */
    public static function fromArray($data)
    {
        $memory = new self(
            $data['id'],
            $data['session_id'],
            $data['content'],
            isset($data['type']) ? $data['type'] : self::TYPE_OBSERVATION,
            isset($data['metadata']) ? $data['metadata'] : array(),
            isset($data['importance']) ? $data['importance'] : 0.5
        );

        // 恢复时间戳
        if (isset($data['created_at'])) {
            $memory->createdAt = $data['created_at'];
        }
        if (isset($data['accessed_at'])) {
            $memory->accessedAt = $data['accessed_at'];
        }

        return $memory;
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
     * 从JSON创建记忆实例
     *
     * @param string $json
     * @return Memory
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);
        return self::fromArray($data);
    }
}