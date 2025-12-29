<?php

/*
 * |--------------------------------------------------------------------------
 * | Web Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register web routes for your application. These
 * | routes are loaded by the RouteServiceProvider within a group which
 * | contains the "web" middleware group. Now create something great!
 * |
 */
Route::group([
    'middleware' => [
        'web'
    ]
], function () {

    Route::get('/', function () {
        return view('welcome');
    })->middleware('guest');

    Route::get('/home', 'IndexController@index');
    Route::get('/index', 'IndexController@index');
    Route::get('/index/test', 'IndexController@test');

    Route::get('/help/feedback', 'HelpController@feedback');
    Route::post('/help/feedbackStore', 'HelpController@feedbackStore');

    Route::get('/notes', 'NoteController@index');
    Route::post('/notes/upload', 'NoteController@upload');
    Route::get('/notes/add_content/{add_content}', 'NoteController@index');
    Route::post('/note', 'NoteController@store');
    Route::delete('/note/{note}', 'NoteController@destroy');
    Route::get('/note/getRecord/{note}', 'NoteController@getRecord');
    Route::get('/noteupdate/{note}', 'NoteController@update');
    Route::post('/noteupdate/{note}', 'NoteController@update');

    Route::get('/minds', 'MindController@index');
    Route::post('/mind', 'MindController@store');
    Route::delete('/mind/{mind}', 'MindController@destroy');
    Route::get('/mind/{mind}', 'MindController@view');
    Route::get('/mindajaxget/{mind}', 'MindController@ajaxget');
    Route::get('/mindoutlineview/{mind}', 'MindController@outlineView');
    Route::get('/mindoutlineviewv2/{mind}', 'MindController@outlineViewv2');
    Route::get('/mindajaxoutlineget/{mind}', 'MindController@ajaxoutlineget');
    Route::post('/mind/{mind}', 'MindController@update');
    Route::post('/mindaddtag/{mind}', 'MindController@addTag');

    Route::get('/tasks', 'TaskController@index');
    Route::get('/tasksall', 'TaskController@getAllList');
    Route::post('/task', 'TaskController@store');
    Route::delete('/task/{task}', 'TaskController@destroy');
    Route::post('/task/{task}', 'TaskController@update');
    Route::get('/tasks/{task}', 'TaskController@show');
    Route::get('/taskpriority', 'TaskController@priority');
    Route::get('/taskparenttasks', 'TaskController@getParentTasks');

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
    Route::get('/article/navinfo', 'ArticleController@navinfo');
    Route::get('/article/navcountinfo', 'ArticleController@navcountinfo');
    Route::get('/article/proxyview', 'ArticleController@proxyView');

    Route::get('/pomos', 'PomoController@index');
    Route::get('/pomostoday', 'PomoController@todayPomos');
    Route::get('/pomos/start', 'PomoController@start');
    Route::get('/pomos/discard/{pomo}', 'PomoController@discard');
    Route::get('/pomos/discard/', 'PomoController@discard');
    Route::get('/pomos/pomostatus', 'PomoController@pomostatus');

    Route::get('/third/index', 'ThirdController@index');
    Route::get('/third/testFave', 'ThirdController@testFave');
    Route::get('/third/fanfouIndex', 'ThirdController@fanfouIndex');
    Route::get('/third/fanfouCallback', 'ThirdController@fanfouCallback');
    Route::get('/third/twitterIndex', 'ThirdController@twitterIndex');
    Route::get('/third/twitterCallback', 'ThirdController@twitterCallback');

    Route::post('/pomoupdate/{pomo}', 'PomoController@update');
    Route::post('/pomo/{pomo}', 'PomoController@store');
    Route::delete('/pomo/{pomo}', 'PomoController@destroy');

    Route::get('/statistics', 'StatisticsController@index');

    Route::get('/goals', 'GoalController@index');
    Route::post('/goal', 'GoalController@store');
    Route::delete('/goal/{goal}', 'GoalController@destroy');
    Route::post('/goal/{goal}', 'GoalController@update');
    Route::get('/goal/{goal}', 'GoalController@update');

    Route::get('/dailysummarys', 'DailySummaryController@index');
    Route::get('/dailycreate', 'DailySummaryController@create');
    Route::get('/dailytips', 'DailySummaryController@getTipInfos');
    Route::post('/dailysummary', 'DailySummaryController@store');
    Route::delete('/dailysummary/{dailySummary}', 'DailySummaryController@destroy');
    Route::post('/dailysummary/{dailySummary}', 'DailySummaryController@update');
    Route::get('/dailysummary/{dailySummary}', 'DailySummaryController@update');

    // welcome
    Route::get('/pomo/welcome', 'PomoController@welcome');
    Route::get('/note/welcome', 'NoteController@welcome');
    Route::get('/read/welcome', 'ArticleController@welcome');
    Route::get('/minds/welcome', 'MindController@welcome');

    Route::get('/accounts', 'AccountController@index');

    Route::get('/settings', 'SettingController@index');
    Route::post('/setting/{setting}', 'SettingController@update');
    Route::post('/setting', 'SettingController@update');

    Route::get('/points', 'PointController@index');
    Route::get('/achievements', 'AchievementController@index');
    Route::post('/achievement/claim', 'AchievementController@claim');


    Route::get('/kindles', 'KindleController@index');
    Route::get('/kindle/test', 'KindleController@test');

    Route::get('/things', 'ThingController@index');
    Route::post('/thing', 'ThingController@store');
    Route::delete('/thing/{thing}', 'ThingController@destroy');
    Route::post('/thing/{thing}', 'ThingController@update');
    Route::get('/thing/{thing}', 'ThingController@update');

    Auth::routes();

//    Route::post('/register', 'AuthController@register');
//    Route::post('/login', 'AuthController@login');
//    Route::post('/logout', 'AuthController@logout');
//    Route::post('/refresh', 'AuthController@refresh');
//    Route::get('/user', 'AuthController@userProfile')->middleware('auth:api');

    Route::get('login/third/{driver}', 'Auth\LoginController@thirdRedirect');
    Route::get('login/third/{driver}/callback', 'Auth\LoginController@thirdCallback');

    Route::get('/logout', 'Auth\LoginController@logout');


    Route::get('/api/wechat/login', 'Wechat\WechatController@wechatlogin');
    Route::get('/api/wechat/articles', 'Wechat\WechatController@articles');
    Route::get('/api/wechat/articleview', 'Wechat\WechatController@articleview');
    Route::get('/api/wechat/explorer', 'Wechat\WechatController@explorer');
    Route::get('/api/wechat/notes', 'Wechat\WechatController@notes');
    Route::get('/api/wechat/addNote', 'Wechat\WechatController@addNote');
    Route::get('/api/wechat/articleSubStatus', 'Wechat\WechatController@articleSubStatus');
    Route::get('/api/wechat/articleSubStatus/{articleSub}', 'Wechat\WechatController@articleSubStatus');

    Route::get('/api/pomos', 'Wechat\TestController@index');
    Route::get('/api/pomo/info', 'Wechat\TestController@info');
    Route::get('/api/pomo/start', 'Wechat\TestController@start');
    Route::get('/api/pomo/discard/{pomo}', 'Wechat\TestController@discard');
    Route::get('/api/pomo/discard/', 'Wechat\TestController@discard');
    Route::post('/api/pomos/{pomo}', 'Wechat\TestController@store');
    Route::delete('/api/pomos/{pomo}', 'PomoController@destroy');

    Route::any('/code/{codeInfo}', 'CodeController@view');
    
    // LLM管理相关路由
    Route::prefix('llm')->group(function () {
        Route::get('/providers', 'LlmController@getProviders');
        Route::get('/providers/{id}', 'LlmController@getProvider');
        Route::post('/providers', 'LlmController@saveProvider');
        Route::put('/providers/{id}', 'LlmController@saveProvider');
        Route::delete('/providers/{id}', 'LlmController@deleteProvider');
        
        Route::get('/models', 'LlmController@getModels');
        Route::get('/models/{id}', 'LlmController@getModel');
        Route::post('/models', 'LlmController@saveModel');
        Route::put('/models/{id}', 'LlmController@saveModel');
        Route::delete('/models/{id}', 'LlmController@deleteModel');
        
        Route::get('/credentials', 'LlmController@getCredentials');
        Route::get('/credentials/{id}', 'LlmController@getCredential');
        Route::post('/credentials', 'LlmController@saveCredential');
        Route::put('/credentials/{id}', 'LlmController@saveCredential');
        Route::delete('/credentials/{id}', 'LlmController@deleteCredential');
        
        Route::get('/usage-stats', 'LlmController@getUsageStats');
    });
    
    // 用户端LLM管理页面
    Route::get('/llm-management', function () {
        return view('llm.index');
    });
    
    // 课程管理相关路由
    Route::resource('courses', 'CourseController')->except(['edit', 'update', 'destroy']);
    Route::post('/courses/{id}/join', 'CourseController@joinCourse');
    Route::get('/user-courses', 'CourseController@getUserCourses');
    
    // 课程项目管理相关路由
    Route::post('/courses/{courseId}/items', 'CourseItemController@store');
    Route::put('/courses/{courseId}/items/{id}', 'CourseItemController@update');
    Route::delete('/courses/{courseId}/items/{id}', 'CourseItemController@destroy');
    
    // 课程讨论相关路由
    Route::get('/courses/{courseId}/discussions', 'DiscussionController@index');
    Route::post('/courses/{courseId}/discussions', 'DiscussionController@store');
    Route::get('/courses/{courseId}/discussions/{id}', 'DiscussionController@show');
    Route::post('/courses/{courseId}/discussions/{id}/reply', 'DiscussionController@reply');
    
    // 课程管理首页
    Route::get('/course-management', 'CourseController@managementIndex');
    
    // 课程项目管理相关路由
    Route::get('/courses/{courseId}/items', 'CourseItemController@index');
    Route::get('/courses/{courseId}/items/{id}', 'CourseItemController@show');
    Route::get('/course-items/structure/{courseId}', 'CourseItemController@getStructure');
    Route::get('/course-items/{id}', 'CourseItemController@showForModal');
    Route::post('/course-items', 'CourseItemController@storeFromModal');
    Route::post('/course-items/{id}', 'CourseItemController@updateFromModal');
    Route::delete('/course-items/{id}', 'CourseItemController@destroy');
});
		