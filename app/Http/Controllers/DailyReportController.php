<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Services\DailyReportService;
use App\Http\Utils\ErrorCodeUtil;

/**
 * 日报控制器
 *
 * @author edison.an
 *        
 */
class DailyReportController extends Controller {
	
	/**
	 * DailyReportService 实例
	 *
	 * @var DailyReportService
	 */
	protected $dailyReportService;
	
	/**
	 * 构造方法
	 *
	 * @param DailyReportService $dailyReportService
	 * @return void
	 */
	public function __construct(DailyReportService $dailyReportService) {
		$this->middleware ( 'auth' );
		
		$this->dailyReportService = $dailyReportService;
	}
	
	/**
	 * 首页
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
	    $reports = $this->dailyReportService->forUserByStatus($request->user (), 1, true);

		return view ( 'reports.index', [
                'reports' => $reports
        ] );
	}

	public function create(Request $request) {
	    // 如果存在参数，则按照参数处理，如果不存在，则默认为今天日报
        if($request->has("report_date")) {
            $report_date = $request->report_date;
        } else {
            $report_date = date("Y-m-d");
        }

        return view ( 'reports.create', [
                'report_date' => $report_date,
        ] );

    }
	
	/**
	 * 
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request) {
		$this->validate ( $request, [ 
				'type' => 'required',
				'report_date' => 'required' 
		] );
		
		$params = array ();
		$params ['type'] = $request->type;
		$params ['report_date'] = $request->report_date;
		$params ['work_content'] = $request->work_content;
		$params ['life_content'] = $request->life_content;
		
		$request->user ()->reports ()->create ( $params );
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/reports' )->with ( 'message', 'IT WORKS!' );
		}
	}
	
	/**
	 * 
	 *
	 * @param Request $request        	
	 * @param DailyReport $dailyReport
	 */
	public function destroy(Request $request, DailyReport $dailyReport) {
		$this->authorize ( 'destroy', $dailyReport );
		
		$params = array ();
		$params ['status'] = 2;
		$flag = $dailyReport->update ( $params );
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/reports' )->with ( 'message', 'IT WORKS!' );
		}
	}
	
	/**
	 * 更新
	 *
	 * @param Request $request        	
	 * @param DailyReport $dailyReport
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function update(Request $request, DailyReport $dailyReport) {
		$this->authorize ( 'destroy', $dailyReport );
		
		if ($request->method () == 'GET') {
			return view ( 'reports.update', array (
					'report' => $dailyReport
			) );
		}
		
		$params = array ();
		$params ['work_content'] = $request->work_content;
		$params ['life_content'] = $request->life_content;
		
		$flag = $dailyReport->update ( $params );
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/reports' )->with ( 'message', 'IT WORKS!' );
		}
	}
}
