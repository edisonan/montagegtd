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
            'scopes.*' => 'string|in:read,write,delete,admin',
            'expires_at' => 'nullable|date|after:now',
        ));

        $data = array(
            'user_id' => (int)$this->getAuthUserId($request),
            'name' => $request->input('name'),
            'scopes' => $request->input('scopes', array()),
            'expires_at' => $request->input('expires_at'),
        );

        $tokenData = $this->tokenService->createToken($data);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($tokenData));
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

