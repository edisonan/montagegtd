<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Setting;
use App\Repositories\SettingRepository;

class SettingController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $settings;
    

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct( SettingRepository $settings)
    {
        $this->middleware('auth', ['except' => ['welcome']]);

        $this->settings = $settings;
    }
    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	$page_params = array();
    	
    	$setting = $this->settings->forUser($request->user());
    	
    	if(empty($setting)){
    		$setting = new Setting();
    	}
        return view('settings.index', [
            'setting' => $setting,
        ]);
    }
    
    
    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return Response
     */
    public function update(Request $request, Setting $setting)
    {
    	$this->validate($request, [
    		'day_pomo_goal' => 'integer|min:1',
    		'week_pomo_goal' => 'integer|min:1',
    		'month_pomo_goal' => 'integer|min:1',
    		'pomo_time' => 'integer|min:10|max:60',
    		'pomo_rest_time' => 'integer|min:1|max:10',
    		'is_start_kindle' => 'integer|min:0|max:1',
    		'with_image_push' => 'integer|min:0|max:1',
    		'kindle_email' => 'email',
    	]);
    	
    	if(empty($setting->user_id)){
    		$setting = $this->settings->forUser($request->user());
    		if(!empty($setting)){
    			echo 'error';exit;
    		}
    		
    		$setting = new Setting();
    		$setting->user_id = $request->user()->id;
    		$setting->save($request->all());
    	} else {
    		$this->authorize('destroy', $setting);
    		$setting->update($request->all());
    	}

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	if($request->has('page_info') && $request->page_info == 'kindle_page'){
        		return redirect('/kindles');
        	} else {
        		return redirect('/settings');
        	}
        	
        }
    }
}
