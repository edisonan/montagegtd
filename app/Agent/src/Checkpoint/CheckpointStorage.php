<?php

namespace Agent\Checkpoint;

use Agent\Config\Config;
use Exception;

/**
 * 断点存储管理器
 * 负责断点的保存、加载、查询和清理
 */
class CheckpointStorage
{
    private $config;
    private $storagePath;
    private $maxCheckpointsPerSession;
    private $cleanupThreshold;
    private $checkpoints;

    /**
     * 构造函数
     *
     * @param Config $config 配置对象
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->storagePath = $config->get('checkpoint.storage_path', sys_get_temp_dir() . '/agent_checkpoints');
        $this->maxCheckpointsPerSession = $config->get('checkpoint.max_checkpoints_per_session', 50);
        $this->cleanupThreshold = $config->get('checkpoint.cleanup_threshold', 0.8);
        
        $this->checkpoints = array(); // 按会话ID索引的断点
        
        $this->ensureStorageDirectory();
        $this->loadCheckpoints();
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
     * 加载所有断点
     */
    private function loadCheckpoints()
    {
        $checkpointFile = $this->storagePath . '/checkpoints.json';
        if (file_exists($checkpointFile)) {
            $content = file_get_contents($checkpointFile);
            $data = json_decode($content, true);
            
            if (is_array($data)) {
                foreach ($data as $checkpointData) {
                    $checkpoint = Checkpoint::fromArray($checkpointData);
                    $this->addCheckpointToIndex($checkpoint);
                }
            }
        }
    }

    /**
     * 将断点添加到索引
     *
     * @param Checkpoint $checkpoint
     */
    private function addCheckpointToIndex($checkpoint)
    {
        $sessionId = $checkpoint->getSessionId();
        if (!isset($this->checkpoints[$sessionId])) {
            $this->checkpoints[$sessionId] = array();
        }
        $this->checkpoints[$sessionId][$checkpoint->getId()] = $checkpoint;
    }

