<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AchievementService;

/**
 * 成就控制器
 */
class AchievementController extends Controller {

    protected $achievementService;

    public function __construct(AchievementService $achievementService) {
        $this->middleware('auth');
        $this->achievementService = $achievementService;
    }

    /**
     * 成就列表
     */
    public function index(Request $request) {
        $userId = \Auth::id();

        $list = $this->achievementService->getUserAchievements($userId);

        return view('achievement.index', [
            'list' => $list,
        ]);
    }

    /**
     * 手动领取勋章
     */
    public function claim(Request $request) {
        $this->validate($request, [
            'achievement_code' => 'required|string',
        ]);

        $userId = \Auth::id();

        $this->achievementService->claimBadge(
            $userId,
            $request->achievement_code
        );

        return redirect()->back()->with('success', '领取成功');
    }
}
