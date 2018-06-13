<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['web']], function () {

    Route::get('/', function () {
        return view('welcome');
    })->middleware('guest');
    
    Route::get('/test', function () {
    	\Log::info('here');
    	$feedFactory = new ArandiLopez\Feed\Factories\FeedFactory(['cache.enabled' => false]);
    	\Log::info('here1');
    	$feeder = $feedFactory->make('https://www.douban.com/location/beijing/events/feed/weekly');
    	\Log::info('here2');
    	$simplePieInstance = $feeder->getRawFeederObject();
    	\Log::info('here3');
    
    	if (!empty($simplePieInstance)) {
    	\Log::info('here4');
    		$previousweek = date('Y-m-j H:i:s', strtotime('-7 days'));
    		foreach ($simplePieInstance->get_items() as $item) {
    	\Log::info('here5');
    			var_dump($item);
    		}
    	} else {
    	\Log::info('here6');
    		echo 'empty';
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
    
    Route::get('/cals', 'CalController@index');
    Route::get('/calics/{theme}', 'CalController@ics');
    Route::get('/taskics/{cal_token}', 'CalController@taskics');
    
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
    Route::get('/feeds/search', 'FeedController@search');
    Route::get('/feeds/weixinrss', 'FeedController@weixinrss');
    Route::get('/feeds/weiborss', 'FeedController@weiborss');
    Route::get('/feeds/opml', 'FeedController@opml');
    Route::post('/feeds/importOpml', 'FeedController@importOpml');
    
    Route::get('/articles', 'ArticleController@index');
    Route::post('/article', 'ArticleController@store');
    Route::get('/article/list', 'ArticleController@list');
    Route::post('/article/mark', 'ArticleController@mark');
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
    
    Route::get('/accounts', 'AccountController@index');
    
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
    
    //Route::auth();
	Auth::routes();
	
	Route::get('login/third/{driver}', 'Auth\LoginController@thirdRedirect');
	Route::get('login/third/{driver}/callback', 'Auth\LoginController@thirdCallback');
	
	Route::get('/logout', 'Auth\LoginController@logout');
	

});

Route::group(['middleware' => ['web']], function () {
	Route::get('/api/wechat/login', 'WechatController@wechatlogin');
	Route::get('/api/wechat/articles', 'WechatController@articles');
	Route::get('/api/wechat/articleview', 'WechatController@articleview');
	Route::get('/api/wechat/explorer', 'WechatController@explorer');
	Route::get('/api/wechat/notes', 'WechatController@notes');
	Route::get('/api/wechat/addNote', 'WechatController@addNote');
	Route::get('/api/wechat/articleSubStatus', 'WechatController@articleSubStatus');
	Route::get('/api/wechat/articleSubStatus/{articleSub}', 'WechatController@articleSubStatus');
});
		
