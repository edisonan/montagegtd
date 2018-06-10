<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Overtrue\Socialite\SocialiteManager;
use Illuminate\Http\Request;
use App\Repositories\OauthInfoRepository;
use App\User;
use App\OauthInfo;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\Debug\header;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout','thirdCallback','thirdRedirect');
    }
    
    public function thirdRedirect(Request $request,$driver){
    	$socialite = new SocialiteManager(config('services'));
    	return $socialite->driver($driver)->redirect();
    }
    
    public function thirdCallback(Request $request,$driver){
    	$socialite = new SocialiteManager(config('services'));
    	$third_user = $socialite->driver($driver)->user();
    	
    	//判断是否是登录态，如果是那么进行关联操作，如果不是那么进行登录操作
    	$curr_user = $request->user();
    	if(empty($curr_user)){
    		$action = 'login';
    	} else {
    		$action = 'related';
    	}

    	//获取oauth表中信息
    	$oauthInfoRepository = new OauthInfoRepository();
    	$oauth_info = $oauthInfoRepository->forByThirdUidAndDriver($third_user->id, $driver);
    	
    	//如果有，那么直接召唤出来user,登录
    	if(!empty($oauth_info)){
    		$user = User::where('id',$oauth_info->user_id)->first();
    		
    		if($action == 'related'){
    			if($curr_user->id != $oauth_info->user_id){
    				echo 'error,this has related the user_id '.$user->name;exit;
    			} else {
    				
    			}
	    		return redirect('/accounts');
    		} else {
    			Auth::login($user);
	    		return redirect('/index');
    		}
    		
    	} else {
    		//如果当前已经登录了，无需创建用户，否则需要单独创建用户
    		if($action == 'related'){
				$user = $curr_user;    			
    		} else {
		    	//如果没有，那么插入user表，插入
		    	$data = array();
	    		$data['name'] = $third_user->name;
	    		$data['email'] = 'taskcongcongus.'.empty($third_user->email)?time().rand(1,9999):$third_user->email;
	    		$data['password'] = bcrypt(str_random(16));
	    		$data['last_login'] = Carbon::now();
	    		
	    		//进行存储user
	    		$user = new User();
	    		$user->create($data);
    		}
			
    		//存储oauth信息
    		$token = $third_user->token->access_token;
    		$expire = isset($third_user->token->expires_in)?date('Y-m-d H:i:s',$third_user->token->expires_in):'2038-01-01 00:00:00';
    		
    		$oauth_info = new OauthInfo();
    		$oauth_info->create(array(
    			'third_uid'=>$third_user->id,
    			'user_id'=>$user->id,
    			'driver'=>$driver,
    			'access_token'=>$token,
    			'expire'=>$expire,
    			'created_at'=>date('Y-m-d H:i:s'),
    			'updated_at'=>date('Y-m-d H:i:s'),
    		));
    		
    		if($action == 'login'){
    			Auth::login($user);
	    		return redirect('/index');
    		} else {
    			return redirect('/accounts');
    		}
    		
    	}
    }
}
