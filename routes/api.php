<?php

use Illuminate\Http\Request;

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

// 使用Personal Access Token中间件的API路由
Route::group(['middleware' => ['personal.token']], function () {
    // 示例API端点
    Route::get('/personal-access-tokens/verify', 'PersonalAccessTokenController@verify');
    
    // 可以添加其他需要令牌验证的API端点
    // 例如：Route::get('/api/data', 'DataController@index');
});