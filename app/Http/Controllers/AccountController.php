<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Setting;
use App\Repositories\SettingRepository;
use App\Repositories\OauthInfoRepository;

class AccountController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $oauths;
    

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct( OauthInfoRepository $oauths)
    {
        $this->middleware('auth', ['except' => ['']]);

        $this->oauths = $oauths;
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
    	
    	$oauthinfos = $this->oauths->forUser($request->user(),false);
    	
    	$oauths = array(
    		'github' => array(),
    		'weibo' => array(),
    	);
    	
    	foreach ($oauthinfos as $oauthinfo){
    		$oauths[$oauthinfo->driver] = array('expire'=>$oauthinfo->expire);
    	}
    	
        return view('accounts.index', [
            'oauths' => $oauths,
        ]);
    }
    
    
}
