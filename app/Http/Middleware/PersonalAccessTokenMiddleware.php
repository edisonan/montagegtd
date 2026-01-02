<?php

namespace App\Http\Middleware;

use App\Services\PersonalAccessTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
     * @param string|null $scope
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next, string $scope = null)
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

        $tokenValue = $matches[1];

        // 验证令牌
        $token = $this->tokenService->validateToken($tokenValue);

        if (!$token) {
            return response()->json([
                'code' => 401,
                'msg' => 'Invalid or expired token'
            ], 401);
        }

        // 检查令牌权限（如果指定了权限范围）
        if ($scope && !$this->tokenService->tokenCan($token, $scope)) {
            return response()->json([
                'code' => 403,
                'msg' => "Insufficient permissions. Token lacks required scope: {$scope}"
            ], 403);
        }

        // 记录令牌使用日志
        $this->tokenService->logTokenUsage(
            $token,
            $request->getRequestUri(),
            $request->ip(),
            $request->userAgent()
        );

        // 将令牌信息附加到请求中，以便控制器可以访问
        $request->attributes->set('personal_access_token', $token);
        $request->attributes->set('auth_user', $token->user);

        // 设置认证用户
        Auth::login($token->user);

        return $next($request);
    }
}