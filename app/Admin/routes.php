<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
	$router->resource('users', UserController::class);
    $router->get('statistic', 'UserController@statistic');
    
	$router->resource('feeds', FeedController::class);
	$router->resource('feedsubs', FeedSubController::class);

});
