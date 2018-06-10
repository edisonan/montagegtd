<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Overtrue\Socialite\SocialiteManager;

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
    
    public function thirdRedirect($driver){
    	$socialite = new SocialiteManager(config('services'));
    	return $socialite->driver($driver)->redirect();
    }
    
    public function thirdCallback($driver){
    	$socialite = new SocialiteManager(config('services'));
    	$user = $socialite->driver($driver)->user();
    	dd($user->token);
    }
    
}
