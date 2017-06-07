<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Third;
use App\Repositories\ThirdRepository;



class ThirdController extends Controller
{
    /**
     * The third repository instance.
     *
     * @var ThirdRepository
     */
    protected $third;

    /**
     * Create a new controller instance.
     *
     * @param  ThirdRepository  $third
     * @return void
     */
    public function __construct(ThirdRepository $third)
    {
        $this->middleware('auth');

        $this->third = $third;
    }

    /**
     * fanfou request token
     *
     * @param  Request  $request
     * @return Response
     */
    public function fanfouIndex(Request $request)
    {
    	$oauth = Jenssegers\OAuth\Facades\OAuth::consumer('facebook');
    	
    	// Response from Facebook
    	if ($code = Input::get('code'))
    	{
    		$token = $facebook->requestAccessToken($code);
    		$result = json_decode($facebook->request('/me'), true);
    		echo 'Your unique facebook user id is: ' . $result['id'] . ' and your name is ' . $result['name'];
    	}
    	// Redirect to login
    	else
    	{
    		return Redirect::away((string) $facebook->getAuthorizationUri());
    	}
    }

    /**
     *  fanfou callback
     *
     * @param  Request  $request
     * @return Response
     */
    public function fanfouCallback(Request $request)
    {
    	
    }
}
