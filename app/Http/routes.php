<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {

    Route::get('/', function () {
        return view('welcome');
    })->middleware('guest');
    
    Route::get('/index', 'IndexController@index');
    Route::get('/index/test', 'IndexController@test');
    
    Route::get('/notes', 'NoteController@index');
    Route::get('/notes/add_content/{add_content}', 'NoteController@index');
    Route::post('/note', 'NoteController@store');
    Route::delete('/note/{note}', 'NoteController@destroy');

    Route::get('/tasks', 'TaskController@index');
    Route::post('/task', 'TaskController@store');
    Route::delete('/task/{task}', 'TaskController@destroy');
    
    Route::get('/categorys', 'CategoryController@index');
    Route::post('/category', 'CategoryController@store');
    Route::delete('/category/{category}', 'CategoryController@destroy');
    
    Route::get('/feeds', 'FeedController@index');
    Route::post('/feed', 'FeedController@store');
    Route::get('/feed/updateFeed', 'FeedController@updateFeed');
    Route::delete('/feed/{feed}', 'FeedController@destroy');
    
    Route::get('/articles', 'ArticleController@index');
    Route::post('/article', 'ArticleController@store');
    Route::get('/article/view/{article}', 'ArticleController@view');
    Route::delete('/article/{article}', 'ArticleController@destroy');
    
    Route::get('/goals', 'GoalController@index');
    Route::post('/goal', 'GoalController@store');
    Route::delete('/goal/{goal}', 'GoalController@destroy');
    
    Route::get('/pomos', 'PomoController@index');
    Route::get('/pomos/start', 'PomoController@start');
    Route::get('/pomos/discard', 'PomoController@discard');
	
    Route::get('/third/index', 'ThirdController@index');
    Route::get('/third/testFave', 'ThirdController@testFave');
    Route::get('/third/fanfouIndex', 'ThirdController@fanfouIndex');
    Route::get('/third/fanfouCallback', 'ThirdController@fanfouCallback');
    Route::get('/third/twitterIndex', 'ThirdController@twitterIndex');
    Route::get('/third/twitterCallback', 'ThirdController@twitterCallback');
    
    Route::post('/pomo', 'PomoController@store');
    Route::delete('/pomo/{pomo}', 'PomoController@destroy');

    Route::auth();

});
