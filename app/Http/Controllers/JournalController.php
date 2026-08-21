<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Services\JournalService;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;

/**
 * 记事控制器
 *
 * @author edison.an
 *        
 */
class JournalController extends Controller {
	
	/**
	 * JournalService 实例.
	 *
	 * @var JournalService
	 */
	protected $journalService;
	
	/**
	 * 构造方法
	 *
	 * @param JournalService $journalService        	
	 * @return void
	 */
	public function __construct(JournalService $journalService) {
		$this->middleware ( 'auth' );
		
		$this->journalService = $journalService;
	}
	
	/**
	 * 首页
	 *
	 * @param Request $request         
	 */
	public function index(Request $request) {
		return view('journals.index');
	}

	/**
	 * 获取当前用户的手账列表数据。
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function data(Request $request) {
		$this->validate($request, array(
			'page_size' => 'nullable|integer|min:1|max:100',
			'keyword' => 'nullable|string|max:100',
			'start_date' => 'nullable|date_format:Y-m-d',
			'end_date' => 'nullable|date_format:Y-m-d',
			'type' => 'nullable|integer|min:1|max:5',
		));

		$pageSize = (int)$request->input('page_size', 10);
		$filters = array_filter(array(
			'keyword' => trim((string)$request->input('keyword', '')),
			'start_date' => $request->input('start_date'),
			'end_date' => $request->input('end_date'),
			'type' => $request->input('type'),
		), function ($value) {
			return $value !== null && $value !== '';
		});

		$journals = $this->journalService->getList($pageSize, $filters);
		$summary = $this->journalService->getListSummary($filters);

		return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
			'journals' => $journals->items(),
			'pagination' => array(
				'current_page' => $journals->currentPage(),
				'per_page' => $journals->perPage(),
				'total' => $journals->total(),
				'last_page' => $journals->lastPage(),
				'next_page_url' => $journals->nextPageUrl(),
				'prev_page_url' => $journals->previousPageUrl(),
				'has_more_pages' => $journals->hasMorePages(),
			),
			'summary' => $summary,
		)));
	}
	
	/**
	 * 新建记事
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request) {
		$this->validate ( $request, [ 
				'name' => 'required|max:255',
				'start_time' => 'date_format:Y-m-d H:i:s',
				'end_time' => 'date_format:Y-m-d H:i:s' 
		] );
		
		$params = array ();
		$params ['name'] = $request->name;
		$params ['start_time'] = $request->input ( 'start_time', date ( 'Y-m-d H:i:s' ) );
		
		if ($request->has ( 'end_time' )) {
			$params ['end_time'] = $request->end_time;
			if (strtotime ( $params ['start_time'] ) > strtotime ( $params ['end_time'] )) {
				throw new CustomException ( "错误的结束时间" );
			}
		}
		
		$journal = $request->user ()->journals ()->create ( $params );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $journal ), '/journals' );
	}
	
	/**
	 * 删除记事
	 *
	 * @param Request $request        	
	 * @param Journal $journal        	
	 */
	public function destroy(Request $request, Journal $journal) {
		$this->authorize ( 'destroy', $journal );
		
		$journal->delete ();
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $journal ), '/journals' );
	}
	
	/**
	 * 更新记事
	 *
	 * @param Request $request        	
	 * @param Journal $journal        	
	 * @return
	 *
	 */
	public function update(Request $request, Journal $journal) {
		$this->authorize ( 'destroy', $journal );
		if ($request->method () == 'GET') {
			return view('journals.update');
		}
		
		$flag = $journal->update ( $request->all () );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $journal ), '/journals' );
	}

	/**
	 * 每日手账视图（按天展示 24 小时轨迹 + 当天手账内容）
	 *
	 * @param Request $request
	 */
	public function daily(Request $request) {
		return view('journals.daily');
	}

	/**
	 * 获取某一天的全部手账（用于每日手账的时间轴与内容展示）。
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function dailyData(Request $request) {
		$this->validate($request, array(
			'date' => 'required|date_format:Y-m-d',
		));

		$date = $request->input('date');

		$journals = $request->user()->journals()
			->where('start_time', '>=', $date . ' 00:00:00')
			->where('start_time', '<=', $date . ' 23:59:59')
			->orderBy('start_time', 'asc')
			->get();

		return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
			'date' => $date,
			'journals' => $journals,
		)));
	}
}
