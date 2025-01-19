<?php

namespace App\Http\Controllers;

use App\Http\Utils\ResponseDataUtil;
use App\Models\DailySummary;
use App\Services\DailySummaryService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;


/**
 * 日报控制器
 *
 * @author edison.an
 *
 */
class DailySummaryController extends Controller
{

    /**
     * DailySummaryService 实例
     *
     * @var DailySummaryService
     */
    protected $dailySummaryService;

    /**
     * 构造方法
     *
     * @param DailySummaryService $dailySummaryService
     * @return void
     */
    public function __construct(DailySummaryService $dailySummaryService)
    {
        $this->middleware('auth');

        $this->dailySummaryService = $dailySummaryService;
    }

    /**
     * 首页
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $dailySummarys = $this->dailySummaryService->getList();

        return view('dailysummarys.index', [
            'dailysummarys' => $dailySummarys
        ]);
    }

    /**
     * 获取特定日期的所做事情
     *
     * @param Request $request
     */
    public function getTipInfos(Request $request)
    {
        $this->validate($request, [
            'summary_date' => 'required'
        ]);

        $summaryDate = $request->get('summary_date');
        $infos = $this->dailySummaryService->getTipInfos($summaryDate);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'infos' => $infos
        )));
    }

    /**
     * 新建日报
     *
     * @param Request $request
     */
    public function create(Request $request)
    {
        // 如果存在参数，则按照参数处理，如果不存在，则默认为今天日报
        if ($request->has("summary_date")) {
            $summaryDate = $request->summary_date;
        } else {
            $summaryDate = date("Y-m-d");
        }

        $dailySummary = $this->dailySummaryService->getBySummaryDate($summaryDate);
        if (!empty ($dailySummary)) {
            return redirect('/dailysummary/' . $dailySummary->id)->with("message", "IT WORKS!");
        } else {
            return view('dailysummarys.create', array(
                'summary_date' => $summaryDate
            ));
        }
    }

    /**
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'summary_date' => 'required'
        ]);

        $params = array();
        $params ['summary_date'] = $request->summary_date;
        $params ['work_content'] = $request->work_content;
        $params ['life_content'] = $request->life_content;

        $dailySummary = new DailySummary ();
        $dailySummary->user_id = Auth::id();
        $dailySummary ['summary_date'] = $params ['summary_date'];
        $dailySummary ['work_content'] = $params ['work_content'];
        $dailySummary ['life_content'] = $params ['life_content'];
        $dailySummary->save();

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/dailysummarys');
    }

    /**
     *
     * @param Request $request
     * @param DailySummary $dailySummary
     */
    public function destroy(Request $request, DailySummary $dailySummary)
    {
        $this->authorize('destroy', $dailySummary);

        $params = array();
        $params ['status'] = 2;
        $flag = $dailySummary->update($params);

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/dailysummarys');
    }

    /**
     * 更新
     *
     * @param Request $request
     * @param DailySummary $dailySummary
     * @return
     *
     */
    public function update(Request $request, DailySummary $dailySummary)
    {
        $this->authorize('destroy', $dailySummary);

        if ($request->method() == 'GET') {
            return view('dailysummarys.update', array(
                'dailysummary' => $dailySummary
            ));
        }

        $params = array();
        $params ['work_content'] = $request->work_content;
        $params ['life_content'] = $request->life_content;

        $flag = $dailySummary->update($params);

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/dailysummarys');
    }
}
