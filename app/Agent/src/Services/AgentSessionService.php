<?php

namespace App\Agent\Services;

use App\Agent\Session\SessionManager;
use App\Agent\Session\AgentSession;
use App\Agent\Session\AgentRunRecord;
use App\Services\LlmAgentService;
use App\Models\LlmAgent;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Laravel 集成的 Agent 会话服务
 * 
 * 将 PHP Agent 框架的会话管理与 Laravel 的 LLM Agent 系统集成。
 */
class AgentSessionService
{
    /**
     * 会话管理器
     *
     * @var SessionManager
     */
    private SessionManager $sessionManager;

    /**
     * LLM Agent 服务
     *
     * @var LlmAgentService
     */
    private LlmAgentService $llmAgentService;

    /**
     * 构造函数
     *
     * @param SessionManager $sessionManager 会话管理器
     * @param LlmAgentService $llmAgentService LLM Agent 服务
     */
    public function __construct(SessionManager $sessionManager, LlmAgentService $llmAgentService)
    {
        $this->sessionManager = $sessionManager;
        $this->llmAgentService = $llmAgentService;
    }

    /**
     * 为 LLM Agent 创建会话
     *
     * @param int $agentId Agent ID
     * @param int|null $userId 用户 ID
     * @param string|null $sessionId 自定义会话 ID
     * @return AgentSession
     */
    public function createAgentSession(int $agentId, ?int $userId = null, ?string $sessionId = null): AgentSession
    {
        try {
            // 获取 Agent 信息
            $agent = $this->llmAgentService->getAgentById($agentId);
            if (!$agent) {
                throw new Exception("Agent not found: {$agentId}");
            }

            // 生成会话 ID（如果未提供）
            $sessionKey = $sessionId ?? $this->generateSessionKey($agentId, $userId);
            
            // 创建会话
            $session = $this->sessionManager->getSession(
                $sessionKey,
                $agent->name,
                $userId ? (string)$userId : null
            );

            Log::info("Created agent session", [
                'agent_id' => $agentId,
                'agent_name' => $agent->name,
                'session_id' => $sessionKey,
                'user_id' => $userId
            ]);

            return $session;
            
        } catch (Exception $e) {
            Log::error("Failed to create agent session: " . $e->getMessage(), [
                'agent_id' => $agentId,
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);
            throw $e;
        }
    }

    /**
     * 记录 Agent 运行结果
     *
     * @param string $sessionId 会话 ID
     * @param string $task 用户任务
     * @param string $response Agent 响应
     * @param bool $success 是否成功
     * @param int $steps 执行步数
     * @param array $metadata 元数据
     * @return AgentRunRecord
     */
    public function recordAgentRun(
        string $sessionId,
        string $task,
        string $response,
        bool $success,
        int $steps,
        array $metadata = []
    ): AgentRunRecord {
        try {
            $runId = uniqid('run_', true);
            
            $runRecord = new AgentRunRecord(
                $runId,
                $task,
                $response,
                $success,
                $steps,
                null,
                $metadata
            );

            $added = $this->sessionManager->addRun($sessionId, $runRecord);
            
            if (!$added) {
                throw new Exception("Failed to add run record to session: {$sessionId}");
            }

            Log::info("Recorded agent run", [
                'session_id' => $sessionId,
                'run_id' => $runId,
                'success' => $success,
                'steps' => $steps
            ]);

            return $runRecord;
            
        } catch (Exception $e) {
            Log::error("Failed to record agent run: " . $e->getMessage(), [
                'session_id' => $sessionId,
                'task' => mb_substr($task, 0, 100) . '...'
            ]);
            throw $e;
        }
    }

    /**
     * 获取 Agent 会话的历史消息
     *
     * @param int $agentId Agent ID
     * @param int|null $userId 用户 ID
     * @param int|null $numRuns 返回最近 N 轮运行
     * @param int $maxResponseChars 响应最大字符数
     * @return array 历史消息
     */
    public function getAgentHistoryMessages(
        int $agentId,
        ?int $userId = null,
        ?int $numRuns = 3,
        int $maxResponseChars = 800
    ): array {
        try {
            $sessionKey = $this->generateSessionKey($agentId, $userId);
            
            if (!$this->sessionManager->hasSession($sessionKey)) {
                return [];
            }

            $session = $this->sessionManager->getSession($sessionKey);
            return $session->getHistoryMessages($numRuns, $maxResponseChars);
            
        } catch (Exception $e) {
            Log::error("Failed to get agent history messages: " . $e->getMessage(), [
                'agent_id' => $agentId,
                'user_id' => $userId
            ]);
            return [];
        }
    }

    /**
     * 获取 Agent 会话的历史上下文
     *
     * @param int $agentId Agent ID
     * @param int|null $userId 用户 ID
     * @param int|null $numRuns 返回最近 N 轮运行
     * @param int|null $maxChars 最大字符数
     * @return string 历史上下文
     */
    public function getAgentHistoryContext(
        int $agentId,
        ?int $userId = null,
        ?int $numRuns = 3,
        ?int $maxChars = null
    ): string {
        try {
            $sessionKey = $this->generateSessionKey($agentId, $userId);
            
            if (!$this->sessionManager->hasSession($sessionKey)) {
                return "";
            }

            $session = $this->sessionManager->getSession($sessionKey);
            return $session->getHistoryContext($numRuns, $maxChars);
            
        } catch (Exception $e) {
            Log::error("Failed to get agent history context: " . $e->getMessage(), [
                'agent_id' => $agentId,
                'user_id' => $userId
            ]);
            return "";
        }
    }

    /**
     * 获取会话统计信息
     *
     * @param int|null $agentId Agent ID（可选）
     * @param int|null $userId 用户 ID（可选）
     * @return array 统计信息
     */
    public function getSessionStats(?int $agentId = null, ?int $userId = null): array
    {
        try {
            $overallStats = $this->sessionManager->getStats();
            
            if ($agentId !== null) {
                $sessionKey = $this->generateSessionKey($agentId, $userId);
                if ($this->sessionManager->hasSession($sessionKey)) {
                    $session = $this->sessionManager->getSession($sessionKey);
                    $overallStats['agent_specific'] = [
                        'agent_id' => $agentId,
                        'user_id' => $userId,
                        'runs_count' => $session->getRunsCount(),
                        'session_created' => $session->getFormattedCreatedAt(),
                        'session_updated' => $session->getFormattedUpdatedAt()
                    ];
                }
            }
            
            return $overallStats;
            
        } catch (Exception $e) {
            Log::error("Failed to get session stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 清理过期会话
     *
     * @param int $maxAgeDays 最大保留天数
     * @return int 清理的会话数量
     */
    public function cleanupOldSessions(int $maxAgeDays = 7): int
    {
        try {
            $deletedCount = $this->sessionManager->cleanupOldSessions($maxAgeDays);
            
            Log::info("Cleaned up old sessions", [
                'deleted_count' => $deletedCount,
                'max_age_days' => $maxAgeDays
            ]);
            
            return $deletedCount;
            
        } catch (Exception $e) {
            Log::error("Failed to cleanup old sessions: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 裁剪会话运行记录
     *
     * @param string $sessionId 会话 ID
     * @param int $maxRuns 最大保留运行数
     * @return int 删除的运行记录数量
     */
    public function trimSessionRuns(string $sessionId, int $maxRuns = 100): int
    {
        try {
            $trimmedCount = $this->sessionManager->trimSessionRuns($sessionId, $maxRuns);
            
            if ($trimmedCount > 0) {
                Log::info("Trimmed session runs", [
                    'session_id' => $sessionId,
                    'max_runs' => $maxRuns,
                    'trimmed_count' => $trimmedCount
                ]);
            }
            
            return $trimmedCount;
            
        } catch (Exception $e) {
            Log::error("Failed to trim session runs: " . $e->getMessage(), [
                'session_id' => $sessionId
            ]);
            return 0;
        }
    }

    /**
     * 获取会话管理器
     *
     * @return SessionManager
     */
    public function getSessionManager(): SessionManager
    {
        return $this->sessionManager;
    }

    /**
     * 生成会话键
     *
     * @param int $agentId Agent ID
     * @param int|null $userId 用户 ID
     * @return string 会话键
     */
    private function generateSessionKey(int $agentId, ?int $userId = null): string
    {
        if ($userId !== null) {
            return "agent_{$agentId}_user_{$userId}";
        }
        return "agent_{$agentId}_shared";
    }

    /**
     * 验证会话是否存在
     *
     * @param int $agentId Agent ID
     * @param int|null $userId 用户 ID
     * @return bool
     */
    public function hasSession(int $agentId, ?int $userId = null): bool
    {
        $sessionKey = $this->generateSessionKey($agentId, $userId);
        return $this->sessionManager->hasSession($sessionKey);
    }

    /**
     * 删除会话
     *
     * @param int $agentId Agent ID
     * @param int|null $userId 用户 ID
     * @return bool 是否删除成功
     */
    public function deleteSession(int $agentId, ?int $userId = null): bool
    {
        try {
            $sessionKey = $this->generateSessionKey($agentId, $userId);
            $deleted = $this->sessionManager->deleteSession($sessionKey);
            
            if ($deleted) {
                Log::info("Deleted agent session", [
                    'agent_id' => $agentId,
                    'user_id' => $userId,
                    'session_key' => $sessionKey
                ]);
            }
            
            return $deleted;
            
        } catch (Exception $e) {
            Log::error("Failed to delete agent session: " . $e->getMessage(), [
                'agent_id' => $agentId,
                'user_id' => $userId
            ]);
            return false;
        }
    }
}