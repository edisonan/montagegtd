<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\OauthInfoRepository;

class AccountController extends Controller
{
    /**
     * The oauthInfo repository instance.
     *
     * @var OauthInfoRepository
     */
    protected $oauths;
    

    /**
     * Create a new controller instance.
     *
     * @param  OauthInfoRepository  $tasks
     * @return void
     */
    public function __construct( OauthInfoRepository $oauths)
    {
        $this->middleware('auth', ['except' => ['']]);

        $this->oauths = $oauths;
    }
    /**
     * Display a list of all of the user's accounts.
     *
     * @param  Request  $request
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
