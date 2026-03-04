<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\AchievementService;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    protected $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    public function index(Request $request)
    {
        $userId = $this->getAuthUserId($request);
        $list = $this->achievementService->getUserAchievements((int)$userId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'list' => $list,
        )));
    }

    public function claim(Request $request)
    {
        $this->validate($request, array(
            'achievement_code' => 'required|string',
        ));

        $userId = $this->getAuthUserId($request);
        $code = (string)$request->input('achievement_code');
        $this->achievementService->claimBadge((int)$userId, $code);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'achievement_code' => $code,
        )));
    }
}

