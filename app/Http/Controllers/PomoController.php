<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorCodeUtil;
use App\Models\Pomo;
use App\Models\Thing;
use App\Services\PomoService;
use Illuminate\Http\Request;

/**
 * 番茄工作法控制器
 *
 * @author edison.an
 *        
 */
class PomoController extends Controller {
	
	/**
	 * PomoService 实例.
	 *
	 * @var PomoService
	 */
	protected $pomoService;
	
	/**
	 * 构造方法
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
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function welcome(Request $request) {
		return view ( 'pomos.welcome', [ ] );
	}
	
	/**
	 * 首页
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		if ($request->has ( 'type' )) {
			$pomos = $this->pomoService->forUserByTime ( $request->user (), date ( 'Ymd' ) );
		} else {
			$pomos = $this->pomoService->forUserByStatus ( $request->user (), 2, $needPage = true );
		}
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $pomos );
			return response ( $resp );
		} else {
			return view ( 'pomos.index', [ 
					'pomos' => $pomos 
			] );
		}
	}
	
	/**
	 * 新建番茄
	 *
	 * @param Request $request        	
	 */
	public function start(Request $request) {
		$request->session ()->forget ( 'rest_start_time' );
		
		$pomoInfo = $this->pomoService->startPomo ( $request->user () );
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $pomoInfo );
			return response ( $resp );
		} else {
			return redirect ( '/index' );
		}
	}
	
	/**
	 * 放弃本次番茄
	 *
	 * @param Request $request        	
	 */
	public function discard(Request $request, Pomo $pomo) {
		if ($pomo->exists == false) {
			$request->session ()->forget ( 'rest_start_time' );
		} else {
			// 判断是否有权限，并置失败
			$this->authorize ( 'destroy', $pomo );
			$pomo->update ( array (
					'status' => 3 
			) );
		}
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/index' );
		}
	}
	
	/**
	 * 存储番茄信息
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request, Pomo $pomo) {
		$setting = $request->user ()->setting;
		$pomo_time = isset ( $setting->pomo_time ) && ! empty ( $setting->pomo_time ) ? $setting->pomo_time * 60 : Pomo::DEFAULT_INTERVAL;
		
		if (time () > strtotime ( $pomo->created_at ) + $pomo_time) {
			$this->validate ( $request, [ 
					'name' => 'required|max:255' 
			] );
			
			$this->authorize ( 'destroy', $pomo );
			$pomo->update ( [ 
					'name' => $request->name,
					'status' => 2 
			] );
			
			$thing = new Thing ();
			$thing->user_id = $request->user ()->id;
			$thing->type = 3;
			$thing->name = $pomo->name;
			$thing->end_time = $pomo->created_at;
			$thing->start_time = date ( 'Y-m-d H:i:s' );
			$thing->save ();
			
			// auto resting
			$request->session ()->put ( 'rest_start_time', time () );
		}
		
		if ($request->ajax () || $request->wantsJson ()) {
			$currentPomoInfo = $this->pomoService->getCurrentPomoInfo ( $request->user () );
			$currentPomoInfo ['active_pomo'] = $pomo;
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $currentPomoInfo );
			return response ( $resp );
		} else {
			return redirect ( '/index' );
		}
	}
	
	/**
	 * 删除番茄
	 *
	 * @param Request $request        	
	 * @param Pomo $pomo        	
	 */
	public function destroy(Request $request, Pomo $pomo) {
		$this->authorize ( 'destroy', $pomo );
		
		$pomo->delete ();
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/index' )->with ( 'message', 'IT WORKS!' );
		}
	}
}
