<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\PersonalAccessTokenService;
use Illuminate\Http\Request;

class PersonalAccessTokenController extends Controller
{
    protected $tokenService;

    public function __construct(PersonalAccessTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function index(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $tokens = $this->tokenService->getUserTokens($userId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'tokens' => $tokens,
        )));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required|string|max:255',
            'scopes' => 'array',
            'scopes.*' => 'string|in:read,write,delete,admin,code:execute',
            'expires_at' => 'nullable|date|after:now',
        ));

        $scopes = (array) $request->input('scopes', array());

        // 安全约束：admin scope 只能授予“后台管理员”本人。
        // 普通用户不能自行声明 admin，否则可自建 admin PAT 绕过权限模型
        // （例如管理应用工作台、管理 LLM provider 等）。
        if (in_array('admin', $scopes, true)) {
            $user = $this->getAuthUser($request);
            if (!$user || !$this->isSystemAdministrator($user)) {
                return response()->json(array(
                    'code' => 403,
                    'msg' => 'admin scope 仅限后台管理员创建；普通用户不能自建 admin PAT',
                    'result' => array(),
                ), 403);
            }
        }

        $data = array(
            'user_id' => (int)$this->getAuthUserId($request),
            'name' => $request->input('name'),
            'scopes' => $scopes,
            'expires_at' => $request->input('expires_at'),
        );

        $tokenData = $this->tokenService->createToken($data);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($tokenData));
    }

    /**
     * 判断当前用户是否为系统管理员（Laravel-admin 后台账号 或 应用管理者白名单）。
     */
    protected function isSystemAdministrator($user)
    {
        $email = strtolower(trim((string) $user->email));

        // 1) 后台管理员：admin_users.username 与用户 email 或昵称匹配
        $administrators = \Encore\Admin\Auth\Database\Administrator::query()
            ->get(array('username', 'name'));
        foreach ($administrators as $admin) {
            $adminUsername = strtolower(trim((string) $admin->username));
            $adminName = strtolower(trim((string) $admin->name));
            if ($adminUsername !== '' && ($adminUsername === $email || $adminUsername === strtolower(trim((string) $user->name)))) {
                return true;
            }
            if ($adminName !== '' && $adminName === $email) {
                return true;
            }
        }

        // 2) 应用管理者白名单（便于同一账号同时管理应用与 admin PAT）
        $allowedEmails = array_values(array_filter(array_map(function ($item) {
            return strtolower(trim((string) $item));
        }, config('app_manage.allowed_emails', array()))));
        if (in_array($email, $allowedEmails, true)) {
            return true;
        }
        $allowedUserIds = config('app_manage.allowed_user_ids', array());
        if ((int) $user->id > 0 && in_array((int) $user->id, $allowedUserIds, true)) {
            return true;
        }

        return false;
    }

    public function destroy(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $forceDelete = (bool)$request->input('force_delete', false);

        $success = $forceDelete
            ? $this->tokenService->deleteToken((int)$id, $userId)
            : $this->tokenService->revokeToken((int)$id, $userId);

        if (!$success) {
            return response()->json(array(
                'code' => 404,
                'msg' => 'Token not found',
                'result' => array(),
            ), 404);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'deleted' => $forceDelete,
            'revoked' => !$forceDelete,
        )));
    }
}
