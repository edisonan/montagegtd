<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;


/**
 *
 * @author edison.an
 *
 */
class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function jsonAndViewAutoResponse($request, $responseData, $viewPage)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($responseData, 200);
        } else {
            return view($viewPage, $responseData ['result']);
        }
    }

    public function jsonAndRedirectAutoResponse($request, $responseData, $redirectPage)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($responseData, 200);
        } else {
            return redirect($redirectPage)->with("message", "IT WORKS!");
        }
    }

    public function jsonResponse($request, $responseData)
    {
        return response()->json($responseData, 200);
    }

    /**
     * 统一获取当前认证用户（支持session和PAT）
     */
    protected function getAuthUser(Request $request = null)
    {
        if ($request && $request->user()) {
            return $request->user();
        }
        return Auth::user();
    }

    /**
     * 统一获取当前认证用户ID
     */
    protected function getAuthUserId(Request $request = null)
    {
        $user = $this->getAuthUser($request);
        return $user ? $user->id : null;
    }

    /**
     * 当前请求关联的PAT（非PAT请求返回null）
     */
    protected function getPersonalAccessToken(Request $request = null)
    {
        if (!$request) {
            return null;
        }
        return $request->attributes->get('personal_access_token');
    }

    /**
     * 当前请求是否通过PAT认证
     */
    protected function isPersonalAccessTokenAuth(Request $request = null)
    {
        if (!$request) {
            return false;
        }
        return (bool)$request->attributes->get('auth_via_personal_access_token', false);
    }
}
