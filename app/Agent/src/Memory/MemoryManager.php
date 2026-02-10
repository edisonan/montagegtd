<?php

namespace Agent\Memory;

use Agent\Config\Config;
use Exception;

/**
 * 记忆管理器
 * 负责记忆的存储、检索、更新和清理
 */
class MemoryManager
{
    private $config;
    private $storagePath;
    private $maxMemories;
    private $cleanupThreshold;
    private $memories;
    private $sessionMemories;

    /**
     * 构造函数
     *
     * @param Config $config 配置对象
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->storagePath = $config->get('memory.storage_path', sys_get_temp_dir() . '/agent_memories');
        $this->maxMemories = $config->get('memory.max_memories', 1000);
        $this->cleanupThreshold = $config->get('memory.cleanup_threshold', 0.8);
        
        $this->memories = array(); // 所有记忆
        $this->sessionMemories = array(); // 按会话分组的记忆
        
        $this->ensureStorageDirectory();
        $this->loadMemories();
    }

    /**
     * 确保存储目录存在
     */
    private function ensureStorageDirectory()
    {
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * 加载所有记忆
     */
    private function loadMemories()
    {
        $memoryFile = $this->storagePath . '/memories.json';
        if (file_exists($memoryFile)) {
            $content = file_get_contents($memoryFile);
            $data = json_decode($content, true);
            
            if (is_array($data)) {
                foreach ($data as $memoryData) {
                    $memory = Memory::fromArray($memoryData);
                    $this->addMemoryToIndex($memory);
                }
            }
        }
    }

    /**
     * 将记忆添加到索引
     *
     * @param Memory $memory
     */
    private function addMemoryToIndex($memory)
    {
        $this->memories[$memory->getId()] = $memory;
        
        $sessionId = $memory->getSessionId();
        if (!isset($this->sessionMemories[$sessionId])) {
            $this->sessionMemories[$sessionId] = array();
        }
        $this->sessionMemories[$sessionId][$memory->getId()] = $memory;
    }

    /**
     * 保存所有记忆到文件
     */
    private function saveMemories()
    {
        $memoryFile = $this->storagePath . '/memories.json';
        $data = array();
        
        foreach ($this->memories as $memory) {
            $data[] = $memory->toArray();
        }
        
        file_put_contents($memoryFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * 添加新记忆
     *
     * @param string $sessionId 会话ID
     * @param string $content 记忆内容
     * @param string $type 记忆类型
     * @param array $metadata 元数据
     * @param float $importance 重要性评分
     * @return Memory 创建的记忆对象
     */
    public function addMemory($sessionId, $content, $type = Memory::TYPE_OBSERVATION, $metadata = array(), $importance = 0.5)
    {
        $id = $this->generateMemoryId();
        $memory = new Memory($id, $sessionId, $content, $type, $metadata, $importance);
        
        $this->addMemoryToIndex($memory);
        
        // 检查是否需要清理
        if (count($this->memories) > $this->maxMemories * $this->cleanupThreshold) {
            $this->performCleanup();
        }
        
        $this->saveMemories();
        
        return $memory;
    }

    /**
     * 批量添加记忆
     *
     * @param string $sessionId 会话ID
     * @param array $memories 记忆数组，每个元素包含content,type,metadata,importance
     * @return array 创建的记忆对象数组
     */
    public function addMemories($sessionId, $memories)
    {
        $createdMemories = array();
        
        foreach ($memories as $memoryData) {
            $content = isset($memoryData['content']) ? $memoryData['content'] : '';
            $type = isset($memoryData['type']) ? $memoryData['type'] : Memory::TYPE_OBSERVATION;
            $metadata = isset($memoryData['metadata']) ? $memoryData['metadata'] : array();
            $importance = isset($memoryData['importance']) ? $memoryData['importance'] : 0.5;
            
            $memory = $this->addMemory($sessionId, $content, $type, $metadata, $importance);
            $createdMemories[] = $memory;
        }
        
        return $createdMemories;
    }

    /**
     * 获取指定会话的所有记忆
     *
     * @param string $sessionId 会话ID
     * @return array Memory对象数组
     */
    public function getMemoriesBySession($sessionId)
    {
        return isset($this->sessionMemories[$sessionId]) ? 
               array_values($this->sessionMemories[$sessionId]) : array();
    }

    /**
     * 搜索记忆
     *
     * @param string $sessionId 会话ID
     * @param string $query 搜索查询
     * @param string|null $type 记忆类型过滤
     * @param int $limit 返回数量限制
     * @param float $minRelevance 最小相关性阈值
     * @return array 按相关性排序的记忆数组
     */
    public function searchMemories($sessionId, $query, $type = null, $limit = 10, $minRelevance = 0.1)
    {
        $relevantMemories = array();
        $sessionMemories = $this->getMemoriesBySession($sessionId);
        
        foreach ($sessionMemories as $memory) {
            // 类型过滤
            if ($type !== null && $memory->getType() !== $type) {
                continue;
            }
            
            // 计算相关性
            $relevance = $memory->calculateRelevance($query);
            
            if ($relevance >= $minRelevance) {
                $relevantMemories[] = array(
                    'memory' => $memory,
                    'relevance' => $relevance
                );
            }
        }
        
        // 按相关性排序
        usort($relevantMemories, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });
        
        // 限制返回数量
        $relevantMemories = array_slice($relevantMemories, 0, $limit);
        
        // 只返回记忆对象
        return array_map(function($item) {
            return $item['memory'];
        }, $relevantMemories);
    }

    /**
     * 获取重要记忆
     *
     * @param string $sessionId 会话ID
     * @param float $threshold 重要性阈值
     * @param int $limit 数量限制
     * @return array 重要记忆数组
     */
    public function getImportantMemories($sessionId, $threshold = 0.7, $limit = 20)
    {
        $importantMemories = array();
        $sessionMemories = $this->getMemoriesBySession($sessionId);
        
        foreach ($sessionMemories as $memory) {
            if ($memory->isImportant($threshold)) {
                $importantMemories[] = $memory;
            }
        }
        
        // 按重要性排序
        usort($importantMemories, function($a, $b) {
            return $b->getImportance() <=> $a->getImportance();
        });
        
        return array_slice($importantMemories, 0, $limit);
    }

    /**
     * 更新记忆
     *
     * @param string $memoryId 记忆ID
     * @param array $updates 更新内容
     * @return bool 是否成功
     */
    public function updateMemory($memoryId, $updates)
    {
        if (!isset($this->memories[$memoryId])) {
            return false;
        }
        
        $memory = $this->memories[$memoryId];
        
        // 更新内容
        if (isset($updates['content'])) {
            $memory->setContent($updates['content']);
        }
        
        // 更新元数据
        if (isset($updates['metadata'])) {
            $memory->setMetadata($updates['metadata']);
        }
        
        // 更新重要性
        if (isset($updates['importance'])) {
            $memory->setImportance($updates['importance']);
        }
        
        $this->saveMemories();
        return true;
    }

    /**
     * 删除记忆
     *
     * @param string $memoryId 记忆ID
     * @return bool 是否成功
     */
    public function deleteMemory($memoryId)
    {
        if (!isset($this->memories[$memoryId])) {
            return false;
        }
        
        $memory = $this->memories[$memoryId];
        $sessionId = $memory->getSessionId();
        
        // 从全局索引删除
        unset($this->memories[$memoryId]);
        
        // 从会话索引删除
        if (isset($this->sessionMemories[$sessionId][$memoryId])) {
            unset($this->sessionMemories[$sessionId][$memoryId]);
        }
        
        $this->saveMemories();
        return true;
    }

    /**
     * 清理会话记忆
     *
     * @param string $sessionId 会话ID
     */
    public function clearSessionMemories($sessionId)
    {
        if (!isset($this->sessionMemories[$sessionId])) {
            return;
        }
        
        // 删除该会话的所有记忆
        foreach ($this->sessionMemories[$sessionId] as $memoryId => $memory) {
            unset($this->memories[$memoryId]);
        }
        
        unset($this->sessionMemories[$sessionId]);
        $this->saveMemories();
    }

    /**
     * 执行记忆清理
     * 移除最不重要的旧记忆
     */
    private function performCleanup()
    {
        $currentCount = count($this->memories);
        $targetCount = (int)($this->maxMemories * 0.8); // 保留80%
        
        if ($currentCount <= $targetCount) {
            return;
        }
        
        // 按重要性和新鲜度综合评分排序
        $scoredMemories = array();
        foreach ($this->memories as $memory) {
            $importance = $memory->getImportance();
            $freshness = $memory->getFreshness();
            $score = $importance * 0.7 + $freshness * 0.3; // 重要性权重更高
            
            $scoredMemories[] = array(
                'memory' => $memory,
                'score' => $score
            );
        }
        
        // 按评分升序排列（低分优先删除）
        usort($scoredMemories, function($a, $b) {
            return $a['score'] <=> $b['score'];
        });
        
        // 删除评分最低的记忆
        $deleteCount = $currentCount - $targetCount;
        for ($i = 0; $i < $deleteCount; $i++) {
            $memoryToDelete = $scoredMemories[$i]['memory'];
            $this->deleteMemory($memoryToDelete->getId());
        }
    }

    /**
     * 获取记忆统计信息
     *
     * @param string|null $sessionId 会话ID，null表示全局统计
     * @return array 统计信息
     */
    public function getStatistics($sessionId = null)
    {
        if ($sessionId !== null) {
            $memories = $this->getMemoriesBySession($sessionId);
        } else {
            $memories = array_values($this->memories);
        }
        
        $total = count($memories);
        $types = array();
        $avgImportance = 0;
        $oldest = null;
        $newest = null;
        
        foreach ($memories as $memory) {
            $type = $memory->getType();
            if (!isset($types[$type])) {
                $types[$type] = 0;
            }
            $types[$type]++;
            
            $avgImportance += $memory->getImportance();
            
            $createdAt = $memory->getCreatedAt();
            if ($oldest === null || $createdAt < $oldest) {
                $oldest = $createdAt;
            }
            if ($newest === null || $createdAt > $newest) {
                $newest = $createdAt;
            }
        }
        
        $avgImportance = $total > 0 ? $avgImportance / $total : 0;
        
        return array(
            'total_count' => $total,
            'types' => $types,
            'average_importance' => round($avgImportance, 3),
            'oldest_memory' => $oldest,
            'newest_memory' => $newest,
            'session_count' => $sessionId === null ? count($this->sessionMemories) : null
        );
    }

    /**
     * 生成记忆ID
     *
     * @return string
     */
    private function generateMemoryId()
    {
        return uniqid('mem_', true);
    }

    /**
     * 获取存储路径
     *
     * @return string
     */
    public function getStoragePath()
    {
        return $this->storagePath;
    }

    /**
     * 获取最大记忆数
     *
     * @return int
     */
    public function getMaxMemories()
    {
        return $this->maxMemories;
    }
}