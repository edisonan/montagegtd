<?php

namespace App\Http\Middleware;

use App\Models\Application;
use App\Services\PersonalAccessTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeAccessMiddleware
{
    protected $personalAccessTokenService;

    public function __construct(PersonalAccessTokenService $personalAccessTokenService)
    {
        $this->personalAccessTokenService = $personalAccessTokenService;
    }

    public function handle(Request $request, Closure $next)
    {
        $application = Application::with('allowedUsers')
            ->whereIn('slug', array(
                $request->route('appSlug'),
                '/' . ltrim((string)$request->route('appSlug'), '/'),
            ))
            ->where('status', '<', 4)
            ->first();

        if (!$application) {
            return $this->deny($request, 404, 'Application not found');
        }

        $codePath = ltrim(urldecode((string)$request->route('codePath')), '/');
        $code = $application->codes()
            ->whereIn('path', array($codePath, '/' . $codePath))
            ->where('status', 1)
            ->first();

        if (!$code) {
            return $this->deny($request, 404, 'Code not found');
        }

        $mode = $code->auth_mode ?: ($application->auth_mode ?: 'public');
        if ($mode === 'public') {
            return $next($request);
        }

        $sessionUser = $request->user() ?: Auth::user();
        $pat = $this->resolvePat($request);
        $user = $pat && $pat->user ? $pat->user : $sessionUser;

        if ($mode === 'pat' && (!$pat || !$pat->can('code:execute'))) {
            return $this->deny($request, 401, 'A PAT with code:execute scope is required');
        }

        if (!$user) {
            if (($mode === 'login' || $mode === 'whitelist') && !$request->expectsJson() && !$request->is('api/*')) {
                return redirect('/login');
            }
            return $this->deny($request, 401, 'Authentication is required');
        }

        if ($mode === 'whitelist' && !$application->allowedUsers->contains('id', $user->id)) {
            return $this->deny($request, 403, 'User is not allowed to access this application');
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        Auth::setUser($user);
        $request->attributes->set('code_application', $application);
        $request->attributes->set('code_user', $user);
        $request->attributes->set('code_pat', $pat);

        return $next($request);
    }

    protected function resolvePat(Request $request)
    {
        $header = $request->header('Authorization');
        if (!$header || !preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return null;
        }

        $token = $this->personalAccessTokenService->validateToken(trim($matches[1]));
        if (!$token || !$token->user) {
            return null;
        }

        try {
            $this->personalAccessTokenService->logTokenUsage(
                $token,
                $request->getRequestUri(),
                $request->ip(),
                $request->userAgent()
            );
        } catch (\Throwable $e) {
            // Token usage logging must not block Code execution.
        }

        return $token;
    }

    protected function deny(Request $request, $status, $message)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(array(
                'code' => $status,
                'msg' => $message,
                'result' => array(),
            ), $status);
        }

        return response($message, $status);
    }
}
