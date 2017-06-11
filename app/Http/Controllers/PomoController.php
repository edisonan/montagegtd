<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Pomo;
use App\Repositories\PomoRepository;

class PomoController extends Controller
{
    /**
     * The pomo repository instance.
     *
     * @var PomoRepository
     */
    protected $pomos;
    
    /**
     * Create a new controller instance.
     *
     * @param  PomoRepository  $pomos
     * @return void
     */
    public function __construct(PomoRepository $pomos)
    {
        $this->middleware('auth');
        $this->pomos = $pomos;
    }

    /**
     * Display a list of all of the user's pomo.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
//     	Session::set('variableName', $value);
    	$runing_pomo_status = 1;
    	$runing_pomo_remain = 0;
    	
    	$pomo_start_time = $request->session()->get('pomo_start_time');
    	if(isset($pomo_start_time) && !empty($pomo_start_time)){
    		if(time() < $pomo_start_time + Pomo::DEFAULT_INTERVAL){
    			$runing_pomo_status = 2;
    			$runing_pomo_remain = $pomo_start_time + Pomo::DEFAULT_INTERVAL - time();
    		} else {
    			$runing_pomo_status = 3;
    		}
    	}
    	
        return view('pomos.index', [
            'pomos' => $this->pomos->forUser($request->user(),$need_page=true),
        	'runing_pomo_status' => $runing_pomo_status,
        	'runing_pomo_remain' => $runing_pomo_remain,
        ]);
    }
    
    /**
     * Start a new pomo.
     *
     * @param  Request  $request
     * @return Response
     */
    public function start(Request $request)
    {
    	$request->session()->set('pomo_status', Pomo::STATUS_PROCESSING);
    	$pomo_start_time = $request->session()->set('pomo_start_time', time());
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/index');
    	}
    }
    
    /**
     * Discard a new pomo.
     *
     * @param  Request  $request
     * @return Response
     */
    public function discard(Request $request)
    {
    	$request->session()->set('pomo_status', Pomo::STATUS_INIT);
    	$pomo_start_time = $request->session()->forget('pomo_start_time');
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/index');
    	}
    }

    /**
     * Create a new pomo.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
    	$pomo_start_time = $request->session()->get('pomo_start_time');
    	if(isset($pomo_start_time) && !empty($pomo_start_time) && time() > $pomo_start_time + Pomo::DEFAULT_INTERVAL){
	        $this->validate($request, [
	            'name' => 'required|max:255',
	        ]);
	
	        $request->user()->pomos()->create([
	            'name' => $request->name,
	        ]);
	        
	        //auto resting
	        $request->session()->set('pomo_status', Pomo::STATUS_RESTING);
	        $request->session()->set('pomo_start_time', time());
    	}
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/index');
    	}
    }

    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Pomo  $pomo
     * @return Response
     */
    public function destroy(Request $request, Pomo $pomo)
    {
        $this->authorize('destroy', $pomo);

        $pomo->delete();

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/index');
        }
    }
}
