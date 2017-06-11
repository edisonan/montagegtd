<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Goal;
use App\Repositories\GoalRepository;

class GoalController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var GoalRepository
     */
    protected $goals;

    /**
     * Create a new controller instance.
     *
     * @param  GoalRepository  $goals
     * @return void
     */
    public function __construct(GoalRepository $goals)
    {
        $this->middleware('auth');

        $this->goals = $goals;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	$status = $request->status;
    	if(empty($status) || !in_array($status, array(1,2))){
    		$status = 1;
    	}
        return view('goals.index', [
            'goals' => $this->goals->forUserByStatus($request->user(), $status,$need_page=true),
        ]);
    }

    /**
     * Create a new task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
    	
        $this->validate($request, [
            'name' => 'required|max:255',
        ]);
        
        $params = array();
        $params['name'] = $request->name;
        
        $request->user()->goals()->create($params);

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/goals');
        }
    }

    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Goal  $goal
     * @return Response
     */
    public function destroy(Request $request, Goal $goal)
    {
        $this->authorize('destroy', $goal);
        
        $params = array();
        
        if($request->type == 'finish'){
        	$params['status'] = 2;
        } else {
        	$params['status'] = 3;
        }
        $flag = $goal->update($params);

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/goals');
        }
    }
    
    public function update(Request $request, Goal $goal)
    {
    	$this->authorize('destroy', $goal);
    	
    	if($request->method() == 'GET'){
    		return view('goals.update', array('goal'=>$goal));
    	}
    
    	$flag = $goal->update($request->all());
    
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/goals');
    	}
    }
}
