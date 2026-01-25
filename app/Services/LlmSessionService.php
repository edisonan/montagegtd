<?php

namespace App\Services;

use App\Models\LlmSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LlmSessionService
{
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