<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\UserNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $userNotificationService;

    public function __construct(UserNotificationService $userNotificationService)
    {
        $this->userNotificationService = $userNotificationService;
    }

    public function index(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $limit = (int)$request->query('limit', 20);

        $list = $this->userNotificationService->getRecentByUser($userId, $limit);
        $unreadCount = $this->userNotificationService->getUnreadCount($userId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'list' => $list,
            'unread_count' => $unreadCount,
        )));
    }

    public function markRead(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $ok = $this->userNotificationService->markRead($userId, (int)$id);

        if (!$ok) {
            return $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '通知不存在或无权限'));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'id' => (int)$id,
        )));
    }

    public function markAllRead(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $updated = $this->userNotificationService->markAllRead($userId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'updated_count' => $updated,
        )));
    }
}

