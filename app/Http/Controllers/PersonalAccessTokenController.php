<?php

namespace App\Http\Controllers;

use App\Services\PersonalAccessTokenService;
use Illuminate\Http\Request;
use App\Http\Utils\ResponseDataUtil;
use Illuminate\Support\Facades\Auth;

class PersonalAccessTokenController extends Controller
{
    protected $tokenService;

    public function __construct(PersonalAccessTokenService $tokenService)
    {
        $this->middleware('auth');
        $this->tokenService = $tokenService;
    }

    /**
     * 显示用户的所有令牌
     */
    public function index(Request $request)
    {
        return view('personal_access_tokens.index');
    }

    /**
     * 显示创建令牌的表单
     */
    public function create(Request $request)
    {
        return view('personal_access_tokens.create');
    }

    /**
     * 创建新的个人访问令牌
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'scopes' => 'array',
            'scopes.*' => 'string|in:read,write,delete,admin,code:execute',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $userId = Auth::id();
        $data = [
            'user_id' => $userId,
            'name' => $request->input('name'),
            'scopes' => $request->input('scopes', []),
            'expires_at' => $request->input('expires_at'),
        ];

        $tokenData = $this->tokenService->createToken($data);

        // 返回成功响应，包含令牌值（只在创建时返回）
        return response()->json([
            'code' => 9999,
            'msg' => 'Token created successfully',
            'result' => $tokenData
        ]);
    }

    /**
     * 删除指定的令牌
     */
    public function destroy(Request $request, $id)
    {
        $userId = Auth::id();
        $forceDelete = (bool)$request->input('force_delete', false);

        $success = $forceDelete
            ? $this->tokenService->deleteToken($id, $userId)
            : $this->tokenService->revokeToken($id, $userId);

        if ($success) {
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
                'msg' => $forceDelete ? 'Token deleted successfully' : 'Token revoked successfully'
            ]));
        }

        return response()->json([
            'code' => 404,
            'msg' => 'Token not found'
        ], 404);
    }

}
