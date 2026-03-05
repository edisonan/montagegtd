<?php

namespace App\Http\Controllers;

use App\Services\AchievementService;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    protected $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->middleware('auth');
        $this->achievementService = $achievementService;
    }

    public function index(Request $request)
    {
        return view('achievement.index');
    }

    public function claim(Request $request)
    {
        $this->validate($request, array(
            'achievement_code' => 'required|string',
        ));

        $userId = \Auth::id();
        $this->achievementService->claimBadge((int)$userId, (string)$request->input('achievement_code'));

        return redirect()->back()->with('success', '领取成功');
    }
}
