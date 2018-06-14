<?php

namespace App\Http\Controllers;

use App\Http\Utils\OAuth1\OAuth;
use App\Http\Utils\OAuth1\FFClient;

use Illuminate\Http\Request;

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
    
    public function index(Request $request){
    	echo '<a href="/third/fanfouIndex">Go Fanfou!</a><br/>';
    	echo '<a href="/third/twitterIndex">Go twitter!</a>';exit;
    }

    /**
     * fanfou request token
     *
     * @param  Request  $request
     * @return Response
     */
    public function fanfouIndex(Request $request)
    {
    	$config = config('services.fanfou');
    	$o = new OAuth( $config['client_id'] , $config['client_secret']  );
    	
    	$keys = $o -> getRequestToken();
    	$aurl = $o -> getAuthorizeURL( $keys['oauth_token'] ,false , $config['redirect']);
    	
    	$request->session()->put('temp', $keys);
    	return redirect((string)$aurl)->with('message', 'IT WORKS!');
    }

    /**
     *  fanfou callback
     *
     * @param  Request  $request
     * @return Response
     */
    public function fanfouCallback(Request $request)
    {
    	$config = config('services.fanfou');
    	$temp = $request->session()->get('temp');
    	
    	$o = new OAuth( config("services.$driver.client_id"), config("services.$driver.client_secret") , $temp['oauth_token'] , $temp['oauth_token_secret']  );
    	
    	//获取access_token
    	$last_key = $o -> getAccessToken(  $temp['oauth_token'] ) ;
    	
    	//创造一个新的请求
    	$ff_user = new FFClient( config("services.$driver.client_id"), config("services.$driver.client_secret") , $last_key['oauth_token'] , $last_key['oauth_token_secret']  );
    	$result = $ff_user -> verify_credentials();
    	$result_arr = json_decode($result, true);
    	
    	$third = $this->third->forUserThirdId($request->user(),$result_arr['id']);
    	if(empty($third)){
    		$request->user()->thirds()->create([
    			'third_id' => $result_arr['id'],
    			'third_name' => $result_arr['name'],
    			'token' => $last_key['oauth_token'],
    			'token_value' => $last_key['oauth_token'],
    			'token_secret' => $last_key['oauth_token_secret'],
    			'source' => 'fanfou',
    		]);
    	} else {
    		$third->update([
    			'third_name' => $result_arr['name'],
    			'token' => $last_key['oauth_token'],
    			'token_value' => $last_key['oauth_token'],
    			'token_secret' => $last_key['oauth_token_secret'],
    		]);
    	}
    	
    	echo '<a href="/">'.$result_arr['name'].'! process finish,return index!</a>';exit;
    	exit;
    }
    
    /**
     * testFave
     * @param Request $request
     */
    public function testFave(Request $request){
    	$config = config('services.fanfou');
    	$third = $this->third->forUserSource($request->user(),'fanfou');
    	
    	if(empty($third)){
    		return redirect('third/fanfouIndex')->with('message', 'IT WORKS!');
    	}
    	
    	$oauth_token = $third['token_value'];
    	$oauth_token_secret = $third['token_secret'];
    	
    	//发表博文
    	$ff_user = new FFClient( config("services.$driver.client_id"), config("services.$driver.client_secret") , $oauth_token , $oauth_token_secret );
    	$result = $ff_user -> update('test!!!robot dog!!!');
    	
    	echo $result;exit;
    }
    
    /**
     * twitter request token
     *
     * @param  Request  $request
     * @return Response
     */
    public function twitterIndex(Request $request)
    {
    	
    }
    
    /**
     *  twitter callback
     *
     * @param  Request  $request
     * @return Response
     */
    public function twitterCallback(Request $request)
    {
    	
    }
}
