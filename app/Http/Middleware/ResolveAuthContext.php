<?php

namespace App\Http\Middleware;

use App\Support\AuthContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResolveAuthContext
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->attributes->has('auth_context')) {
            return $next($request);
        }

        $context = AuthContext::guest();

        $user = $request->user();
        if ($user) {
            $context = new AuthContext(AuthContext::TYPE_SESSION, $user, null, array('*'));
        } elseif (Auth::check()) {
            $authUser = Auth::user();
            $context = new AuthContext(AuthContext::TYPE_SESSION, $authUser, null, array('*'));
        }

        $request->attributes->set('auth_context', $context);

        return $next($request);
    }
}
