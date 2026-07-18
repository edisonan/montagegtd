<?php
use Illuminate\Routing\Router;

Admin::registerAuthRoutes ();

Route::group ( [ 
		'prefix' => config ( 'admin.route.prefix' ),
		'namespace' => config ( 'admin.route.namespace' ),
		'middleware' => config ( 'admin.route.middleware' ) 
], function (Router $router) {
	
	$router->get ( '/', 'HomeController@index' );
	$router->get ( 'statistic', 'UserController@statistic' );
	$router->get ( 'system-logs', 'SystemLogController@index' );
	$router->get ( 'system-logs/content', 'SystemLogController@content' );
	
	$router->resource ( 'users', UserController::class );
	
	$router->resource ( 'feeds', FeedController::class );
	$router->resource ( 'feedsubs', FeedSubController::class );
	
	$router->resource ( 'articles', ArticleController::class );
	$router->resource ( 'articlemarks', ArticleMarkController::class );
	$router->resource ( 'feedbacks', FeedbackController::class );
	$router->resource ( 'plans', PlanController::class );
	$router->resource ( 'categorys', CategoryController::class );
	$router->resource ( 'kindlelogs', KindleLogController::class );
	$router->resource ( 'minds', MindController::class );
	$router->resource ( 'notes', NoteController::class );
	$router->resource ( 'focus', FocusController::class );
	$router->resource ( 'settings', SettingController::class );
	$router->resource ( 'tasks', TaskController::class );
	$router->resource ( 'journals', JournalController::class );

    $router->resource('codes', CodeController::class);
    $router->get('getCode/{id}', 'CodeController@getCode');
    $router->post('updateCode', 'CodeController@updateCode');
    $router->post('generateCode', 'CodeController@generateCode');
    $router->get('getCodeHistory/{id}', 'CodeController@getCodeHistory');
    $router->get('getHistoryCode/{id}', 'CodeController@getHistoryCode');
    
    $router->resource('llm-providers', LlmProviderController::class);
    $router->resource('llm-models', LlmModelController::class);
    $router->resource('llm-usage-logs', LlmUsageLogController::class);
    $router->resource('llm-agents', LlmAgentController::class);
    
    $router->resource('courses', CourseController::class);
    
    $router->get('applications/{id}/workspace-data', 'ApplicationController@workspaceData');
    $router->get('applications/{id}/codes/{codeId}', 'ApplicationController@showCode');
    $router->post('applications', 'ApplicationController@store');
    $router->put('applications/{id}/meta', 'ApplicationController@updateMeta');
    $router->post('applications/{id}/codes', 'ApplicationController@storeCode');
    $router->put('applications/{id}/codes/{codeId}', 'ApplicationController@updateCode');
    $router->post('applications/{id}/codes/{codeId}/ai-generate', 'ApplicationController@generateCode');
    $router->get('applications/{id}/codes/{codeId}/history', 'ApplicationController@getCodeHistory');
    $router->post('applications/{id}/codes/{codeId}/history/{historyId}/rollback', 'ApplicationController@rollbackCodeHistory');
    $router->get('applications/{id}/virtual-tables', 'ApplicationController@virtualTables');
    $router->post('applications/{id}/virtual-tables', 'ApplicationController@storeVirtualTable');
    $router->post('applications/{id}/virtual-tables/{tableId}/fields', 'ApplicationController@storeVirtualField');
    $router->get('applications/{id}/virtual-tables/{tableId}/records', 'ApplicationController@virtualTableRecords');
    $router->post('applications/{id}/virtual-tables/{tableId}/records', 'ApplicationController@storeVirtualRecord');
    $router->put('applications/{id}/virtual-tables/{tableId}/records/{recordId}', 'ApplicationController@updateVirtualRecord');
    $router->delete('applications/{id}/virtual-tables/{tableId}/records/{recordId}', 'ApplicationController@deleteVirtualRecord');
    $router->get('applications/{id}', 'ApplicationController@show');
    $router->get('applications', 'ApplicationController@index');
} );
