<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Validator;
//use Illuminate\Http\Request;
//use Tymon\JWTAuth\Facades\JWTAuth;
//use Illuminate\Support\Facades\Auth;
//use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /*
     * |--------------------------------------------------------------------------
     * | Registration & Login Controller
     * |--------------------------------------------------------------------------
     * |
     * | This controller handles the registration of new users, as well as the
     * | authentication of existing users. By default, this controller uses
     * | a simple trait to add these behaviors. Why don't you explore it?
     * |
     */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/index';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', [
            'except' => 'logout'
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data ['name'],
            'email' => $data ['email'],
            'password' => bcrypt($data ['password'])
        ]);
    }
//
//    // 用户登录并获取 JWT 令牌
//    public function jwtlogin(Request $request)
//    {
//        $credentials = $request->only('email', 'password');
//        try {
//            if (!$token = JWTAuth::attempt($credentials)) {
//                return response()->json(['error' => 'Invalid credentials'], 401);
//            }
//        } catch (JWTException $e) {
//            return response()->json(['error' => 'Could not create token'], 500);
//        }
//        return response()->json(['token' => $token], 200);
//    }
//
//    // 刷新 JWT 令牌
//    public function jwtrefreshToken()
//    {
//        try {
//            $token = JWTAuth::getToken();
//            if (!$token) {
//                return response()->json(['error' => 'Token not provided'], 401);
//            }
//            $newToken = JWTAuth::refresh($token);
//            return response()->json(['token' => $newToken], 200);
//        } catch (JWTException $e) {
//            return response()->json(['error' => 'Could not refresh token'], 500);
//        }
//    }
//
//    // 注销用户（使 JWT 令牌失效）
//    public function jwtlogout()
//    {
//        try {
//            JWTAuth::invalidate(JWTAuth::getToken());
//            return response()->json(['message' => 'Successfully logged out'], 200);
//        } catch (JWTException $e) {
//            return response()->json(['error' => 'Could not invalidate token'], 500);
//        }
//    }
//
//    // 获取用户信息
//    public function jwtUserProfile()
//    {
//        try {
//            $user = JWTAuth::parseToken()->authenticate();
//            if (!$user) {
//                return response()->json(['error' => 'User not found'], 404);
//            }
//            return response()->json(['user' => $user], 200);
//        } catch (JWTException $e) {
//            return response()->json(['error' => 'User not authenticated'], 401);
//        }
//    }
}
