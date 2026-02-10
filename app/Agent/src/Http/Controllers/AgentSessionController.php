<?php

namespace App\Agent\Http\Controllers;

use App\Agent\Services\AgentSessionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Agent 会话控制器
 * 
 * 提供 RESTful API 接口用于管理 Agent 会话。
 */
class AgentSessionController extends Controller
{
    private AgentSessionService $agentSessionService;

    public function __construct(AgentSessionService $agentSessionService)
    {
        $this->agentSessionService = $agentSessionService;
    }

    /**
     * 获取 Agent 会话历史
     *
     * @param Request $request
     * @param int $agentId
     * @return JsonResponse
     */
    public function getHistory(Request $request, int $agentId): JsonResponse
    {
        try {
            $userId = $request->user()?->id;
            $numRuns = $request->input('num_runs', 3);
            $maxChars = $request->input('max_chars', 1000);

            $history = $this->agentSessionService->getAgentHistoryContext(
                $agentId,
                $userId,
                $numRuns,
                $maxChars
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'agent_id' => $agentId,
                    'user_id' => $userId,
                    'history' => $history,
                    'num_runs' => $numRuns
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取会话统计信息
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $agentId = $request->input('agent_id');
            $userId = $request->user()?->id;

            $stats = $this->agentSessionService->getSessionStats($agentId, $userId);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 清理会话
     *
     * @param Request $request
     * @param int $agentId
     * @return JsonResponse
     */
    public function clearSession(Request $request, int $agentId): JsonResponse
    {
        try {
            $userId = $request->user()?->id;
            
            $deleted = $this->agentSessionService->deleteSession($agentId, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'agent_id' => $agentId,
                    'user_id' => $userId,
                    'deleted' => $deleted
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 清理过期会话
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cleanupSessions(Request $request): JsonResponse
    {
        try {
            $maxAgeDays = $request->input('max_age_days', 7);
            
            $deletedCount = $this->agentSessionService->cleanupOldSessions($maxAgeDays);

            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_count' => $deletedCount,
                    'max_age_days' => $maxAgeDays
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}