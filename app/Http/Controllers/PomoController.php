<?php

namespace App\Http\Controllers;

use App\Http\Utils\PaginationHelper;
use Illuminate\Http\Request;
use App\Models\Pomo;
use App\Models\Thing;
use App\Repositories\PomoRepository;
use App\Services\PomoService;
use App\Http\Utils\ResponseDataUtil;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Cache;


/**
 * 番茄工作法控制器
 *
 * @author edison.an
 *        
 */
class PomoController extends Controller {
	
	/**
	 * The pomo servie instance.
	 *
	 * @var PomoService
	 */
	protected $pomoService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param PomoService $pomoService        	
	 * @return void
	 */
	public function __construct(PomoService $pomoService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome' 
				] 
		] );
		$this->pomoService = $pomoService;
	}
	
	/**
	 * 欢迎页
	 *
	 * @param Request $request        	
	 * @return
	 *
	 */
	public function welcome(Request $request) {
		return view ( 'pomos.welcome', [ ] );
	}
	
	/**
	 * 首页.
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
        $pageSize = PaginationHelper::getPageSize($request);
		$pomos = $this->pomoService->getPomoListWithPagination ($pageSize);
		
		return $this->jsonAndViewAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( [ 
				'pomos' => $pomos 
		] ), 'pomos.index' );
	}
	
	/**
	 * 今日番茄.
	 *
	 * @param Request $request        	
	 */
	public function todayPomos(Request $request) {
		$pomos = $this->pomoService->getTodayList ();
		
		return $this->jsonAndViewAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $pomos ), 'pomos.index' );
	}
	
	/**
	 * 开始做番茄.
	 *
	 * @param Request $request        	
	 */
	public function start(Request $request) {
		$pomoInfo = $this->pomoService->startPomo ( $request->user () );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $pomoInfo ), '/index' );
	}
	
	/**
	 * 放弃番茄/休息.
	 *
	 * @param Request $request        	
	 */
	public function discard(Request $request, Pomo $pomo) {
		if ($pomo->exists == false) {
//            $userId = $request->user()->id;
//            Cache::forget('user_'.$userId.'_rest_start_time');
//			$request->session ()->forget ( 'rest_start_time' );
		} else {
			// 判断是否有权限，并置失败
			$this->authorize ( 'destroy', $pomo );
			$updateParams = array ();
			if ($pomo->status == 1) {
				$updateParams ['status'] = 3;
			} else {
				$updateParams ['rest_status'] = 3;
			}
			$pomo->update ( $updateParams );
		}
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/index' );
	}
	
	/**
	 * 记录番茄
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request, Pomo $pomo) {
		$this->validate ( $request, [ 
				'name' => 'required|max:255' 
		] );
		
		$this->authorize ( 'destroy', $pomo );
		
		if (time () < strtotime ( $pomo->end_time )) {
			throw new CustomException ( "还未到番茄完成时间" );
		}
		if ($pomo->status == 1) {
			$currentPomoInfo = $this->pomoService->store ( $pomo, $request->name );
		} else {
			$currentPomoInfo = $this->pomoService->getRecentFormatPomo ();
		}
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $currentPomoInfo ), '/index' );
	}
	
	/**
	 * 删除.
	 *
	 * @param Request $request        	
	 * @param Pomo $pomo        	
	 */
	public function destroy(Request $request, Pomo $pomo) {
		$this->authorize ( 'destroy', $pomo );
		
		$pomo->delete ();
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/index' );
	}
	
	/**
	 * 查看当前番茄状态
	 * 
	 * @param Request $request        	
	 * @return unknown
	 */
	public function pomostatus(Request $request) {
		// 获取当前活动信息
		$currentPomoInfo = $this->pomoService->getRecentFormatPomo ();
		
		return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( $currentPomoInfo ) );
	}
	
	public function update(Request $request, Pomo $pomo) {
	    $this->validate ( $request, [
	        'name' => 'required|max:255'
	    ] );
	    
	    $pomo->update(array('name'=>$request->name));
	    
	    return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (  ), '/index' );
	}
}
