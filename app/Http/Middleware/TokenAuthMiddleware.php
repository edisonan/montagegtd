<?php

namespace App\Http\Middleware;

use Cache;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Log;

class TokenAuthMiddleware
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 检测登录状态
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('token', '');

        Log::info(print_r($token, true));

        if (empty ($token)) {
            return response('Unauthorized.', 401);
        }

        $token_value = Cache::store('file')->get($token);

        if (empty ($token_value)) {
            return response('Unauthorized.', 401);
        }

        $v = explode('#', $token_value);

        $this->app->instance('app_session', $v);

        return $next ($request);
    }
}
