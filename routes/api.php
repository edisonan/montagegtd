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

Route::middleware('hybrid.token:read')->get('/user', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'code' => 9999,
        'msg' => 'ok',
        'result' => [
            'id' => $user ? $user->id : null,
            'name' => $user ? $user->name : null,
            'email' => $user ? $user->email : null,
        ],
    ]);
});

Route::prefix('v2')->group(function () {
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

    Route::post('/auth/login', 'Api\\V2\\AuthController@login');
    Route::post('/auth/register', 'Api\\V2\\AuthController@register');
    Route::post('/auth/refresh', 'Api\\V2\\AuthController@refresh');
    Route::post('/auth/password/email', 'Api\\V2\\AuthController@sendPasswordResetLink');
    Route::post('/auth/password/reset', 'Api\\V2\\AuthController@resetPassword');
    Route::post('/wechat/login', 'Api\\V2\\WechatController@login');
    Route::get('/calendar/taskics/{calToken}', 'Api\\V2\\CalendarController@taskics');

    // read capability: 查询类接口（兼容 PAT + UAT）
    Route::group(['middleware' => ['hybrid.token:read']], function () {
        Route::get('/minds', 'Api\\V2\\MindController@index');
        Route::get('/minds/{mind}', 'Api\\V2\\MindController@show');
        Route::get('/minds/{mind}/jsmind', 'Api\\V2\\MindController@jsmind');
        Route::get('/minds/{mind}/outline', 'Api\\V2\\MindController@outline');

        Route::get('/plans', 'Api\\V2\\PlanController@index');
        Route::get('/plans/{plan}', 'Api\\V2\\PlanController@show');

        Route::get('/settings', 'Api\\V2\\SettingController@index');
        Route::get('/kindles', 'Api\\V2\\KindleController@index');
        Route::get('/categories', 'Api\\V2\\CategoryController@index');
        Route::get('/categories/{category}', 'Api\\V2\\CategoryController@show');

        Route::get('/tasks', 'Api\\V2\\TaskController@index');
        Route::get('/study/overview', 'Api\\V2\\StudyController@overview');
        Route::get('/study/checkins', 'Api\\V2\\StudyController@checkins');
        Route::get('/study/plans', 'Api\\V2\\StudyController@plans');
        Route::get('/study/plans/{plan}', 'Api\\V2\\StudyController@showPlan');
        Route::get('/study/tasks/{task}/focus', 'Api\\V2\\StudyController@focusTask');
        Route::get('/index', 'Api\\V2\\IndexController@show');
        Route::get('/tasks/all', 'Api\\V2\\TaskController@getAllList');
        Route::get('/tasks/tab-counts', 'Api\\V2\\TaskController@tabCounts');
        Route::get('/tasks/priority', 'Api\\V2\\TaskController@priority');
        Route::get('/tasks/parent-tasks', 'Api\\V2\\TaskController@getParentTasks');
        Route::get('/tasks/{task}', 'Api\\V2\\TaskController@show');

        Route::get('/focuss', 'Api\\V2\\FocusController@index');
        Route::get('/focuss/today', 'Api\\V2\\FocusController@today');
        Route::get('/focuss/tab-counts', 'Api\\V2\\FocusController@tabCounts');
        Route::get('/focuss/status', 'Api\\V2\\FocusController@focusstatus');

        Route::get('/notes', 'Api\\V2\\NoteController@index');
        Route::get('/notes/{note}', 'Api\\V2\\NoteController@show');
        Route::get('/notes/{note}/record', 'Api\\V2\\NoteController@getRecord');
        Route::get('/journals', 'Api\\V2\\JournalController@index');
        Route::get('/journals/{journal}', 'Api\\V2\\JournalController@show');
        Route::get('/daily-summaries', 'Api\\V2\\DailySummaryController@index');
        Route::get('/daily-summaries/by-date', 'Api\\V2\\DailySummaryController@getBySummaryDate');
        Route::get('/daily-summaries/tips', 'Api\\V2\\DailySummaryController@getTipInfos');
        Route::get('/daily-summaries/{dailySummary}', 'Api\\V2\\DailySummaryController@show');
        Route::get('/achievements', 'Api\\V2\\AchievementController@index');
        Route::get('/notifications', 'Api\\V2\\NotificationController@index');
        Route::get('/points', 'Api\\V2\\PointController@index');
        Route::get('/point-mall/entrances', 'Api\\V2\\PointMallController@entrances');
        Route::get('/point-mall/goods', 'Api\\V2\\PointMallController@goods');
        Route::get('/point-mall/orders', 'Api\\V2\\PointMallController@orders');
        Route::get('/point-mall/tree/overview', 'Api\\V2\\PointMallGameplayController@treeOverview');
        Route::get('/point-mall/tree/leaderboard', 'Api\\V2\\PointMallGameplayController@treeLeaderboard');
        Route::get('/point-mall/pet/overview', 'Api\\V2\\PointMallGameplayController@petOverview');
        Route::get('/point-mall/pond/overview', 'Api\\V2\\PointMallGameplayController@pondOverview');
        Route::get('/point-mall/lottery/overview', 'Api\\V2\\PointMallGameplayController@lotteryOverview');
        Route::get('/point-mall/bus/overview', 'Api\\V2\\PointMallGameplayController@busOverview');
        Route::get('/statistics', 'Api\\V2\\StatisticsController@index');
        Route::get('/accounts', 'Api\\V2\\AccountController@index');
        Route::get('/personal-access-tokens', 'Api\\V2\\PersonalAccessTokenController@index');
        Route::get('/thirds', 'Api\\V2\\ThirdController@index');
        Route::get('/help/about', 'Api\\V2\\HelpController@about');
        Route::any('/codes/{codeInfo}', 'Api\\V2\\CodeController@view');
        Route::get('/calendar', 'Api\\V2\\CalendarController@index');
        Route::get('/calendar/ics/{theme}', 'Api\\V2\\CalendarController@ics');

        Route::get('/articles', 'Api\\V2\\ArticleController@index');
        Route::get('/articles/list', 'Api\\V2\\ArticleController@list');
        Route::get('/articles/proxyview', 'Api\\V2\\ArticleController@proxyView');
        Route::get('/auth/verify', 'Api\\V2\\AuthController@verify');
        Route::get('/auth/me', 'Api\\V2\\AuthController@me');
        Route::get('/feeds/check-feed-url', 'Api\\V2\\FeedController@checkFeedUrl');
        Route::get('/feeds', 'Api\\V2\\FeedController@index');
        Route::get('/feeds/explorer', 'Api\\V2\\FeedController@explorer');
        Route::get('/feeds/navinfo', 'Api\\V2\\FeedController@navinfo');
        Route::get('/feeds/search', 'Api\\V2\\FeedController@search');
        Route::get('/feeds/{feedSub}', 'Api\\V2\\FeedController@show');
        Route::get('/articles/navinfo', 'Api\\V2\\ArticleController@navinfo');
        Route::get('/articles/navcountinfo', 'Api\\V2\\ArticleController@navcountinfo');
        Route::get('/articles/{article}/reader-view', 'Api\\V2\\ArticleController@readerView');
        Route::get('/articles/{article}/ai-render', 'Api\\V2\\ArticleController@getAiRender');
        Route::get('/articles/{article}', 'Api\\V2\\ArticleController@show');
        Route::get('/articles/{articleSub}/record', 'Api\\V2\\ArticleController@getRecord');
        Route::get('/music/hot-playlist', 'Api\\V2\\ArticleController@hotPlaylist');

        Route::get('/artifacts', 'Api\\V2\\ArtifactController@index');
        Route::get('/artifacts/{artifact}', 'Api\\V2\\ArtifactController@show');

        Route::get('/courses', 'Api\\V2\\CourseController@index');
        Route::get('/courses/management', 'Api\\V2\\CourseController@management');
        Route::get('/course-enrollments', 'Api\\V2\\CourseController@enrollments');
        Route::get('/courses/{id}', 'Api\\V2\\CourseController@show');
        Route::get('/courses/{courseId}/items', 'Api\\V2\\CourseItemController@index');
        Route::get('/courses/{courseId}/items/{id}', 'Api\\V2\\CourseItemController@show');
        Route::get('/course-items/structure/{courseId}', 'Api\\V2\\CourseItemController@getStructure');
        Route::get('/course-items/{id}', 'Api\\V2\\CourseItemController@show');
        Route::get('/course-items/{id}/quiz', 'Api\\V2\\CourseQuizController@show');
        Route::get('/course-items/{id}/quiz/attempts', 'Api\\V2\\CourseQuizController@attempts');
        Route::get('/reviews/course-items', 'Api\\V2\\CourseReviewController@index');
        Route::get('/courses/{courseId}/discussions', 'Api\\V2\\DiscussionController@index');
        Route::get('/courses/{courseId}/discussions/{id}', 'Api\\V2\\DiscussionController@show');

        // 只读业务接口
        Route::get('/llm/sessions', 'Api\\V2\\LlmSessionController@getSessions');
        Route::get('/llm/sessions/{id}', 'Api\\V2\\LlmSessionController@getSession');
        Route::get('/llm/agents', 'Api\\V2\\LlmAgentController@index');
        Route::get('/llm/agents/{id}', 'Api\\V2\\LlmAgentController@show');
        Route::get('/llm/agents/{id}/draft', 'Api\\V2\\LlmAgentController@getDraft');
        Route::get('/llm/providers', 'Api\\V2\\LlmController@getProviders');
        Route::get('/llm/providers/{id}', 'Api\\V2\\LlmController@getProvider');
        Route::get('/llm/models', 'Api\\V2\\LlmController@getModels');
        Route::get('/llm/models/{id}', 'Api\\V2\\LlmController@getModel');
        Route::get('/llm/usage-stats', 'Api\\V2\\LlmController@getUsageStats');
        Route::get('/wechat/explorer', 'Api\\V2\\WechatController@explorer');
        Route::get('/wechat/articles', 'Api\\V2\\WechatController@articles');
        Route::get('/wechat/articleview', 'Api\\V2\\WechatController@articleView');
        Route::get('/wechat/notes', 'Api\\V2\\WechatController@notes');
        Route::get('/digest/profile', 'Api\\V2\\DigestController@profile');
        Route::get('/digest/pages', 'Api\\V2\\DigestController@pages');
        Route::get('/digest/pages/{id}', 'Api\\V2\\DigestController@showPage');
        Route::get('/briefings/configs', 'Api\\V2\\BriefingController@configs');
        Route::get('/briefings/pages', 'Api\\V2\\BriefingController@pages');
        Route::get('/briefings/pages/{id}', 'Api\\V2\\BriefingController@showPage');
    });

    // Code 的访问策略由应用/文件自身决定，默认公开；PAT 模式由 code.access 校验。
    Route::get('/applications/{appSlug}/{codePath}', 'Api\\V2\\ApplicationController@show')
        ->where('codePath', '.*')
        ->middleware('code.access');

    Route::post('/auth/logout', 'Api\\V2\\AuthController@logout')->middleware('hybrid.token');

    // write capability: 写操作接口（兼容 PAT + UAT）
    Route::group(['middleware' => ['hybrid.token:write']], function () {
        // 应用工作台管理（控制器内有管理者白名单二次校验；API 层禁止 PHP 文件）
        Route::get('/app-manage/apps', 'Api\\V2\\AppManageController@index');
        Route::post('/app-manage/apps', 'Api\\V2\\AppManageController@store');
        Route::get('/app-manage/apps/{id}', 'Api\\V2\\AppManageController@show')->where('id', '[0-9]+');
        Route::put('/app-manage/apps/{id}', 'Api\\V2\\AppManageController@update')->where('id', '[0-9]+');
        Route::delete('/app-manage/apps/{id}', 'Api\\V2\\AppManageController@destroy')->where('id', '[0-9]+');

        Route::post('/app-manage/apps/{id}/codes', 'Api\\V2\\AppManageController@storeCode')->where('id', '[0-9]+');
        Route::put('/app-manage/apps/{id}/codes/{codeId}', 'Api\\V2\\AppManageController@updateCode')->where('id', '[0-9]+')->where('codeId', '[0-9]+');
        Route::delete('/app-manage/apps/{id}/codes/{codeId}', 'Api\\V2\\AppManageController@destroyCode')->where('id', '[0-9]+')->where('codeId', '[0-9]+');
        Route::get('/app-manage/apps/{id}/codes/{codeId}/history', 'Api\\V2\\AppManageController@codeHistory')->where('id', '[0-9]+')->where('codeId', '[0-9]+');
        Route::post('/app-manage/apps/{id}/codes/{codeId}/rollback/{historyId}', 'Api\\V2\\AppManageController@rollbackCode')->where('id', '[0-9]+')->where('codeId', '[0-9]+')->where('historyId', '[0-9]+');

        Route::get('/app-manage/apps/{id}/virtual-tables', 'Api\\V2\\AppManageController@virtualTables')->where('id', '[0-9]+');
        Route::post('/app-manage/apps/{id}/virtual-tables', 'Api\\V2\\AppManageController@storeVirtualTable')->where('id', '[0-9]+');
        Route::post('/app-manage/apps/{id}/virtual-tables/{tableId}/fields', 'Api\\V2\\AppManageController@storeVirtualField')->where('id', '[0-9]+')->where('tableId', '[0-9]+');
        Route::get('/app-manage/apps/{id}/virtual-tables/{tableId}/records', 'Api\\V2\\AppManageController@virtualTableRecords')->where('id', '[0-9]+')->where('tableId', '[0-9]+');
        Route::post('/app-manage/apps/{id}/virtual-tables/{tableId}/records', 'Api\\V2\\AppManageController@storeVirtualRecord')->where('id', '[0-9]+')->where('tableId', '[0-9]+');
        Route::put('/app-manage/apps/{id}/virtual-tables/{tableId}/records/{recordId}', 'Api\\V2\\AppManageController@updateVirtualRecord')->where('id', '[0-9]+')->where('tableId', '[0-9]+')->where('recordId', '[0-9]+');
        Route::delete('/app-manage/apps/{id}/virtual-tables/{tableId}/records/{recordId}', 'Api\\V2\\AppManageController@deleteVirtualRecord')->where('id', '[0-9]+')->where('tableId', '[0-9]+')->where('recordId', '[0-9]+');

        Route::post('/minds', 'Api\\V2\\MindController@store');
        Route::post('/minds/{mind}', 'Api\\V2\\MindController@update');
        Route::put('/minds/{mind}', 'Api\\V2\\MindController@update');
        Route::delete('/minds/{mind}', 'Api\\V2\\MindController@destroy');
        Route::post('/minds/{mind}/tags', 'Api\\V2\\MindController@addTag');

        Route::post('/plans', 'Api\\V2\\PlanController@store');
        Route::post('/plans/{plan}', 'Api\\V2\\PlanController@update');
        Route::put('/plans/{plan}', 'Api\\V2\\PlanController@update');
        Route::delete('/plans/{plan}', 'Api\\V2\\PlanController@destroy');

        Route::post('/settings/current', 'Api\\V2\\SettingController@updateCurrent');
        Route::post('/settings/{setting}', 'Api\\V2\\SettingController@update')->where('setting', '[0-9]+');
        Route::put('/settings/{setting}', 'Api\\V2\\SettingController@update')->where('setting', '[0-9]+');
        Route::post('/settings/test-kindle', 'Api\\V2\\SettingController@testKindle');
        Route::post('/settings/test-ifttt', 'Api\\V2\\SettingController@testIfttt');
        Route::post('/settings/test-bark', 'Api\\V2\\SettingController@testBark');
        Route::get('/settings/export', 'Api\\V2\\SettingController@export');
        Route::post('/kindles/test', 'Api\\V2\\KindleController@test');

        Route::post('/tasks', 'Api\\V2\\TaskController@store');
        Route::put('/tasks/{task}', 'Api\\V2\\TaskController@update');
        Route::delete('/tasks/{task}', 'Api\\V2\\TaskController@destroy');
        Route::post('/study/plans', 'Api\\V2\\StudyController@createPlan');
        Route::post('/study/plans/{plan}/status', 'Api\\V2\\StudyController@updatePlanStatus');
        Route::post('/study/generate', 'Api\\V2\\StudyController@generate');
        Route::post('/study/plans/{plan}/generate', 'Api\\V2\\StudyController@generateByPlan');
        Route::delete('/study/plans/{plan}', 'Api\\V2\\StudyController@destroyPlan');
        Route::post('/study/tasks/{task}/checkin', 'Api\\V2\\StudyController@checkin');

        Route::post('/focuss/start', 'Api\\V2\\FocusController@start');
        Route::post('/focuss/discard', 'Api\\V2\\FocusController@discardCurrent');
        Route::post('/focuss/discard/{focus}', 'Api\\V2\\FocusController@discard');
        Route::post('/focuss/{focus}', 'Api\\V2\\FocusController@store');
        Route::put('/focuss/{focus}', 'Api\\V2\\FocusController@update');
        Route::delete('/focuss/{focus}', 'Api\\V2\\FocusController@destroy');

        Route::post('/notes', 'Api\\V2\\NoteController@store');
        Route::post('/notes/upload', 'Api\\V2\\NoteController@upload');
        Route::put('/notes/{note}', 'Api\\V2\\NoteController@update');
        Route::delete('/notes/{note}', 'Api\\V2\\NoteController@destroy');
        Route::post('/notes/{note}/like', 'Api\\V2\\NoteController@like');
        Route::post('/journals', 'Api\\V2\\JournalController@store');
        Route::put('/journals/{journal}', 'Api\\V2\\JournalController@update');
        Route::delete('/journals/{journal}', 'Api\\V2\\JournalController@destroy');
        Route::post('/daily-summaries', 'Api\\V2\\DailySummaryController@store');
        Route::put('/daily-summaries/{dailySummary}', 'Api\\V2\\DailySummaryController@update');
        Route::delete('/daily-summaries/{dailySummary}', 'Api\\V2\\DailySummaryController@destroy');
        Route::post('/achievements/claim', 'Api\\V2\\AchievementController@claim');
        Route::post('/point-mall/purchase', 'Api\\V2\\PointMallController@purchase');
        Route::post('/point-mall/tree/plant', 'Api\\V2\\PointMallGameplayController@treePlant');
        Route::post('/point-mall/tree/{treeId}/water', 'Api\\V2\\PointMallGameplayController@treeWater');
        Route::post('/point-mall/tree/{treeId}/decorate', 'Api\\V2\\PointMallGameplayController@treeDecorate');
        Route::post('/point-mall/pet/adopt', 'Api\\V2\\PointMallGameplayController@petAdopt');
        Route::post('/point-mall/pet/{petId}/feed', 'Api\\V2\\PointMallGameplayController@petFeed');
        Route::post('/point-mall/pond/release', 'Api\\V2\\PointMallGameplayController@pondRelease');
        Route::post('/point-mall/pond/{fishId}/feed', 'Api\\V2\\PointMallGameplayController@pondFeed');
        Route::post('/point-mall/lottery/draw', 'Api\\V2\\PointMallGameplayController@lotteryDraw');
        Route::post('/point-mall/bus/buy-line', 'Api\\V2\\PointMallGameplayController@busBuyLine');
        Route::post('/point-mall/bus/start-run', 'Api\\V2\\PointMallGameplayController@busStartRun');
        Route::post('/point-mall/bus/run/{runId}/tick', 'Api\\V2\\PointMallGameplayController@busTickRun');
        Route::post('/notifications/read-all', 'Api\\V2\\NotificationController@markAllRead');
        Route::post('/notifications/{id}/read', 'Api\\V2\\NotificationController@markRead');
        Route::post('/personal-access-tokens', 'Api\\V2\\PersonalAccessTokenController@store');
        Route::delete('/personal-access-tokens/{id}', 'Api\\V2\\PersonalAccessTokenController@destroy');
        Route::post('/help/feedback', 'Api\\V2\\HelpController@feedbackStore');
        Route::post('/thirds/fanfou/request', 'Api\\V2\\ThirdController@fanfouRequest');
        Route::post('/thirds/fanfou/test', 'Api\\V2\\ThirdController@testFave');

        Route::post('/feeds/quickstore', 'Api\\V2\\FeedController@quickstore');
        Route::post('/feeds/refresh', 'Api\\V2\\FeedController@refreshAll');
        Route::post('/feeds', 'Api\\V2\\FeedController@store');
        Route::post('/feeds/import-opml', 'Api\\V2\\FeedController@importOpml');
        Route::post('/feeds/sort', 'Api\\V2\\FeedController@sort');
        Route::post('/feeds/{feedSub}', 'Api\\V2\\FeedController@update');
        Route::put('/feeds/{feedSub}', 'Api\\V2\\FeedController@update');
        Route::delete('/feeds/{feedSub}', 'Api\\V2\\FeedController@destroy');
        Route::post('/feeds/{feedSub}/refresh', 'Api\\V2\\FeedController@refresh');
        Route::post('/feeds/{feedSub}/toggle-status', 'Api\\V2\\FeedController@toggleStatus');
        Route::post('/feeds/{feedSub}/clear-articles', 'Api\\V2\\FeedController@clearArticles');
        Route::post('/categories/sort', 'Api\\V2\\CategoryController@sort');
        Route::post('/categories', 'Api\\V2\\CategoryController@store');
        Route::put('/categories/{category}', 'Api\\V2\\CategoryController@update');
        Route::delete('/categories/{category}', 'Api\\V2\\CategoryController@destroy');
        Route::post('/articles/status/{articleSub}', 'Api\\V2\\ArticleController@status');
        Route::post('/articles/allstatus', 'Api\\V2\\ArticleController@allstatus');
        Route::post('/articles/mark', 'Api\\V2\\ArticleController@mark');
        Route::post('/articles/{article}/ai-render/generate', 'Api\\V2\\ArticleController@generateAiRender');
        Route::post('/digest/profile', 'Api\\V2\\DigestController@saveProfile');
        Route::post('/digest/pages/generate', 'Api\\V2\\DigestController@generate');
        Route::post('/briefings/configs', 'Api\\V2\\BriefingController@saveConfig');
        Route::delete('/briefings/configs/{id}', 'Api\\V2\\BriefingController@destroyConfig');
        Route::post('/briefings/configs/{configId}/generate', 'Api\\V2\\BriefingController@generate');
        Route::delete('/briefings/pages/{id}', 'Api\\V2\\BriefingController@destroyPage');
        Route::delete('/articles/{articleSub}', 'Api\\V2\\ArticleController@destroy');

        Route::post('/artifacts/generate', 'Api\\V2\\ArtifactController@generate');
        Route::post('/artifacts/{artifact}/to-mind', 'Api\\V2\\ArtifactController@toMind');
        Route::delete('/artifacts/{artifact}', 'Api\\V2\\ArtifactController@destroy');

        Route::post('/courses', 'Api\\V2\\CourseController@store');
        Route::post('/courses/{id}', 'Api\\V2\\CourseController@update');
        Route::put('/courses/{id}', 'Api\\V2\\CourseController@update');
        Route::delete('/courses/{id}', 'Api\\V2\\CourseController@destroy');
        Route::post('/courses/{id}/join', 'Api\\V2\\CourseController@join');
        Route::post('/courses/{id}/publish', 'Api\\V2\\CourseController@publish');
        Route::post('/courses/{id}/automation', 'Api\\V2\\CourseController@automation');
        Route::post('/courses/{courseId}/generate', 'Api\\V2\\CourseContentController@generate');
        Route::post('/courses/{courseId}/fetch', 'Api\\V2\\CourseContentController@fetch');
        Route::post('/courses/{courseId}/items', 'Api\\V2\\CourseItemController@store');
        Route::post('/courses/{courseId}/items/{id}', 'Api\\V2\\CourseItemController@update');
        Route::put('/courses/{courseId}/items/{id}', 'Api\\V2\\CourseItemController@update');
        Route::delete('/courses/{courseId}/items/{id}', 'Api\\V2\\CourseItemController@destroy');
        Route::post('/course-items', 'Api\\V2\\CourseItemController@storeFromModal');
        Route::post('/course-items/{id}', 'Api\\V2\\CourseItemController@updateFromModal');
        Route::put('/course-items/{id}', 'Api\\V2\\CourseItemController@updateFromModal');
        Route::post('/course-items/{id}/complete', 'Api\\V2\\CourseItemController@complete');
        Route::post('/course-items/{id}/publish', 'Api\\V2\\CourseItemController@publish');
        Route::post('/course-items/{id}/quiz', 'Api\\V2\\CourseQuizController@store');
        Route::put('/course-items/{id}/quiz', 'Api\\V2\\CourseQuizController@store');
        Route::post('/course-items/{id}/quiz/attempts', 'Api\\V2\\CourseQuizController@submit');
        Route::delete('/course-items/{id}', 'Api\\V2\\CourseItemController@destroy');
        Route::post('/courses/{courseId}/discussions', 'Api\\V2\\DiscussionController@store');
        Route::post('/courses/{courseId}/discussions/{id}/reply', 'Api\\V2\\DiscussionController@reply');

        Route::post('/llm/chat', 'Api\\V2\\LlmController@chat');
        Route::post('/llm/ask-ai', 'Api\\V2\\LlmController@askAi');
        Route::post('/llm/chat/stop', 'Api\\V2\\LlmController@stopChat');
        Route::post('/llm/attachments', 'Api\\V2\\LlmAttachmentController@store');
        Route::delete('/llm/attachments/{id}', 'Api\\V2\\LlmAttachmentController@destroy');

        Route::post('/llm/sessions', 'Api\\V2\\LlmSessionController@createSession');
        Route::put('/llm/sessions/{id}/title', 'Api\\V2\\LlmSessionController@updateSessionTitle');
        Route::post('/llm/sessions/{id}/clear', 'Api\\V2\\LlmSessionController@clearSession');
        Route::post('/llm/sessions/{id}/regenerate', 'Api\\V2\\LlmSessionController@regenerateSession');
        Route::post('/llm/sessions/{id}/messages/{conversationId}/branch', 'Api\\V2\\LlmSessionController@branchFromMessage');
        Route::put('/llm/sessions/{id}/messages/{conversationId}/feedback', 'Api\\V2\\LlmSessionController@feedbackMessage');
        Route::post('/llm/sessions/{id}/toggle-pin', 'Api\\V2\\LlmSessionController@togglePinSession');
        Route::delete('/llm/sessions/{id}', 'Api\\V2\\LlmSessionController@deleteSession');

        Route::post('/llm/agents', 'Api\\V2\\LlmAgentController@store');
        Route::put('/llm/agents/{id}', 'Api\\V2\\LlmAgentController@update');
        Route::delete('/llm/agents/{id}', 'Api\\V2\\LlmAgentController@destroy');
        Route::post('/llm/agents/{id}/toggle-status', 'Api\\V2\\LlmAgentController@toggleStatus');
        Route::post('/llm/agents/create-draft', 'Api\\V2\\LlmAgentController@createDraft');
        Route::put('/llm/agents/{id}/draft', 'Api\\V2\\LlmAgentController@updateDraft');
        Route::post('/llm/agents/{id}/publish', 'Api\\V2\\LlmAgentController@publishDraft');
        Route::post('/llm/agents/{id}/test-chat', 'Api\\V2\\LlmAgentController@testChat');
        Route::post('/llm/models/{id}/test', 'Api\\V2\\LlmController@testModel');
    });

    // admin capability: 高权限管理接口（按需开放）
    Route::group(['middleware' => ['hybrid.token:admin']], function () {
        Route::post('/llm/providers', 'Api\\V2\\LlmController@saveProvider');
        Route::put('/llm/providers/{id}', 'Api\\V2\\LlmController@saveProvider');
        Route::delete('/llm/providers/{id}', 'Api\\V2\\LlmController@deleteProvider');

        Route::post('/llm/models', 'Api\\V2\\LlmController@saveModel');
        Route::put('/llm/models/{id}', 'Api\\V2\\LlmController@saveModel');
        Route::delete('/llm/models/{id}', 'Api\\V2\\LlmController@deleteModel');

        Route::post('/wechat/notes', 'Api\\V2\\WechatController@addNote');
        Route::post('/wechat/addNote', 'Api\\V2\\WechatController@addNote');
        Route::post('/wechat/articles/status', 'Api\\V2\\WechatController@articleSubStatus');
        Route::post('/wechat/articles/status/{articleSub}', 'Api\\V2\\WechatController@articleSubStatus');
    });
});
