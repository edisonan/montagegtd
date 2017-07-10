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
        $this->middleware('auth', ['except' => ['welcome']]);
        $this->pomos = $pomos;
    }
    
    public function welcome(Request $request)
    {
    	return view('pomos.welcome', []);
    }

    /**
     * Display a list of all of the user's pomo.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('pomos.index', [
            'pomos' => $this->pomos->forUserByStatus($request->user(),2,$need_page=true),
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
    	$request->session()->forget('rest_start_time');
    	
    	$request->user()->pomos()->create([
    		'name' => $request->has('name')?$request->name:'',
    		'status'=> 1,
    	]);
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/index')->with('message', 'IT WORKS!');
    	}
    }
    
    /**
     * Discard a new pomo.
     *
     * @param  Request  $request
     * @return Response
     */
    public function discard(Request $request, Pomo $pomo)
    {
    	if($pomo->exists == false){
    		$request->session()->forget('rest_start_time');
    	} else {
    		//判断是否有权限，并置失败
    		$this->authorize('destroy', $pomo);
    		$pomo->update(array('status'=>3));
    	}
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/index')->with('message', 'IT WORKS!');
    	}
    }

    /**
     * Create a new pomo.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request, Pomo $pomo)
    {
    	$setting = $request->user()->setting;
    	$pomo_time = isset($setting->pomo_time)&&!empty($setting->pomo_time)?$setting->pomo_time*60:Pomo::DEFAULT_INTERVAL;
    	
    	if(time() > strtotime($pomo->created_at) + $pomo_time){
	        $this->validate($request, [
	            'name' => 'required|max:255',
	        ]);
	        
	        $this->authorize('destroy', $pomo);
	        $pomo->update([
	            'name' => $request->name,
	        	'status'=> 2,
	        ]);
	        
	        //auto resting
	        $request->session()->set('rest_start_time', time());
    	}
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/index')->with('message', 'IT WORKS!');
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
        	return redirect('/index')->with('message', 'IT WORKS!');
        }
    }
}
