<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\CategoryRepository;
use App\Repositories\ArticleRepository;
use App\Models\ArticleSub;
use App\Models\NoteTagMap;
use App\Models\Tag;
use App\Repositories\FeedSubRepository;
use App\Repositories\ArticleSubRepository;
use App\Models\User;
use App\Models\OauthInfo;
use App\Http\Utils\CommonUtil;
use function Qiniu\json_decode;
use App\Repositories\OauthInfoRepository;

class TestController extends Controller {
	/**
	 * The pomo repository instance.
	 *
	 * @var PomoRepository
	 */
	protected $pomos;
	
	/**
	 * The pomo servie instance.
	 *
	 * @var PomoService
	 */
	protected $pomoService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param CategoryRepository $categorys        	
	 * @param ArticleRepository $articles        	
	 * @param FeedSubRepository $feedSubs        	
	 * @param ArticleSubRepository $articleSubs        	
	 * @return void
	 */
	public function __construct(PomoService $pomoService, PomoRepository $pomos) {
		$this->pomoService = $pomoService;
		$this->pomos = $pomos;
	}
	public function index(Request $request) {
		$user = new User ();
		$user->id = 1;
		if ($request->has ( 'type' )) {
			$pomos = $this->pomos->forUserByTime ( $user, date ( 'Ymd' ) );
		} else {
			$pomos = $this->pomos->forUserByStatus ( $user, 2, $needPage = true );
		}
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE, $pomos );
			return response ( $resp );
		} else {
			return view ( 'pomos.index', [ 
					'pomos' => $pomos 
			] );
		}
	}
	public function info(Request $request) {
		$user = new User ();
		$user->id = 1;
		
		$currentPomoInfo = $this->pomoService->getCurrentPomoInfo ( $request->user () );
		
		$resp = $this->responseJson ( self::OK_CODE, $currentPomoInfo );
		return response ( $resp );
	}
	
	/**
	 * Start a new pomo.
	 *
	 * @param Request $request        	
	 */
	public function start(Request $request) {
		$user = new User ();
		$user->id = 1;
		$request->session ()->forget ( 'rest_start_time' );
		
		$pomoInfo = $this->pomoService->startPomo ( $user );
		
		$resp = $this->responseJson ( self::OK_CODE, $pomoInfo );
		return response ( $resp );
	}
	
	/**
	 * Discard a new pomo.
	 *
	 * @param Request $request        	
	 */
	public function discard(Request $request, Pomo $pomo) {
		$user = new User ();
		$user->id = 1;
		if ($pomo->exists == false) {
			$request->session ()->forget ( 'rest_start_time' );
		} else {
			// 判断是否有权限，并置失败
			$this->authorize ( 'destroy', $pomo );
			$pomo->update ( array (
					'status' => 3 
			) );
		}
		
		$resp = $this->responseJson ( self::OK_CODE );
		return response ( $resp );
	}
	
	/**
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request, Pomo $pomo) {
		$user = new User ();
		$user->id = 1;
		$setting = $user->setting;
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
			$thing->user_id = $user->id;
			$thing->type = 3;
			$thing->name = $pomo->name;
			$thing->end_time = $pomo->created_at;
			$thing->start_time = date ( 'Y-m-d H:i:s' );
			$thing->save ();
			
			// auto resting
			$request->session ()->put ( 'rest_start_time', time () );
		}
		
		$currentPomoInfo = $this->pomoService->getCurrentPomoInfo ( $user );
		$currentPomoInfo ['active_pomo'] = $pomo;
		$resp = $this->responseJson ( self::OK_CODE, $currentPomoInfo );
		return response ( $resp );
	}
	
	/**
	 * Destroy the given task.
	 *
	 * @param Request $request        	
	 * @param Pomo $pomo        	
	 */
	public function destroy(Request $request, Pomo $pomo) {
		$user = new User ();
		$user->id = 1;
		$this->authorize ( 'destroy', $pomo );
		
		$pomo->delete ();
		
		$resp = $this->responseJson ( self::OK_CODE );
		return response ( $resp );
	}
}
