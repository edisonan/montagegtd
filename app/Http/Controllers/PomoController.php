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
    		if(time() < $pomo_start_time + 25 * 60){
    			$runing_pomo_status = 2;
    			$runing_pomo_remain = $pomo_start_time + 25 * 60- time();
    		} else {
    			$runing_pomo_status = 3;
    		}
    	}
    	
        return view('pomos.index', [
            'pomos' => $this->pomos->forUser($request->user()),
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
    	$pomo_start_time = $request->session()->set('pomo_start_time', time());
    	return redirect('/pomos');
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
    	
    	if(isset($pomo_start_time) && !empty($pomo_start_time) && time() > $pomo_start_time + 25 * 60){
	        $this->validate($request, [
	            'name' => 'required|max:255',
	        ]);
	
	        $request->user()->pomos()->create([
	            'name' => $request->name,
	        ]);
	        
	        //remove
	        $request->session()->forget('pomo_start_time');
    	}

        return redirect('/pomos');
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

        return redirect('/pomos');
    }
}
