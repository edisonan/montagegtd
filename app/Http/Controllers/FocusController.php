<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Focus;
use App\Models\Journal;
use App\Repositories\FocusRepository;
use App\Services\FocusService;
use App\Http\Utils\ResponseDataUtil;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Cache;


/**
 * 专注工作法控制器
 *
 * @author edison.an
 *        
 */
class FocusController extends Controller {
	
	/**
	 * The focus servie instance.
	 *
	 * @var FocusService
	 */
	protected $focusService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param FocusService $focusService        	
	 * @return void
	 */
	public function __construct(FocusService $focusService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome' 
				] 
		] );
		$this->focusService = $focusService;
	}
	
	/**
	 * 欢迎页
	 *
	 * @param Request $request        	
	 * @return
	 *
	 */
	public function welcome(Request $request) {
		return view('focus.welcome');
	}
	
	/**
	 * 首页.
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		return view('focus.index');
	}
	
	/**
	 * 今日专注.
	 *
	 * @param Request $request        	
	 */
	public function todayFocuss(Request $request) {
		return view('focus.index');
	}
	
	/**
	 * 开始做专注.
	 *
	 * @param Request $request        	
	 */
	public function start(Request $request) {
		$focusInfo = $this->focusService->startFocus ( $request->user () );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $focusInfo ), '/index' );
	}
	
	/**
	 * 放弃专注/休息.
	 *
	 * @param Request $request        	
	 */
	public function discard(Request $request, Focus $focus) {
		if ($focus->exists == false) {
//            $userId = $request->user()->id;
//            Cache::forget('user_'.$userId.'_rest_start_time');
//			$request->session ()->forget ( 'rest_start_time' );
		} else {
			// 判断是否有权限，并置失败
			$this->authorize ( 'destroy', $focus );
			$updateParams = array ();
			if ($focus->status == 1) {
				$updateParams ['status'] = 3;
			} else {
				$updateParams ['rest_status'] = 3;
			}
			$focus->update ( $updateParams );
		}
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/index' );
	}
	
	/**
	 * 记录专注
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request, Focus $focus) {
		$this->validate ( $request, [ 
				'name' => 'required|max:255' 
		] );
		
		$this->authorize ( 'destroy', $focus );
		
		if (time () < strtotime ( $focus->end_time )) {
			throw new CustomException ( "还未到专注完成时间" );
		}
		if ($focus->status == 1) {
			$currentFocusInfo = $this->focusService->store ( $focus, $request->name );
		} else {
			$currentFocusInfo = $this->focusService->getRecentFormatFocus ();
		}
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $currentFocusInfo ), '/index' );
	}
	
	/**
	 * 删除.
	 *
	 * @param Request $request        	
	 * @param Focus $focus        	
	 */
	public function destroy(Request $request, Focus $focus) {
		$this->authorize ( 'destroy', $focus );
		
		$focus->delete ();
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/index' );
	}
	
	/**
	 * 查看当前专注状态
	 * 
	 * @param Request $request        	
	 * @return unknown
	 */
	public function focusstatus(Request $request) {
		// 获取当前活动信息
		$currentFocusInfo = $this->focusService->getRecentFormatFocus ();
		
		return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( $currentFocusInfo ) );
	}
	
	public function update(Request $request, Focus $focus) {
	    $this->validate ( $request, [
	        'name' => 'required|max:255'
	    ] );
	    
	    $focus->update(array('name'=>$request->name));
	    
	    return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (  ), '/index' );
	}
}
