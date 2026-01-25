<?php

namespace App\Http\Controllers;

use App\Models\LlmAgent;
use App\Repositories\LlmSessionRepository;
use App\Services\LlmSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LlmSessionController extends Controller
{
    protected $sessionService;
    protected $sessionRepository;
    
    public function __construct(
        LlmSessionService $sessionService,
        LlmSessionRepository $sessionRepository
    ) {
        $this->sessionService = $sessionService;
        $this->sessionRepository = $sessionRepository;
    }
    
    /**
     * 显示AI助手主页
     */
    public function index()
    {
        return view('llm.index');
    }

    /**
     * 获取用户的会话列表
     */
    public function getSessions(Request $request)
    {
        $sessions = $this->sessionRepository->getUserSessions(null, [], 'last_message_at', 'desc')
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'uuid' => $session->uuid,
                    'title' => $session->title ?: '未命名会话',
                    'is_pinned' => $session->is_pinned,
                    'last_message_at' => $session->last_message_at ? $session->last_message_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $session->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * 创建新会话
     */
    public function createSession(Request $request)
    {
        $this->validate($request, [
            'agent_id' => 'nullable|exists:llm_agents,id',
            'title' => 'nullable|string|max:255'
        ]);

        $session = $this->sessionService->createSession([
            'agent_id' => $request->input('agent_id'),
            'title' => $request->input('title')
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'uuid' => $session->uuid,
                'title' => $session->title ?: '未命名会话',
                'agent_id' => $session->agent_id
            ]
        ]);
    }

    /**
     * 更新会话标题
     */
    public function updateSessionTitle(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255'
        ]);

        $result = $this->sessionService->updateSessionTitle($id, $request->input('title'));
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => '会话不存在或无权限修改'
            ], 404);
        }
        
        $session = $this->sessionRepository->findById($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'title' => $session->title
            ]
        ]);
    }

    /**
     * 删除会话
     */
    public function deleteSession($id)
    {
        $result = $this->sessionService->deleteSession($id);
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => '会话不存在或无权限删除'
            ], 404);
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * 固定/取消固定会话
     */
    public function togglePinSession($id)
    {
        $result = $this->sessionService->togglePinSession($id);
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => '会话不存在或无权限操作'
            ], 404);
        }
        
        $session = $this->sessionRepository->findById($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'is_pinned' => $session->is_pinned
            ]
        ]);
    }

    /**
     * 获取指定会话详情
     */
    public function getSession($id)
    {
        $session = $this->sessionRepository->findById($id, null, ['agent']);
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => '会话不存在或无权限访问'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'uuid' => $session->uuid,
                'title' => $session->title ?: '未命名会话',
                'agent_id' => $session->agent_id,
                'agent' => $session->agent ? [
                    'id' => $session->agent->id,
                    'name' => $session->agent->name,
                    'description' => $session->agent->description,
                    'prompt' => $session->agent->prompt,
                ] : null,
                'is_pinned' => $session->is_pinned,
                'last_message_at' => $session->last_message_at ? $session->last_message_at->format('Y-m-d H:i:s') : null,
            ]
        ]);
    }

    /**
     * 获取智能体列表
     */
    public function getAgents()
    {
        try {
            $userId = Auth::id();
            
            // 获取所有公开激活的智能体以及当前用户的智能体
            $agents = LlmAgent::where('is_active', true)
                ->where(function ($query) use ($userId) {
                    $query->where('is_public', true)
                          ->orWhere('user_id', $userId);
                })
                ->select('id', 'name', 'description', 'avatar')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $agents
            ]);
        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => '获取智能体列表失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 处理聊天消息
     */
    public function chat(Request $request)
    {
        $this->validate($request, [
            'message' => 'required|string',
            'session_id' => 'required|integer|exists:llm_sessions,id',
            'agent_id' => 'nullable|integer|exists:llm_agents,id'
        ]);
        
        $message = $request->input('message');
        $sessionId = $request->input('session_id');
        $agentId = $request->input('agent_id');
        
        // 获取会话和智能体信息
        $session = $this->sessionRepository->findById($sessionId);
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => '会话不存在或无权限访问'
            ], 404);
        }
        
        // 获取智能体信息
        $agent = null;
        if ($agentId) {
            $agent = \App\Models\LlmAgent::find($agentId);
        } elseif ($session->agent_id) {
            $agent = $session->agent;
        }
        
        try {
            // 为了兼容Laravel 5.5，我们暂时返回一个模拟响应
            // 在实际实现中，这里应该调用实际的AI服务
            $aiResponse = "这是来自AI的模拟回复。在实际应用中，这里将连接到真实的AI模型。";
            
            // 更新会话统计信息
            $this->sessionService->updateSessionStats($sessionId, 2, strlen($message) + strlen($aiResponse));
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user_message' => $message,
                    'ai_response' => $aiResponse,
                    'session_id' => $sessionId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI服务调用失败: ' . $e->getMessage()
            ]);
        }
    }
}