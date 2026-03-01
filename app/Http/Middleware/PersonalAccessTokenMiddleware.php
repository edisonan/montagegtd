<?php

namespace App\Http\Middleware;

use App\Services\PersonalAccessTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PersonalAccessTokenMiddleware
{
    protected $tokenService;

    public function __construct(PersonalAccessTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * 处理传入的请求，验证Personal Access Token
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        // 从请求头中获取令牌
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json([
                'code' => 401,
                'msg' => 'Authorization header is required'
            ], 401);
        }

        // 检查是否是Bearer token格式
        if (!preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return response()->json([
                'code' => 401,
                'msg' => 'Invalid authorization header format. Expected: Bearer {token}'
            ], 401);
        }

        $tokenValue = trim($matches[1] ?? '');
        if ($tokenValue === '') {
            return response()->json([
                'code' => 401,
                'msg' => 'Bearer token is required'
            ], 401);
        }

        // 验证令牌
        $token = $this->tokenService->validateToken($tokenValue);

        if (!$token || !$token->user) {
            return response()->json([
                'code' => 401,
                'msg' => 'Invalid or expired token'
            ], 401);
        }

        // 支持 middleware 参数 `personal.token:read` / `personal.token:read,write`
        $requiredScopes = [];
        foreach ($scopes as $scopeGroup) {
            $parts = explode(',', (string)$scopeGroup);
            foreach ($parts as $scope) {
                $scope = trim($scope);
                if ($scope !== '') {
                    $requiredScopes[] = $scope;
                }
            }
        }
        $requiredScopes = array_values(array_unique($requiredScopes));

        foreach ($requiredScopes as $requiredScope) {
            if (!$this->tokenService->tokenCan($token, $requiredScope)) {
                return response()->json([
                    'code' => 403,
                    'msg' => "Insufficient permissions. Token lacks required scope: {$requiredScope}"
                ], 403);
            }
        }

        // 记录令牌使用日志
        try {
            $this->tokenService->logTokenUsage(
                $token,
                $request->getRequestUri(),
                $request->ip(),
                $request->userAgent()
            );
        } catch (Throwable $e) {
            // 日志失败不应影响主请求
        }

        // 将令牌信息附加到请求中，以便控制器可以访问
        $request->attributes->set('personal_access_token', $token);
        $request->attributes->set('auth_user', $token->user);
        $request->attributes->set('auth_via_personal_access_token', true);

        // 在当前请求上下文中设置认证用户，避免依赖session
        $request->setUserResolver(function () use ($token) {
            return $token->user;
        });
        Auth::setUser($token->user);

        return $next($request);
    }
}
