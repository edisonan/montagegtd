<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\StudyToolRoom;
use App\Services\StudyToolService;
use Illuminate\Http\Request;

/**
 * 学习工具 - 远程辅导房间与信令中继 API
 *
 * 信令（offer/answer/ice）与白板降级消息经 HTTP 轮询中转，
 * 无 WebSocket 依赖；音视频媒体流走 WebRTC P2P。
 */
class StudyToolController extends Controller
{
    protected $studyToolService;

    public function __construct(StudyToolService $studyToolService)
    {
        $this->studyToolService = $studyToolService;
    }

    /**
     * POST /api/v2/study/tools/rooms  创建房间
     */
    public function createRoom(Request $request)
    {
        $this->validate($request, array(
            'kind' => 'nullable|string|max:32',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $kind = (string)$request->input('kind', 'tutoring');
        if (!in_array($kind, array('tutoring'), true)) {
            return $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '暂不支持的工具类型'));
        }

        $room = $this->studyToolService->createRoom($userId, $kind);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($room));
    }

    /**
     * GET /api/v2/study/tools/rooms/{code}  房间信息
     */
    public function showRoom(Request $request, $code)
    {
        $room = $this->roomOrFail($request, $code);
        if (!$room) {
            return null;
        }
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(
            $this->studyToolService->roomData($room)
        ));
    }

    /**
     * POST /api/v2/study/tools/rooms/{code}/join  加入房间
     */
    public function joinRoom(Request $request, $code)
    {
        $this->validate($request, array(
            'role' => 'nullable|string|max:16',
            'password' => 'nullable|string|max:64',
        ));

        $joinPassword = (string)config('study_tools.room_join_password', '');
        $providedPassword = (string)$request->input('password', '');
        if ($joinPassword !== '' && !hash_equals($joinPassword, $providedPassword)) {
            return $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '房间口令不正确'));
        }

        $room = $this->roomOrFail($request, $code);
        if (!$room) {
            return null;
        }
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(
            $this->studyToolService->roomData($room)
        ));
    }

    /**
     * POST /api/v2/study/tools/rooms/{code}/messages  发送信令/白板消息
     */
    public function sendMessage(Request $request, $code)
    {
        $this->validate($request, array(
            'type' => 'required|string|max:32',
            'payload' => 'nullable',
            'peer_id' => 'nullable|string|max:64',
        ));

        $room = $this->roomOrFail($request, $code);
        if (!$room) {
            return null;
        }

        $type = (string)$request->input('type');
        if (!$this->studyToolService->isValidMessageType($type)) {
            return $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '不支持的消息类型'));
        }

        $payload = $request->input('payload');
        $payloadSize = is_array($payload)
            ? strlen(json_encode($payload, JSON_UNESCAPED_UNICODE))
            : strlen((string)$payload);
        if ($payloadSize > (int)config('study_tools.message_payload_max', 65536)) {
            return $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '消息体过大'));
        }

        $userId = (int)$this->getAuthUserId($request);
        $peerId = (string)$request->input('peer_id', '');
        $message = $this->studyToolService->sendMessage($room, $userId, $peerId, $type, $payload);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'id' => (int)$message->id,
            'server_time' => date('Y-m-d H:i:s'),
        )));
    }

    /**
     * GET /api/v2/study/tools/rooms/{code}/messages?since=<id>  增量拉取
     */
    public function pollMessages(Request $request, $code)
    {
        $this->validate($request, array(
            'since' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:200',
            'peer_id' => 'nullable|string|max:64',
        ));

        $room = $this->roomOrFail($request, $code);
        if (!$room) {
            return null;
        }

        $since = (int)$request->input('since', 0);
        $limit = (int)$request->input('limit', 200);
        $data = $this->studyToolService->pollMessages($room, $since, $limit);
        $data['peers'] = $this->studyToolService->countActivePeers($room, (string)$request->input('peer_id', ''));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($data));
    }

    /**
     * POST /api/v2/study/tools/rooms/{code}/close  关闭房间（仅房主）
     */
    public function closeRoom(Request $request, $code)
    {
        $room = $this->roomOrFail($request, $code);
        if (!$room) {
            return null;
        }

        $userId = (int)$this->getAuthUserId($request);
        $closed = $this->studyToolService->closeRoom($room, $userId);
        if (!$closed) {
            return $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '仅房主可关闭房间'));
        }
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'code' => (string)$room->code,
            'state' => StudyToolRoom::STATE_CLOSED,
        )));
    }

    /**
     * 查找房间；不存在/已过期返回 JSON 错误并返回 null
     */
    protected function roomOrFail(Request $request, $code)
    {
        $code = strtoupper(trim((string)$code));
        if (!preg_match('/^[A-Z0-9]{4,12}$/', $code)) {
            $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '房间号格式不正确'))->send();
            return null;
        }
        $room = $this->studyToolService->findRoomByCode($code);
        if (!$room) {
            $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '房间不存在或已过期'))->send();
            return null;
        }
        return $room;
    }
}