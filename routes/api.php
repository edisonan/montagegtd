<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | API Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register API routes for your application. These
 * | routes are loaded by the RouteServiceProvider within a group which
 * | is assigned the "api" middleware group. Enjoy building your API!
 * |
 */

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // 健康检查
    Route::get('/health', function () {
        return response()->json([
            'code' => 9999,
            'msg' => 'ok',
            'result' => [
                'service' => 'task-gitee-api',
                'timestamp' => date('Y-m-d H:i:s'),
            ],
        ]);
    });

    // read scope: 查询类接口
    Route::group(['middleware' => ['personal.token:read']], function () {
        Route::get('/auth/verify', 'PersonalAccessTokenController@verify');
        Route::get('/auth/me', function (Request $request) {
            $user = $request->user();
            $token = $request->attributes->get('personal_access_token');

            return response()->json([
                'code' => 9999,
                'msg' => 'ok',
                'result' => [
                    'auth_via_personal_access_token' => (bool)$request->attributes->get('auth_via_personal_access_token'),
                    'user' => [
                        'id' => $user ? $user->id : null,
                        'name' => $user ? $user->name : null,
                        'email' => $user ? $user->email : null,
                    ],
                    'token' => [
                        'id' => $token ? $token->id : null,
                        'name' => $token ? $token->name : null,
                        'scopes' => $token ? $token->scopes : [],
                        'expires_at' => $token ? $token->expires_at : null,
                        'last_used_at' => $token ? $token->last_used_at : null,
                    ],
                ],
            ]);
        });

        // 只读业务接口
        Route::get('/llm/sessions', 'LlmSessionController@getSessions');
        Route::get('/llm/sessions/{id}', 'LlmSessionController@getSession');
        Route::get('/llm/agents', 'LlmAgentController@index');
        Route::get('/llm/agents/{id}', 'LlmAgentController@show');
        Route::get('/llm/models', 'LlmController@getModels');
    });

    // write scope: 写操作接口
    Route::group(['middleware' => ['personal.token:write']], function () {
        Route::post('/llm/chat', 'LlmController@chat');

        Route::post('/llm/sessions', 'LlmSessionController@createSession');
        Route::put('/llm/sessions/{id}/title', 'LlmSessionController@updateSessionTitle');
        Route::post('/llm/sessions/{id}/toggle-pin', 'LlmSessionController@togglePinSession');
        Route::delete('/llm/sessions/{id}', 'LlmSessionController@deleteSession');

        Route::post('/llm/agents', 'LlmAgentController@store');
        Route::put('/llm/agents/{id}', 'LlmAgentController@update');
        Route::delete('/llm/agents/{id}', 'LlmAgentController@destroy');
    });

    // admin scope: 高权限管理接口（按需开放）
    Route::group(['middleware' => ['personal.token:admin']], function () {
        Route::post('/llm/providers', 'LlmController@saveProvider');
        Route::put('/llm/providers/{id}', 'LlmController@saveProvider');
        Route::delete('/llm/providers/{id}', 'LlmController@deleteProvider');

        Route::post('/llm/models', 'LlmController@saveModel');
        Route::put('/llm/models/{id}', 'LlmController@saveModel');
        Route::delete('/llm/models/{id}', 'LlmController@deleteModel');

        Route::post('/llm/credentials', 'LlmController@saveCredential');
        Route::put('/llm/credentials/{id}', 'LlmController@saveCredential');
        Route::delete('/llm/credentials/{id}', 'LlmController@deleteCredential');
    });
});
