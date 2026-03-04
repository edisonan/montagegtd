<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\Request;

/**
 * 统计控制器
 *
 * @author edison.an
 *        
 */
class StatisticsController extends Controller {
	
	/**
	 * The statistics service instance.
	 *
	 * @var StatisticsService
	 */
	protected $statisticsService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param StatisticsService $statisticsService        	
	 * @return void
	 */
	public function __construct(StatisticsService $statisticsService) {
		$this->middleware ( 'auth' );
		
		$this->statisticsService = $statisticsService;
	}
	
	/**
	 * 统计首页
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		$range = $request->input('range', 'month');
		$startDate = $request->input('start_date');
		$endDate = $request->input('end_date');
		$datas = $this->statisticsService->getIndexInfo ($range, $startDate, $endDate);
		
		return view ( 'statistics.index', $datas );
	}
}
