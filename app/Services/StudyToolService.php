<?php

namespace App\Services;

use App\Models\StudyToolMessage;
use App\Models\StudyToolRoom;
use Illuminate\Support\Str;

/**
 * 学习工具服务：WebRTC 远程辅导的房间与信令消息中继
 */
class StudyToolService
{
    /**
     * 生成不重复的房间号（16 位以内，去掉易混淆字符）
     */
    public function generateRoomCode()
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = '';
            $len = strlen($alphabet);
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[rand(0, $len - 1)];
            }
            $exists = StudyToolRoom::where('code', $code)
                ->where('state', '<>', StudyToolRoom::STATE_CLOSED)
                ->exists();
            if (!$exists) {
                return $code;
            }
        }
        return strtoupper(Str::random(8));
    }

    /**
     * 创建房间
     */
    public function createRoom($userId, $kind = 'tutoring')
    {
        $ttlMinutes = (int)config('study_tools.room_ttl_minutes', 120);
        $room = StudyToolRoom::create(array(
            'code' => $this->generateRoomCode(),
            'kind' => $kind,
            'owner_user_id' => (int)$userId,
            'state' => StudyToolRoom::STATE_WAITING,
            'expires_at' => date('Y-m-d H:i:s', time() + $ttlMinutes * 60),
            'meta' => null,
        ));
        return $this->roomData($room);
    }

    /**
     * 按房号查找可用房间；已过期/已关闭则返回 null（并懒清理）
     */
    public function findRoomByCode($code)
    {
        $room = StudyToolRoom::where('code', (string)$code)
            ->where('state', '<>', StudyToolRoom::STATE_CLOSED)
            ->first();
        if (!$room) {
            return null;
        }
        if ($room->isExpired()) {
            $room->state = StudyToolRoom::STATE_CLOSED;
            $room->save();
            return null;
        }
        return $room;
    }

    /**
     * 房间对外数据结构
     */
    public function roomData(StudyToolRoom $room)
    {
        return array(
            'room_id' => (int)$room->id,
            'code' => (string)$room->code,
            'kind' => (string)$room->kind,
            'state' => (int)$room->state,
            'owner_user_id' => (int)$room->owner_user_id,
            'expires_at' => (string)$room->expires_at,
            'server_time' => date('Y-m-d H:i:s'),
            'peer_count' => $this->countActivePeers($room),
        );
    }

    /**
     * 活跃成员数：近 3 分钟内出现过的 (sender_user_id, peer_id) 组合数
     * 可通过 $excludePeerId 排除自身（用于轮询接口展示“对方在线”）
     */
    public function countActivePeers(StudyToolRoom $room, $excludePeerId = '')
    {
        $since = date('Y-m-d H:i:s', time() - 180);
        $rows = StudyToolMessage::where('room_id', (int)$room->id)
            ->where('created_at', '>=', $since)
            ->whereIn('type', array('join', 'ping', 'offer', 'wb'))
            ->get(array('sender_user_id', 'peer_id'));
        $seen = array();
        foreach ($rows as $row) {
            if ($excludePeerId !== '' && (string)$row->peer_id === (string)$excludePeerId) {
                continue;
            }
            $key = (int)$row->sender_user_id . ':' . (string)$row->peer_id;
            $seen[$key] = true;
        }
        return count($seen);
    }

    /**
     * 发送一条房间消息；返回消息记录
     */
    public function sendMessage(StudyToolRoom $room, $userId, $peerId, $type, $payload)
    {
        if (is_array($payload) || $payload === null) {
            $payloadJson = $payload === null ? '' : json_encode($payload, JSON_UNESCAPED_UNICODE);
        } else {
            $payloadJson = (string)$payload;
        }

        $message = StudyToolMessage::create(array(
            'room_id' => (int)$room->id,
            'sender_user_id' => (int)$userId,
            'peer_id' => substr((string)$peerId, 0, 64),
            'type' => substr((string)$type, 0, 32),
            'payload' => $payloadJson,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return $message;
    }

    /**
     * 增量拉取消息（以 id 为游标）
     */
    public function pollMessages(StudyToolRoom $room, $sinceId = 0, $limit = 200)
    {
        $limit = max(1, min((int)$limit, (int)config('study_tools.message_poll_limit', 200)));
        $query = StudyToolMessage::where('room_id', (int)$room->id)
            ->where('id', '>', (int)$sinceId)
            ->orderBy('id', 'asc')
            ->limit($limit);
        $rows = $query->get();
        $list = array();
        $maxId = (int)$sinceId;
        foreach ($rows as $row) {
            $maxId = max($maxId, (int)$row->id);
            $list[] = array(
                'id' => (int)$row->id,
                'type' => (string)$row->type,
                'sender_user_id' => (int)$row->sender_user_id,
                'peer_id' => (string)$row->peer_id,
                'payload' => $row->payloadArray(),
                'created_at' => (string)$row->created_at,
            );
        }
        return array(
            'list' => $list,
            'since' => $maxId,
            'has_more' => count($list) === $limit,
            'server_time' => date('Y-m-d H:i:s'),
            'room_state' => (int)$room->state,
        );
    }

    /**
     * 关闭房间（仅房主）
     */
    public function closeRoom(StudyToolRoom $room, $userId)
    {
        if ((int)$room->owner_user_id !== (int)$userId) {
            return false;
        }
        $room->state = StudyToolRoom::STATE_CLOSED;
        $room->save();
        return true;
    }

    /**
     * 过期房间与消息清理（命令与懒清理共用）
     */
    public function cleanupExpired()
    {
        $expired = StudyToolRoom::where(function ($q) {
            $q->where('expires_at', '<', date('Y-m-d H:i:s'))
                ->orWhere('state', StudyToolRoom::STATE_CLOSED);
        })->limit(500)->get();

        $deletedRooms = 0;
        foreach ($expired as $room) {
            StudyToolMessage::where('room_id', (int)$room->id)->delete();
            $room->delete();
            $deletedRooms++;
        }
        return array(
            'rooms_deleted' => $deletedRooms,
        );
    }

    /**
     * 校验消息 type 白名单
     */
    public function isValidMessageType($type)
    {
        $allowed = array(
            'offer', 'answer', 'ice', 'join', 'leave',
            'ping', 'file', 'file_ack',
            'wb', 'wb_clear', 'wb_undo',
            'content_url', 'renegotiate', 'sync_req', 'scroll',
            'interact', 'zoom',
        );
        return in_array((string)$type, $allowed, true);
    }
}