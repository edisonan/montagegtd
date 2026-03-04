<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\Auth\UserTokenService;
use App\Services\PersonalAccessTokenService;
use App\Services\SettingService;
use App\Support\AuthContext;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $userTokenService;
    protected $personalAccessTokenService;
    protected $settingService;

    public function __construct(
        UserTokenService $userTokenService,
        PersonalAccessTokenService $personalAccessTokenService,
        SettingService $settingService
    )
    {
        $this->userTokenService = $userTokenService;
        $this->personalAccessTokenService = $personalAccessTokenService;
        $this->settingService = $settingService;
    }

    public function login(Request $request)
    {
        $this->validate($request, array(
            'email' => 'required|email',
            'password' => 'required|string',
            'client_type' => 'nullable|string|max:32',
            'device_id' => 'nullable|string|max:128',
        ));

        $pair = $this->userTokenService->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('client_type', 'web'),
            $request->input('device_id')
        );

        if (!$pair) {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Invalid credentials',
                'result' => array(),
            ), 401);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($pair));
    }

    public function refresh(Request $request)
    {
        $this->validate($request, array(
            'refresh_token' => 'required|string',
        ));

        $pair = $this->userTokenService->refresh($request->input('refresh_token'));

        if (!$pair) {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Invalid or expired refresh token',
                'result' => array(),
            ), 401);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($pair));
    }

    public function register(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'client_type' => 'nullable|string|max:32',
            'device_id' => 'nullable|string|max:128',
        ));

        $user = User::create(array(
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ));

        $this->settingService->createDefaultSetting($user->id);

        $pair = $this->userTokenService->issueTokenPair(
            $user,
            array('*'),
            $request->input('client_type', 'web'),
            $request->input('device_id')
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($pair));
    }

    public function sendPasswordResetLink(Request $request)
    {
        $this->validate($request, array(
            'email' => 'required|email',
        ));

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json(array(
                'code' => 1001,
                'msg' => trans($status),
                'result' => array(),
            ), 422);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'message' => trans($status),
        )));
    }

    public function resetPassword(Request $request)
    {
        $this->validate($request, array(
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ));

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(array(
                'code' => 1001,
                'msg' => trans($status),
                'result' => array(),
            ), 422);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'message' => trans($status),
        )));
    }

    public function logout(Request $request)
    {
        $context = $this->getAuthContext($request);

        if (!$context || !$context->userId) {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Unauthorized',
                'result' => array(),
            ), 401);
        }

        if ($context->authType === AuthContext::TYPE_USER_TOKEN) {
            $authHeader = $request->header('Authorization', '');
            if (preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = trim($matches[1]);
                $this->userTokenService->revokeByAccessToken($token);
            }
        } elseif ($context->authType === AuthContext::TYPE_PERSONAL_TOKEN && $context->tokenId) {
            $this->personalAccessTokenService->revokeToken((int)$context->tokenId, (int)$context->userId);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'message' => 'Logged out',
        )));
    }

    public function me(Request $request)
    {
        $context = $this->getAuthContext($request);

        if (!$context || !$context->userId) {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Unauthorized',
                'result' => array(),
            ), 401);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'auth_type' => $context->authType,
            'token_id' => $context->tokenId,
            'capabilities' => $context->capabilities,
            'user' => array(
                'id' => $context->user->id,
                'name' => $context->user->name,
                'email' => $context->user->email,
            ),
        )));
    }

    public function verify(Request $request)
    {
        return $this->me($request);
    }

    /**
     * 基于已登录的web session引导签发UAT/URT，供前端切换到v2 token接口。
     */
    public function bootstrapSession(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Unauthorized',
                'result' => array(),
            ), 401);
        }

        $pair = $this->userTokenService->issueTokenPair(
            $user,
            array('*'),
            'web_session',
            (string)$request->session()->getId()
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($pair));
    }

    /**
     * 基于已通过 token 认证的用户，建立 web session，兼容现有 SSR 页面 auth 中间件。
     */
    public function establishWebSession(Request $request)
    {
        $context = $this->getAuthContext($request);
        if (!$context || !$context->user) {
            return response()->json(array(
                'code' => 401,
                'msg' => 'Unauthorized',
                'result' => array(),
            ), 401);
        }

        Auth::login($context->user, true);
        $request->session()->regenerate();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'session' => 'established',
            'user' => array(
                'id' => $context->user->id,
                'name' => $context->user->name,
                'email' => $context->user->email,
            ),
        )));
    }
}
