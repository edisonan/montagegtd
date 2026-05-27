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
}
