<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Monolog\Processor\UidProcessor;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		// $monolog = Log::getMonolog();
		// $monolog->pushProcessor(new UidProcessor());
		\DB::listen ( function ($query) {
			Log::info ( $query->sql );
		} );
		
		//
		
		// 注册LLM服务提供者
		$this->app->register(\App\Providers\LlmServiceProvider::class);
		// 注册课程服务提供者
		$this->app->register(\App\Providers\CourseServiceProvider::class);
	}
	
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}
}