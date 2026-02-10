<?php

namespace App\Agent\Session;

use Exception;

/**
 * Agent 会话管理器 (PHP 7.3 兼容版本)
 * 
 * 管理所有 Agent 会话的生命周期，支持内存存储和文件持久化。
 */
class SessionManager
{
    /**
     * 会话存储路径
     *
     * @var string|null
     */
    private $storagePath;

    /**
     * 会话数组
     *
     * @var array
     */
    private $sessions = [];

    /**
     * 是否启用文件存储
     *
     * @var bool
     */
    private $enableFileStorage;

    /**
     * 构造函数
     *
     * @param string|null $storagePath 存储路径
     * @param bool $enableFileStorage 是否启用文件存储
     */
    public function __construct($storagePath = null, $enableFileStorage = true)
    {
        $this->storagePath = $storagePath;
        $this->enableFileStorage = $enableFileStorage;
        
        // 如果启用了文件存储，尝试加载已有会话
        if ($this->enableFileStorage && $this->storagePath) {
            $this->loadFromFile();
        }
    }

    /**
     * 获取或创建会话
     *
     * @param string $sessionId 会话 ID
     * @param string $agentName Agent 名称
     * @param string|null $userId 用户 ID
     * @return AgentSession
     */
    public function getSession($sessionId, $agentName = "default", $userId = null)
    {
        if (!isset($this->sessions[$sessionId])) {
            $this->sessions[$sessionId] = new AgentSession(
                $sessionId,
                $agentName,
                $userId
            );
            
            // 如果启用了文件存储，保存到文件
            if ($this->enableFileStorage) {
                $this->saveToFile();
            }
        }
        
        return $this->sessions[$sessionId];
    }

