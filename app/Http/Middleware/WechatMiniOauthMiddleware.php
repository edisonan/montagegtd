<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use function GuzzleHttp\json_encode;
use Log;


class WechatMiniOauthMiddleware
{

    private $app;

    public function __construct(Application $app){
        $this->app = $app;
    }


    /**
     *  检测小程序端的登录状态
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        $wechat_mini_token = $request->header('token','');
        
		Log::info(print_r($wechat_mini_token,true));

        if(empty($wechat_mini_token)){
            return  json_encode(array('code'=>1000,'msg'=>'params error'));
        }

        $token_value = Cache::store('file')->get($wechat_mini_token);

        if(empty($token_value)){
            return  json_encode(array('code'=>1000,'msg'=>'token error'));
        }

        $v = explode('#', $token_value);

        $this->app->instance('app_session', $v);

        return $next($request);
    }
}
