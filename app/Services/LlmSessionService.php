<?php

namespace App\Services;

use App\Models\LlmConversation;
use App\Models\LlmSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LlmSessionService
{
    protected $attachmentService;

    public function __construct(LlmAttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     * 创建新的会话
     *
     * @param array $data
     * @return LlmSession
     */
    public function createSession(array $data)
    {
        $session = new LlmSession();
        $session->uuid = $this->generateUuid();
        $session->user_id = Auth::id();
        $session->agent_id = $data['agent_id'] ?? null;
        $session->title = $data['title'] ?? null;
        $session->save();

        return $session;
    }

    public function createBranch(LlmSession $sourceSession, LlmConversation $targetConversation)
    {
        $branchOrder = LlmSession::where('parent_session_id', $sourceSession->id)->max('branch_order') + 1;
        $baseTitle = preg_replace('/\s*·\s*分支\s*\d+$/u', '', $sourceSession->title ?: '未命名会话');
        $branch = new LlmSession();
        $branch->uuid = $this->generateUuid();
        $branch->user_id = $sourceSession->user_id;
        $branch->agent_id = $sourceSession->agent_id;
        $branch->parent_session_id = $sourceSession->id;
        $branch->branched_from_conversation_id = $targetConversation->id;
        $branch->branch_order = $branchOrder;
        $branch->title = mb_substr($baseTitle . ' · 分支 ' . $branchOrder, 0, 255);
        $branch->save();

        try {
            $sourceConversations = LlmConversation::where('session_id', $sourceSession->id)
                ->where('id', '<', $targetConversation->id)
                ->orderBy('id', 'asc')
                ->get();
            foreach ($sourceConversations as $sourceConversation) {
                $conversation = LlmConversation::create([
                    'user_id' => $sourceConversation->user_id,
                    'session_id' => $branch->id,
                    'model_id' => $sourceConversation->model_id,
                    'question' => $sourceConversation->question,
                    'answer' => $sourceConversation->answer,
                    'feedback' => $sourceConversation->feedback,
                    'request_data' => $sourceConversation->request_data,
                    'response_data' => $sourceConversation->response_data,
                    'prompt_tokens' => (int)$sourceConversation->prompt_tokens,
                    'completion_tokens' => (int)$sourceConversation->completion_tokens,
                    'total_tokens' => (int)$sourceConversation->total_tokens,
                    'cost' => $sourceConversation->cost,
                    'answered_at' => $sourceConversation->answered_at,
                ]);
                $this->attachmentService->cloneForConversation($sourceConversation->id, $branch->id, $conversation->id);
            }

            $latestConversation = LlmConversation::where('session_id', $branch->id)->orderBy('id', 'desc')->first();
            $branch->message_count = $sourceConversations->count();
            $branch->token_count = (int)$sourceConversations->sum('total_tokens');
            $branch->last_message_at = $latestConversation
                ? ($latestConversation->answered_at ?: $latestConversation->updated_at ?: $latestConversation->created_at)
                : null;
            $branch->save();

            return [
                'session' => $branch,
                'attachments' => $this->attachmentService->cloneAsPending($targetConversation->id, $branch->id),
            ];
        } catch (\Throwable $e) {
            $this->attachmentService->deleteForSession($branch->id);
            LlmConversation::where('session_id', $branch->id)->delete();
            $branch->delete();
            throw $e;
        }
    }

    /**
     * 生成 UUID
     *
     * @return string
     */
    private function generateUuid()
    {
        // 为 Laravel 5.5 生成 UUID 的兼容方法
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * 获取用户的会话列表
     *
     * @param int|null $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserSessions($userId = null)
    {
        $userId = $userId ?: Auth::id();
        
        return LlmSession::where('user_id', $userId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    /**
     * 根据ID获取会话
     *
     * @param int $id
     * @param int|null $userId
     * @return LlmSession|null
     */
    public function getSessionById($id, $userId = null)
    {
        $userId = $userId ?: Auth::id();
        
        return LlmSession::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * 更新会话标题
     *
     * @param int $id
     * @param string $title
     * @return bool
     */
    public function updateSessionTitle($id, $title)
    {
        $session = $this->getSessionById($id);
        
        if (!$session) {
            return false;
        }

        $session->title = $title;
        return $session->save();
    }

    /**
     * 删除会话
     *
     * @param int $id
     * @return bool
     */
    public function deleteSession($id)
    {
        $session = $this->getSessionById($id);
        
        if (!$session) {
            return false;
        }

        if (Schema::hasColumn('llm_conversations', 'session_id')) {
            $this->attachmentService->deleteForSession($session->id);
            LlmConversation::where('session_id', $session->id)->delete();
        }

        return $session->delete();
    }

    /**
     * 固定/取消固定会话
     *
     * @param int $id
     * @return bool
     */
    public function togglePinSession($id)
    {
        $session = $this->getSessionById($id);
        
        if (!$session) {
            return false;
        }

        $session->is_pinned = !$session->is_pinned;
        return $session->save();
    }

    /**
     * 更新会话统计信息
     *
     * @param int $sessionId
     * @param int $messageCountIncrement
     * @param int $tokenCountIncrement
     * @return bool
     */
    public function updateSessionStats($sessionId, $messageCountIncrement = 0, $tokenCountIncrement = 0)
    {
        $session = $this->getSessionById($sessionId);
        
        if (!$session) {
            return false;
        }

        if ($messageCountIncrement != 0) {
            $session->message_count += $messageCountIncrement;
        }
        
        if ($tokenCountIncrement != 0) {
            $session->token_count += $tokenCountIncrement;
        }
        
        $session->last_message_at = now();
        return $session->save();
    }

    /**
     * 根据UUID获取会话
     *
     * @param string $uuid
     * @param int|null $userId
     * @return LlmSession|null
     */
    public function getSessionByUuid($uuid, $userId = null)
    {
        $userId = $userId ?: Auth::id();
        
        return LlmSession::where('uuid', $uuid)
            ->where('user_id', $userId)
            ->first();
    }
}
