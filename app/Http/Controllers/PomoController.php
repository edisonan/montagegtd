<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pomo;
use App\Models\Thing;
use App\Repositories\PomoRepository;
use App\Services\PomoService;

class PomoController extends Controller {
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
	 * @param PomoRepository $pomos        	
	 * @return void
	 */
	public function __construct(PomoService $pomoService, PomoRepository $pomos) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome' 
				] 
		] );
		$this->pomoService = $pomoService;
		$this->pomos = $pomos;
	}
	public function welcome(Request $request) {
		return view ( 'pomos.welcome', [ ] );
	}
	
	/**
	 * Display a list of all of the user's pomo.
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		return view ( 'pomos.index', [ 
				'pomos' => $this->pomos->forUserByStatus ( $request->user (), 2, $needPage = true ) 
		] );
	}
	
	/**
	 * Start a new pomo.
	 *
	 * @param Request $request        	
	 */
	public function start(Request $request) {
		$request->session ()->forget ( 'rest_start_time' );
		
		$pomoInfo = $this->pomoService->startPomo ( $request->user () );
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE, 'ok', $pomoInfo );
			return response ( $resp );
		} else {
			return redirect ( '/index' );
		}
	}
	
	/**
	 * Discard a new pomo.
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
			$resp = $this->responseJson ( self::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/index' );
		}
	}
	
	/**
	 * Create a new pomo.
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
			$resp = $this->responseJson ( self::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/index' );
		}
	}
	
	/**
	 * Destroy the given task.
	 *
	 * @param Request $request        	
	 * @param Pomo $pomo        	
	 */
	public function destroy(Request $request, Pomo $pomo) {
		$this->authorize ( 'destroy', $pomo );
		
		$pomo->delete ();
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/index' )->with ( 'message', 'IT WORKS!' );
		}
	}
}
