<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;

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
        $this->middleware('guest')->except('logout');
    }
    
    public function githubRedirect(){
    	return Socialite::driver('github')->redirect();
    }
    
    public function githubCallback(){
    	$user = Socialite::driver('github')->user();
    	dd($user->token);
    }
    
    public function weiboRedirect(){
    	return Socialite::driver('weibo')->redirect();
    }
    
    public function weiboCallback(){
    	$user = Socialite::driver('weibo')->user();
    	dd($user->token);
    }
}
