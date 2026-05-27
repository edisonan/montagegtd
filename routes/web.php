<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
/*
 * |--------------------------------------------------------------------------
 * | Web Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register web routes for your application. These
 * | routes are loaded by the RouteServiceProvider within a group which
 * | contains the "web" middleware group. Now create somejournal great!
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
    Route::get('/about', 'HelpController@about');
    Route::post('/help/feedbackStore', 'HelpController@feedbackStore');

    Route::get('/notes', 'NoteController@index');
    Route::post('/notes/upload', 'NoteController@upload');
    Route::get('/notes/add_content/{add_content}', 'NoteController@index');
    Route::post('/note', 'NoteController@store');
    Route::delete('/note/{note}', 'NoteController@destroy');
    Route::get('/note/getRecord/{note}', 'NoteController@getRecord');
    Route::get('/notes/{note}/edit', 'NoteController@update');

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
    Route::get('/study', 'StudyController@index');
    Route::get('/study/checkins', 'StudyController@checkins');
    Route::get('/studyfocus/{task}', 'StudyController@focus');
    Route::get('/tasksall', 'TaskController@getAllList');
    Route::post('/task', 'TaskController@store');
    Route::delete('/task/{task}', 'TaskController@destroy');
    Route::post('/task/{task}', 'TaskController@update');
    Route::get('/tasks/{task}', 'TaskController@show');
    Route::get('/taskpriority', 'TaskController@priority');
    Route::get('/taskparenttasks', 'TaskController@getParentTasks');

    Route::get('/cals', 'CalendarController@index');
    Route::get('/calics/{theme}', 'CalendarController@ics');
    Route::get('/taskics/{cal_token}', 'CalendarController@taskics');

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

    Route::get('/focuss', 'FocusController@index');
    Route::get('/focusstoday', 'FocusController@todayFocuss');
    Route::get('/focuss/start', 'FocusController@start');
    Route::get('/focuss/discard/{focus}', 'FocusController@discard');
    Route::get('/focuss/discard/', 'FocusController@discard');
    Route::get('/focuss/focusstatus', 'FocusController@focusstatus');

    Route::get('/third/index', 'ThirdController@index');
    Route::get('/third/testFave', 'ThirdController@testFave');
    Route::get('/third/fanfouIndex', 'ThirdController@fanfouIndex');
    Route::get('/third/fanfouCallback', 'ThirdController@fanfouCallback');
    Route::get('/third/twitterIndex', 'ThirdController@twitterIndex');
    Route::get('/third/twitterCallback', 'ThirdController@twitterCallback');

    Route::post('/focusupdate/{focus}', 'FocusController@update');
    Route::post('/focus/{focus}', 'FocusController@store');
    Route::delete('/focus/{focus}', 'FocusController@destroy');

    Route::get('/statistics', 'StatisticsController@index');

    Route::get('/plans', 'PlanController@index');
    Route::post('/plan', 'PlanController@store');
    Route::delete('/plan/{plan}', 'PlanController@destroy');
    Route::post('/plan/{plan}', 'PlanController@update');
    Route::get('/plan/{plan}', 'PlanController@update');
    // backward compatibility for legacy endpoints
    Route::post('/goal', 'PlanController@store');
    Route::delete('/goal/{plan}', 'PlanController@destroy');
    Route::post('/goal/{plan}', 'PlanController@update');
    Route::get('/goal/{plan}', 'PlanController@update');

    Route::get('/dailysummarys', 'DailySummaryController@index');
    Route::get('/dailycreate', 'DailySummaryController@create');
    Route::get('/dailytips', 'DailySummaryController@getTipInfos');
    Route::post('/dailysummary', 'DailySummaryController@store');
    Route::delete('/dailysummary/{dailySummary}', 'DailySummaryController@destroy');
    Route::post('/dailysummary/{dailySummary}', 'DailySummaryController@update');
    Route::get('/dailysummary/{dailySummary}', 'DailySummaryController@update');

    // welcome
    Route::get('/focus/welcome', 'FocusController@welcome');
    Route::get('/note/welcome', 'NoteController@welcome');
    Route::get('/read/welcome', 'ArticleController@welcome');
    Route::get('/minds/welcome', 'MindController@welcome');

    Route::get('/accounts', 'AccountController@index');

    Route::get('/settings', 'SettingController@index');
    Route::post('/setting/{setting}', 'SettingController@update');
    Route::post('/setting', 'SettingController@update');

    Route::get('/points', 'PointController@index');
    Route::get('/point-mall', 'PointMallController@index');
    Route::get('/point-mall/tree', 'PointMallController@tree');
    Route::get('/point-mall/lottery', 'PointMallController@lottery');
    Route::get('/point-mall/bus', 'PointMallController@bus');
    Route::get('/point-mall/pet', 'PointMallController@pet');
    Route::get('/point-mall/pond', 'PointMallController@pond');
    Route::get('/achievements', 'AchievementController@index');
    Route::post('/achievement/claim', 'AchievementController@claim');


    Route::get('/kindles', 'KindleController@index');
    Route::get('/kindle/test', 'KindleController@test');

    Route::get('/journals', 'JournalController@index');
    Route::post('/journal', 'JournalController@store');
    Route::delete('/journal/{journal}', 'JournalController@destroy');
    Route::post('/journal/{journal}', 'JournalController@update');
    Route::get('/journal/{journal}', 'JournalController@update');

    Auth::routes();
    Route::post('/auth/token/bootstrap', 'Api\\V2\\AuthController@bootstrapSession')->middleware('auth');
    Route::post('/api/v2/auth/bootstrap-session', 'Api\\V2\\AuthController@bootstrapSession')->middleware('auth');
    Route::post('/api/v2/auth/session-from-token', 'Api\\V2\\AuthController@establishWebSession')->middleware('hybrid.token:read');

    Route::get('login/third/{driver}', 'Auth\LoginController@thirdRedirect');
    Route::get('login/third/{driver}/callback', 'Auth\LoginController@thirdCallback');

    Route::get('/logout', 'Auth\LoginController@logout');


    // Legacy wechat mini and old /api/focus routes are migrated to /api/v2/wechat/*
    // and /api/v2/focuss* token routes.

    Route::any('/code/{codeInfo}', 'CodeController@view');
    
    // 应用代码访问路由
    Route::get('/app/{appSlug}/{codePath}', 'ApplicationController@show')->where('codePath', '.*');
    
    // 智能体管理页面 - 必须在API路由之后定义，以避免冲突
    Route::get('/llm/agentmanagement', function () {
        return view('llm.agentmanagement');
    })->middleware('auth');
    
    // 智能体草稿编辑页面
    Route::get('/llm/agents/{id}/draft', 'LlmAgentController@showDraftEditor')->middleware('auth');

    // 用户端LLM管理页面
    Route::get('/llm/llmmanagement', function () {
        return view('llm.llmmanagement');
    })->middleware('auth');
    
    // 课程管理相关路由
    Route::resource('courses', 'CourseController')->except(['edit', 'update', 'destroy']);
    Route::post('/courses/{id}/join', 'CourseController@joinCourse');
    Route::get('/course-enrollments', 'CourseController@getUserCourses');
    
    // 课程项目管理相关路由
    Route::post('/courses/{courseId}/items', 'CourseItemController@store');
    Route::put('/courses/{courseId}/items/{id}', 'CourseItemController@update');
    Route::delete('/courses/{courseId}/items/{id}', 'CourseItemController@destroy');
    
    // 课程讨论相关路由
    Route::get('/courses/{courseId}/discussions', 'DiscussionController@index');
    Route::post('/courses/{courseId}/discussions', 'DiscussionController@store');
    Route::get('/courses/{courseId}/discussions/{id}', 'DiscussionController@show');
    Route::post('/courses/{courseId}/discussions/{id}/reply', 'DiscussionController@reply');

    Route::get('/course/management', 'CourseController@management');
    
    // 课程项目管理相关路由
    Route::get('/courses/{courseId}/items', 'CourseItemController@index');
    Route::get('/courses/{courseId}/items/{id}', 'CourseItemController@show');
    Route::get('/course-items/structure/{courseId}', 'CourseItemController@getStructure');
    Route::get('/course-items/{id}', 'CourseItemController@showForModal');
    Route::post('/course-items', 'CourseItemController@storeFromModal');
    Route::post('/course-items/{id}', 'CourseItemController@updateFromModal');
    Route::delete('/course-items/{id}', 'CourseItemController@destroy');
    
    // Personal Access Token 相关路由
    Route::prefix('personal-access-tokens')->group(function () {
        Route::get('/', 'PersonalAccessTokenController@index')->name('personal-access-tokens.index');
        Route::get('/create', 'PersonalAccessTokenController@create')->name('personal-access-tokens.create');
        Route::post('/', 'PersonalAccessTokenController@store')->name('personal-access-tokens.store');
        Route::delete('/{id}', 'PersonalAccessTokenController@destroy')->name('personal-access-tokens.destroy');
    });

    // AI助手页面路由
    Route::get('/llm/index', 'LlmSessionController@index')->name('llm.index')->middleware('auth');



});

		
