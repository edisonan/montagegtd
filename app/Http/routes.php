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
    	$feedFactory = new ArandiLopez\Feed\Factories\FeedFactory(['cache.enabled' => false]);
    	$feeder = $feedFactory->make('https://www.douban.com/location/beijing/events/feed/weekly');
    	$simplePieInstance = $feeder->getRawFeederObject();
    
    	if (!empty($simplePieInstance)) {
    		$previousweek = date('Y-m-j H:i:s', strtotime('-7 days'));
    		foreach ($simplePieInstance->get_items() as $item) {
    			var_dump($item);
    		}
    	}
    })->middleware('guest');
    
    Route::get('/test3', function () {
    	$feeds = \App\Feed::get();
    	foreach ($feeds as $feed){
    		$feedSub = new \App\FeedSub();
    		$feedSub->user_id = $feed->user_id;
    		$feedSub->category_id = $feed->category_id;
    		$feedSub->feed_id = $feed->id;
    		$feedSub->feed_name = $feed->feed_name;
    		$feedSub->save();
    	}
    })->middleware('guest');
    
    Route::get('/test4', function () {
    	$articles = \App\Article::select('id','user_id','status','feed_id','published')->get();
    	foreach ($articles as $article){
    		$articleSub = new \App\ArticleSub();
    		$articleSub->user_id = $article->user_id;
    		$articleSub->status = $article->status;
    		$articleSub->article_id = $article->id;
    		$articleSub->feed_id = $article->feed_id;
    		$articleSub->published = $article->published;
    		$articleSub->save();
    	}
    })->middleware('guest');
    
    Route::get('/test2', function () {
    			$articles = \App\Article::where('user_id',$user->id)->where('status','unread')->where('published','<',$now)->where('published','>',$start_time)->orderBy('feed_id')->limit(100)->get();
    			$temp = array();
    			$content = array();
    			
    			$chapter_count = 0;
    			$article_count = 0;
    			
    			foreach($articles as $article)
    			{
    				if(!isset($temp[$article->feed_id])){
    					$content[] = array('title'=>$chapter_count.' '.$article->feed->feed_name);
    					
    					$temp[$article->feed_id] = $article->feed_id;
    					
    					$chapter_count++;
    					$article_count = 0;
    				}
    				$content[] = array('title'=>$chapter_count.' '.$article_count.$article->subject);
    				
    				$article_count++;
    			}
    			var_dump($content);
    })->middleware('guest');
    
    Route::get('/home', 'IndexController@index');
    Route::get('/index', 'IndexController@index');
    Route::get('/index/test', 'IndexController@test');
    Route::get('/index/feedback', 'IndexController@feedback');
    Route::post('/index/feedbackStore', 'IndexController@feedbackStore');
    
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
    Route::post('/categorys/sort', 'CategoryController@sort');
    
    Route::get('/feeds', 'FeedController@index');
    Route::get('/feeds/setting', 'FeedController@setting');
    Route::post('/feed', 'FeedController@store');
    Route::get('/feed/checkNewFeed', 'FeedController@checkNewFeed');
    Route::get('/feed/checkFeedUrl', 'FeedController@checkFeedUrl');
    Route::delete('/feed/{feedSub}', 'FeedController@destroy');
    Route::post('/feed/{feedSub}', 'FeedController@update');
    Route::get('/feed/{feedSub}', 'FeedController@update');
    Route::post('/feeds/sort', 'FeedController@sort');
    Route::get('/feeds/explorer', 'FeedController@explorer');
    Route::get('/feeds/quickstore', 'FeedController@quickstore');
    
    Route::get('/articles', 'ArticleController@index');
    Route::post('/article', 'ArticleController@store');
    Route::get('/article/list', 'ArticleController@list');
    Route::get('/article/view/{article}', 'ArticleController@view');
    Route::get('/articles/status/{articleSub}', 'ArticleController@status');
    Route::get('/articles/allstatus', 'ArticleController@status');
    Route::delete('/article/{article}', 'ArticleController@destroy');
    Route::get('/article/record/{articleSub}', 'ArticleController@getArticleRecord');
    
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
    
    Route::get('/things', 'ThingController@index');
    Route::post('/thing', 'ThingController@store');
    Route::delete('/thing/{thing}', 'ThingController@destroy');
    Route::post('/thing/{thing}', 'ThingController@update');
    Route::get('/thing/{thing}', 'ThingController@update');
    
    Route::get('/api/wechat/articles', 'ApiController@articles');
    Route::get('/api/wechat/articleview', 'ApiController@articleview');
    Route::get('/api/wechat/explorer', 'ApiController@explorer');
    Route::get('/api/wechat/notes', 'ApiController@notes');

    Route::auth();

});
