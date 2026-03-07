<?php

namespace App\Http\Controllers;

use App\Models\LlmAgent;
use App\Models\LlmConversation;
use App\Repositories\LlmSessionRepository;
use App\Services\LlmSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class LlmSessionController extends Controller
{
    protected $sessionService;
    protected $sessionRepository;
    protected $conversationHasSessionId = null;
    
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
        $sessions = $this->sessionRepository->getUserSessions(null, ['agent'], 'last_message_at', 'desc')
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'uuid' => $session->uuid,
                    'title' => $session->title ?: '未命名会话',
                    'agent_id' => $session->agent_id,
                    'agent_name' => optional($session->agent)->name,
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
            'agent_id' => 'nullable',
            'title' => 'nullable|string|max:255'
        ]);

        $temp_agent_id = $request->input('agent_id');
        $agent = null;

        if ($temp_agent_id) {
            // 检查是否包含 builtin
            if (strpos($temp_agent_id, 'builtin') !== false) {
                // 如果是 builtin，使用 builtin_slug 查询
                $agent = LlmAgent::where('builtin_slug', $temp_agent_id)->first();
            } else {
                // 否则使用 id 查询
                $agent = LlmAgent::find($temp_agent_id);
            }
        }

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => '指定智能体不存在'
            ], 404);
        }

        $agent_id = $agent->id;

        $title = $request->input('title') ?? '未命名对话';

        // 检查长度并截取
        if (mb_strlen($title) > 50) {
            $title = mb_substr($title, 0, 50);
        }


        $session = $this->sessionService->createSession([
            'agent_id' => $agent_id,
            'title' => $title
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
     * 删除最后一轮对话并返回原问题，用于重新生成
     */
    public function regenerateSession($id)
    {
        $session = $this->sessionRepository->findById($id, null, ['agent']);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => '会话不存在或无权限访问'
            ], 404);
        }

        if (!$this->conversationSupportsSession()) {
            return response()->json([
                'success' => false,
                'message' => '当前环境暂不支持会话重试'
            ], 400);
        }

        $lastConversation = LlmConversation::where('session_id', $session->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastConversation || empty($lastConversation->question)) {
            return response()->json([
                'success' => false,
                'message' => '当前会话没有可重新生成的内容'
            ], 400);
        }

        $question = $lastConversation->question;
        $lastConversation->delete();

        $latestConversation = LlmConversation::where('session_id', $session->id)
            ->orderBy('answered_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $session->message_count = LlmConversation::where('session_id', $session->id)->count();
        $session->last_message_at = $latestConversation
            ? ($latestConversation->answered_at ?: $latestConversation->updated_at ?: $latestConversation->created_at)
            : null;
        $session->save();

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $session->id,
                'agent_id' => $session->agent_id,
                'agent_name' => optional($session->agent)->name,
                'query' => $question,
            ]
        ]);
    }

    /**
     * 清空指定会话的对话内容
     */
    public function clearSession($id)
    {
        $session = $this->sessionRepository->findById($id);
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => '会话不存在或无权限操作'
            ], 404);
        }

        if ($this->conversationSupportsSession()) {
            LlmConversation::where('session_id', $session->id)->delete();
        }
        $session->message_count = 0;
        $session->token_count = 0;
        $session->last_message_at = null;
        $session->save();

        return response()->json([
            'success' => true
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

        $messages = [];
        if ($this->conversationSupportsSession()) {
            $conversations = LlmConversation::where('session_id', $session->id)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($conversations as $conv) {
                if (!empty($conv->question)) {
                    $messages[] = [
                        'role' => 'user',
                        'content' => $conv->question,
                        'created_at' => optional($conv->created_at)->format('Y-m-d H:i:s'),
                    ];
                }
                if (!empty($conv->answer)) {
                    $messages[] = [
                        'role' => 'assistant',
                        'content' => $conv->answer,
                        'created_at' => optional($conv->answered_at ?: $conv->updated_at)->format('Y-m-d H:i:s'),
                    ];
                }
            }
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
                'agent_name' => optional($session->agent)->name,
                'is_pinned' => $session->is_pinned,
                'last_message_at' => $session->last_message_at ? $session->last_message_at->format('Y-m-d H:i:s') : null,
                'updated_at' => optional($session->updated_at)->format('Y-m-d H:i:s'),
                'messages' => $messages,
            ]
        ]);
    }

    protected function conversationSupportsSession()
    {
        if ($this->conversationHasSessionId !== null) {
            return $this->conversationHasSessionId;
        }
        $this->conversationHasSessionId = Schema::hasColumn('llm_conversations', 'session_id');
        return $this->conversationHasSessionId;
    }
}
