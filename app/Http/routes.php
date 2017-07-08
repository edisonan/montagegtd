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
    
    Route::get('/test', function () {
    	$feeds = \App\Feed::where('type',2)->get();
    	$spideUtil = new \App\Http\Utils\SpideUtil();
    	foreach ($feeds as $feed){
    		$spideUtil->processFeed($feed);
    	}
    	for($i=1;$i<=67;$i++){
    		$url = "http://www.mafengwo.cn/yj/10065/2-0-$i.html";
    		$feed->url = $url;
    		$spideUtil->processFeed($feed);
    		echo $url."\n";
    	}
    })->middleware('guest');
    
    Route::get('/home', 'IndexController@index');
    Route::get('/index', 'IndexController@index');
    Route::get('/index/test', 'IndexController@test');
    
    Route::get('/notes', 'NoteController@index');
    Route::post('/notes/upload', 'NoteController@upload');
    Route::get('/notes/add_content/{add_content}', 'NoteController@index');
    Route::post('/note', 'NoteController@store');
    Route::delete('/note/{note}', 'NoteController@destroy');
    Route::get('/note/getRecord/{note}', 'NoteController@getRecord');
    
    Route::get('/minds', 'MindController@index');
    Route::post('/mind', 'MindController@store');
    Route::delete('/mind/{mind}', 'MindController@destroy');
    Route::get('/mind/{mind}', 'MindController@view');
    Route::post('/mind/{mind}', 'MindController@update');

    Route::get('/tasks', 'TaskController@index');
    Route::post('/task', 'TaskController@store');
    Route::delete('/task/{task}', 'TaskController@destroy');
    Route::post('/task/{task}', 'TaskController@update');
    Route::get('/task/{task}', 'TaskController@update');
    
    Route::get('/categorys', 'CategoryController@index');
    Route::post('/category', 'CategoryController@store');
    Route::post('/category/{category}', 'CategoryController@update');
    Route::get('/category/{category}', 'CategoryController@update');
    Route::delete('/category/{category}', 'CategoryController@destroy');
    
    Route::get('/feeds', 'FeedController@index');
    Route::post('/feed', 'FeedController@store');
    Route::get('/feed/checkNewFeed', 'FeedController@checkNewFeed');
    Route::get('/feed/checkFeedUrl', 'FeedController@checkFeedUrl');
    Route::delete('/feed/{feed}', 'FeedController@destroy');
    Route::post('/feed/{feed}', 'FeedController@update');
    Route::get('/feed/{feed}', 'FeedController@update');
    
    Route::get('/articles', 'ArticleController@index');
    Route::post('/article', 'ArticleController@store');
    Route::get('/article/view/{article}', 'ArticleController@view');
    Route::get('/article/star/{article}', 'ArticleController@star');
    Route::get('/article/read/{article}', 'ArticleController@read');
    Route::delete('/article/{article}', 'ArticleController@destroy');
    
    Route::get('/pomos', 'PomoController@index');
    Route::get('/pomos/start', 'PomoController@start');
    Route::get('/pomos/discard/{pomo}', 'PomoController@discard');
    Route::get('/pomos/discard/', 'PomoController@discard');
	
    Route::get('/third/index', 'ThirdController@index');
    Route::get('/third/testFave', 'ThirdController@testFave');
    Route::get('/third/fanfouIndex', 'ThirdController@fanfouIndex');
    Route::get('/third/fanfouCallback', 'ThirdController@fanfouCallback');
    Route::get('/third/twitterIndex', 'ThirdController@twitterIndex');
    Route::get('/third/twitterCallback', 'ThirdController@twitterCallback');
    
    Route::post('/pomo/{pomo}', 'PomoController@store');
    Route::delete('/pomo/{pomo}', 'PomoController@destroy');
    
    Route::get('/statistics', 'StatisticsController@index');
    
    Route::get('/goals', 'GoalController@index');
    Route::post('/goal', 'GoalController@store');
    Route::delete('/goal/{goal}', 'GoalController@destroy');
    Route::post('/goal/{goal}', 'GoalController@update');
    Route::get('/goal/{goal}', 'GoalController@update');
    
    //welcome
    Route::get('/pomo/welcome', 'PomoController@welcome');
    Route::get('/note/welcome', 'NoteController@welcome');
    Route::get('/read/welcome', 'ArticleController@welcome');
    Route::get('/minds/welcome', 'MindController@welcome');
    
    Route::get('/settings', 'SettingController@index');
    Route::post('/setting/{setting}', 'SettingController@update');
    Route::post('/setting', 'SettingController@update');
    
    Route::get('/kindles', 'KindleController@index');
    Route::get('/kindle/test', 'KindleController@test');

    Route::auth();

});