    /**
     * 保存所有断点到文件
     */
    private function saveCheckpoints()
    {
        $checkpointFile = $this->storagePath . '/checkpoints.json';
        $data = array();
        
        foreach ($this->checkpoints as $sessionCheckpoints) {
            foreach ($sessionCheckpoints as $checkpoint) {
                $data[] = $checkpoint->toArray();
            }
        }
        
        file_put_contents($checkpointFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * 保存断点
     *
     * @param Checkpoint $checkpoint 断点对象
     * @return bool 是否成功
     */
    public function saveCheckpoint($checkpoint)
    {
        try {
            // 验证断点有效性
            if (!$checkpoint->isValid()) {
                throw new Exception("Invalid checkpoint data");
            }
            
            $this->addCheckpointToIndex($checkpoint);
            
            // 检查是否需要清理该会话的断点
            $sessionId = $checkpoint->getSessionId();
            if (isset($this->checkpoints[$sessionId]) && 
                count($this->checkpoints[$sessionId]) > $this->maxCheckpointsPerSession * $this->cleanupThreshold) {
                $this->cleanupSessionCheckpoints($sessionId);
            }
            
            $this->saveCheckpoints();
            return true;
            
        } catch (Exception $e) {
            error_log("Failed to save checkpoint: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取指定会话的最新断点
     *
     * @param string $sessionId 会话ID
     * @return Checkpoint|null 最新的断点，如果没有则返回null
     */
    public function getLatestCheckpoint($sessionId)
    {
        if (!isset($this->checkpoints[$sessionId]) || empty($this->checkpoints[$sessionId])) {
            return null;
        }
        
        $sessionCheckpoints = $this->checkpoints[$sessionId];
        
        // 按时间戳排序，获取最新的
        $latest = null;
        foreach ($sessionCheckpoints as $checkpoint) {
            if ($latest === null || $checkpoint->getTimestamp() > $latest->getTimestamp()) {
                $latest = $checkpoint;
            }
        }
        
        return $latest;
    }

    /**
     * 获取指定会话的所有断点
     *
     * @param string $sessionId 会话ID
     * @param string|null $sortBy 排序字段 ('timestamp', 'step')
     * @param string $order 排序方向 ('asc', 'desc')
     * @return array Checkpoint对象数组
     */
    public function getCheckpointsBySession($sessionId, $sortBy = 'timestamp', $order = 'desc')
    {
        if (!isset($this->checkpoints[$sessionId])) {
            return array();
        }
        
        $sessionCheckpoints = array_values($this->checkpoints[$sessionId]);
        
        // 排序
        usort($sessionCheckpoints, function($a, $b) use ($sortBy, $order) {
            $result = 0;
            
            switch ($sortBy) {
                case 'timestamp':
                    $result = $a->getTimestamp() <=> $b->getTimestamp();
                    break;
                case 'step':
                    $result = $a->getStep() <=> $b->getStep();
                    break;
            }
            
            return $order === 'desc' ? -$result : $result;
        });
        
        return $sessionCheckpoints;
    }

    /**
     * 根据步骤获取断点
     *
     * @param string $sessionId 会话ID
     * @param int $step 步骤号
     * @return Checkpoint|null 匹配的断点
     */
    public function getCheckpointByStep($sessionId, $step)
    {
        if (!isset($this->checkpoints[$sessionId])) {
            return null;
        }
        
        foreach ($this->checkpoints[$sessionId] as $checkpoint) {
            if ($checkpoint->getStep() === $step) {
                return $checkpoint;
            }
        }
        
        return null;
    }

    /**
     * 删除指定断点
     *
     * @param string $checkpointId 断点ID
     * @return bool 是否成功
     */
    public function deleteCheckpoint($checkpointId)
    {
        foreach ($this->checkpoints as $sessionId => $sessionCheckpoints) {
            if (isset($sessionCheckpoints[$checkpointId])) {
                unset($this->checkpoints[$sessionId][$checkpointId]);
                
                // 如果会话没有其他断点了，删除整个会话索引
                if (empty($this->checkpoints[$sessionId])) {
                    unset($this->checkpoints[$sessionId]);
                }
                
                $this->saveCheckpoints();
                return true;
            }
        }
        
        return false;
    }

    /**
     * 清理会话的所有断点
     *
     * @param string $sessionId 会话ID
     * @return int 删除的断点数量
     */
    public function clearSessionCheckpoints($sessionId)
    {
        if (!isset($this->checkpoints[$sessionId])) {
            return 0;
        }
        
        $count = count($this->checkpoints[$sessionId]);
        unset($this->checkpoints[$sessionId]);
        $this->saveCheckpoints();
        
        return $count;
    }

    /**
     * 清理指定会话的旧断点
     *
     * @param string $sessionId 会话ID
     */
    private function cleanupSessionCheckpoints($sessionId)
    {
        if (!isset($this->checkpoints[$sessionId])) {
            return;
        }
        
        $sessionCheckpoints = array_values($this->checkpoints[$sessionId]);
        $currentCount = count($sessionCheckpoints);
        $targetCount = (int)($this->maxCheckpointsPerSession * 0.8); // 保留80%
        
        if ($currentCount <= $targetCount) {
            return;
        }
        
        // 按时间戳排序，保留最新的
        usort($sessionCheckpoints, function($a, $b) {
            return $b->getTimestamp() <=> $a->getTimestamp();
        });
        
        // 删除超出目标数量的旧断点
        $deleteCount = $currentCount - $targetCount;
        for ($i = $targetCount; $i < $currentCount; $i++) {
            $checkpointToDelete = $sessionCheckpoints[$i];
            $this->deleteCheckpoint($checkpointToDelete->getId());
        }
    }

    /**
     * 获取断点统计信息
     *
     * @param string|null $sessionId 会话ID，null表示全局统计
     * @return array 统计信息
     */
    public function getStatistics($sessionId = null)
    {
        if ($sessionId !== null) {
            $checkpoints = isset($this->checkpoints[$sessionId]) ? 
                          array_values($this->checkpoints[$sessionId]) : array();
        } else {
            $checkpoints = array();
            foreach ($this->checkpoints as $sessionCheckpoints) {
                $checkpoints = array_merge($checkpoints, array_values($sessionCheckpoints));
            }
        }
        
        $total = count($checkpoints);
        $earliest = null;
        $latest = null;
        $steps = array();
        
        foreach ($checkpoints as $checkpoint) {
            $timestamp = $checkpoint->getTimestamp();
            
            if ($earliest === null || $timestamp < $earliest) {
                $earliest = $timestamp;
            }
            if ($latest === null || $timestamp > $latest) {
                $latest = $timestamp;
            }
            
            $step = $checkpoint->getStep();
            if (!isset($steps[$step])) {
                $steps[$step] = 0;
            }
            $steps[$step]++;
        }
        
        return array(
            'total_count' => $total,
            'session_count' => $sessionId === null ? count($this->checkpoints) : null,
            'earliest_checkpoint' => $earliest,
            'latest_checkpoint' => $latest,
            'step_distribution' => $steps,
            'average_checkpoints_per_session' => $sessionId === null && !empty($this->checkpoints) ? 
                                               $total / count($this->checkpoints) : null
        );
    }

    /**
     * 导出断点数据
     *
     * @param string|null $sessionId 会话ID，null表示导出所有
     * @param string $format 导出格式 ('json', 'array')
     * @return mixed 导出的数据
     */
    public function export($sessionId = null, $format = 'json')
    {
        if ($sessionId !== null) {
            $checkpoints = $this->getCheckpointsBySession($sessionId);
        } else {
            $checkpoints = array();
            foreach ($this->checkpoints as $sessionCheckpoints) {
                $checkpoints = array_merge($checkpoints, array_values($sessionCheckpoints));
            }
        }
        
        $exportData = array();
        foreach ($checkpoints as $checkpoint) {
            $exportData[] = $checkpoint->toArray();
        }
        
        switch ($format) {
            case 'json':
                return json_encode($exportData, JSON_PRETTY_PRINT);
            case 'array':
            default:
                return $exportData;
        }
    }

    /**
     * 导入断点数据
     *
     * @param array|string $data 要导入的数据
     * @param string $format 数据格式 ('json', 'array')
     * @return int 成功导入的断点数量
     */
    public function import($data, $format = 'json')
    {
        if ($format === 'json') {
            $dataArray = json_decode($data, true);
        } else {
            $dataArray = $data;
        }
        
        if (!is_array($dataArray)) {
            throw new Exception("Invalid import data format");
        }
        
        $importedCount = 0;
        foreach ($dataArray as $checkpointData) {
            try {
                $checkpoint = Checkpoint::fromArray($checkpointData);
                if ($this->saveCheckpoint($checkpoint)) {
                    $importedCount++;
                }
            } catch (Exception $e) {
                error_log("Failed to import checkpoint: " . $e->getMessage());
                continue;
            }
        }
        
        return $importedCount;
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
     * 获取最大断点数配置
     *
     * @return int
     */
    public function getMaxCheckpointsPerSession()
    {
        return $this->maxCheckpointsPerSession;
    }
}