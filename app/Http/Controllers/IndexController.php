<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PomoRepository;
use App\Repositories\GoalRepository;
use App\Services\PomoService;

class IndexController extends Controller
{

    /**
     *
     * @var PomoService $pomoService
     */
    protected $pomoService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PomoService $pomoService, PomoRepository $pomos, GoalRepository $goals)
    {
        $this->middleware('auth');
        
        $this->pomoService = $pomoService;
    }

    /**
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        // 获取当前活动信息
        $currentPomoInfo = $this->pomoService->getCurrentPomoInfo($request->user());
        
        // 标题栏相关提示
        $tipInfo = $this->pomoService->getTipInfo($currentPomoInfo['current_pomo_status']);
        
        return view('index.index', array_merge($currentPomoInfo, $tipInfo));
    }
}
