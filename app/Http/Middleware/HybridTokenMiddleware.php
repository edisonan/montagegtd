<?php

namespace App\Http\Middleware;

use App\Services\Auth\UserTokenService;
use App\Services\PersonalAccessTokenService;
use App\Support\AuthContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HybridTokenMiddleware
{
    protected $userTokenService;
    protected $personalAccessTokenService;

    public function __construct(
        UserTokenService $userTokenService,
        PersonalAccessTokenService $personalAccessTokenService
    ) {
        $this->userTokenService = $userTokenService;
        $this->personalAccessTokenService = $personalAccessTokenService;
    }

    public function handle(Request $request, Closure $next, ...$capabilities)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Invalid authorization header format. Expected: Bearer {token}',
                'result' => array(),
            ), 401);
        }

        $tokenValue = trim($matches[1]);
        if ($tokenValue === '') {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Bearer token is required',
                'result' => array(),
            ), 401);
        }

        $requiredCapabilities = $this->parseRequiredCapabilities($capabilities);
        $resolved = $this->resolveToken($request, $tokenValue);

        if (!$resolved || empty($resolved['user'])) {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Invalid or expired token',
                'result' => array(),
            ), 401);
        }

        $tokenCapabilities = isset($resolved['capabilities']) && is_array($resolved['capabilities'])
            ? $resolved['capabilities']
            : array();

        foreach ($requiredCapabilities as $capability) {
            if (!in_array('*', $tokenCapabilities, true) && !in_array($capability, $tokenCapabilities, true)) {
                return response()->json(array(
                    'code' => 403,
                    'msg' => 'Insufficient permissions. Missing capability: ' . $capability,
                    'result' => array(),
                ), 403);
            }
        }

        $user = $resolved['user'];
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        Auth::setUser($user);

        $request->attributes->set('auth_context', new AuthContext(
            $resolved['auth_type'],
            $user,
            $resolved['token_id'],
            $tokenCapabilities
        ));

        if ($resolved['auth_type'] === AuthContext::TYPE_PERSONAL_TOKEN) {
            $request->attributes->set('auth_via_personal_access_token', true);
            $request->attributes->set('personal_access_token', $resolved['token']);
        } else {
            $request->attributes->set('auth_via_user_access_token', true);
            $request->attributes->set('user_access_token', $resolved['token']);
        }

        return $next($request);
    }

    protected function parseRequiredCapabilities(array $capabilityGroups)
    {
        $required = array();
        foreach ($capabilityGroups as $group) {
            foreach (explode(',', (string)$group) as $capability) {
                $capability = trim($capability);
                if ($capability !== '') {
                    $required[] = $capability;
                }
            }
        }
        return array_values(array_unique($required));
    }

    protected function resolveToken(Request $request, $tokenValue)
    {
        if (strpos($tokenValue, UserTokenService::ACCESS_TOKEN_PREFIX) === 0) {
            $userToken = $this->userTokenService->validateAccessToken($tokenValue);
            if (!$userToken || !$userToken->user) {
                return null;
            }

            return array(
                'auth_type' => AuthContext::TYPE_USER_TOKEN,
                'token_id' => $userToken->id,
                'token' => $userToken,
                'user' => $userToken->user,
                'capabilities' => is_array($userToken->capabilities) ? $userToken->capabilities : array(),
            );
        }

        $personalToken = $this->personalAccessTokenService->validateToken($tokenValue);
        if (!$personalToken || !$personalToken->user) {
            return null;
        }

        try {
            $this->personalAccessTokenService->logTokenUsage(
                $personalToken,
                $request->getRequestUri(),
                $request->ip(),
                $request->userAgent()
            );
        } catch (\Throwable $e) {
            // ignore log failures
        }

        return array(
            'auth_type' => AuthContext::TYPE_PERSONAL_TOKEN,
            'token_id' => $personalToken->id,
            'token' => $personalToken,
            'user' => $personalToken->user,
            'capabilities' => is_array($personalToken->scopes) ? $personalToken->scopes : array(),
        );
    }
}