    /**
     * 添加运行记录到会话
     *
     * @param string $sessionId 会话 ID
     * @param AgentRunRecord $run 运行记录
     * @return bool 是否成功添加
     */
    public function addRun($sessionId, AgentRunRecord $run)
    {
        if (isset($this->sessions[$sessionId])) {
            $this->sessions[$sessionId]->addRun($run);
            
            // 如果启用了文件存储，保存到文件
            if ($this->enableFileStorage) {
                $this->saveToFile();
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * 获取所有会话
     *
     * @return array
     */
    public function getAllSessions()
    {
        return $this->sessions;
    }

    /**
     * 删除会话
     *
     * @param string $sessionId 会话 ID
     * @return bool 是否成功删除
     */
    public function deleteSession($sessionId)
    {
        if (isset($this->sessions[$sessionId])) {
            unset($this->sessions[$sessionId]);
            
            // 如果启用了文件存储，保存到文件
            if ($this->enableFileStorage) {
                $this->saveToFile();
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * 清空所有会话
     *
     * @return void
     */
    public function clearAllSessions()
    {
        $this->sessions = [];
        
        // 如果启用了文件存储，清空文件
        if ($this->enableFileStorage && $this->storagePath) {
            $this->saveToFile();
        }
    }

    /**
     * 保存会话到文件
     *
     * @return bool 是否保存成功
     */
    private function saveToFile()
    {
        if (!$this->enableFileStorage || !$this->storagePath) {
            return false;
        }

        try {
            $data = [];
            foreach ($this->sessions as $sessionId => $session) {
                $data[$sessionId] = $session->toArray();
            }

            // 确保存储目录存在
            $directory = dirname($this->storagePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // 原子写入：先写入临时文件，再重命名
            $tempFile = $this->storagePath . '.tmp';
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            if (file_put_contents($tempFile, $json) !== false) {
                rename($tempFile, $this->storagePath);
                return true;
            }
            
            // 清理临时文件
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('SessionManager saveToFile error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 从文件加载会话
     *
     * @return bool 是否加载成功
     */
    private function loadFromFile()
    {
        if (!$this->enableFileStorage || !$this->storagePath || !file_exists($this->storagePath)) {
            return false;
        }

        try {
            $json = file_get_contents($this->storagePath);
            if ($json === false) {
                return false;
            }

            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('SessionManager loadFromFile JSON decode error: ' . json_last_error_msg());
                return false;
            }

            $this->sessions = [];
            foreach ($data as $sessionId => $sessionData) {
                $this->sessions[$sessionId] = AgentSession::fromArray($sessionData);
            }

            return true;
            
        } catch (Exception $e) {
            error_log('SessionManager loadFromFile error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 清理过期会话
     *
     * @param int $maxAgeDays 会话最大保留天数
     * @return int 清理的会话数量
     */
    public function cleanupOldSessions($maxAgeDays = 7)
    {
        $cutoffTime = time() - ($maxAgeDays * 86400); // 86400 seconds per day
        $toDelete = [];

        foreach ($this->sessions as $sessionId => $session) {
            if ($session->getUpdatedAt() < $cutoffTime) {
                $toDelete[] = $sessionId;
            }
        }

        $deletedCount = 0;
        foreach ($toDelete as $sessionId) {
            if ($this->deleteSession($sessionId)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * 裁剪会话运行记录
     *
     * @param string $sessionId 会话 ID
     * @param int $maxRuns 最大保留运行数
     * @return int 删除的运行记录数量
     */
    public function trimSessionRuns($sessionId, $maxRuns = 100)
    {
        if (!isset($this->sessions[$sessionId])) {
            return 0;
        }

        $session = $this->sessions[$sessionId];
        $runsCount = $session->getRunsCount();
        
        if ($runsCount <= $maxRuns) {
            return 0;
        }

        // 获取所有运行记录
        $runs = $session->getRuns();
        
        // 保留最近的 maxRuns 条
        $keptRuns = array_slice($runs, -$maxRuns);
        
        // 重新创建会话（这会丢失原始状态，但保留最近的运行记录）
        $newSession = new AgentSession(
            $session->getSessionId(),
            $session->getAgentName(),
            $session->getUserId(),
            array_map(function($run) { return $run->toArray(); }, $keptRuns),
            $session->getState(),
            $session->getCreatedAt(),
            microtime(true)
        );
        
        $this->sessions[$sessionId] = $newSession;
        
        // 如果启用了文件存储，保存到文件
        if ($this->enableFileStorage) {
            $this->saveToFile();
        }

        return $runsCount - $maxRuns;
    }

    /**
     * 获取会话统计信息
     *
     * @return array
     */
    public function getStats()
    {
        $totalRuns = 0;
        $oldestSession = null;
        $newestSession = null;

        foreach ($this->sessions as $session) {
            $totalRuns += $session->getRunsCount();
            
            $createdAt = $session->getCreatedAt();
            if ($oldestSession === null || $createdAt < $oldestSession) {
                $oldestSession = $createdAt;
            }
            
            $updatedAt = $session->getUpdatedAt();
            if ($newestSession === null || $updatedAt > $newestSession) {
                $newestSession = $updatedAt;
            }
        }

        return [
            'total_sessions' => count($this->sessions),
            'total_runs' => $totalRuns,
            'oldest_session_age_days' => $oldestSession ? (time() - $oldestSession) / 86400 : 0,
            'newest_session_age_days' => $newestSession ? (time() - $newestSession) / 86400 : 0,
        ];
    }

    /**
     * 检查会话是否存在
     *
     * @param string $sessionId 会话 ID
     * @return bool
     */
    public function hasSession($sessionId)
    {
        return isset($this->sessions[$sessionId]);
    }

    /**
     * 获取会话数量
     *
     * @return int
     */
    public function getSessionCount()
    {
        return count($this->sessions);
    }

    /**
     * 设置存储路径
     *
     * @param string $storagePath 存储路径
     * @return void
     */
    public function setStoragePath($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * 启用文件存储
     *
     * @param bool $enable 是否启用
     * @return void
     */
    public function setEnableFileStorage($enable)
    {
        $this->enableFileStorage = $enable;
    }
}