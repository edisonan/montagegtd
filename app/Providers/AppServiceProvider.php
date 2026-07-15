<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Monolog\Processor\UidProcessor;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		// $monolog = Log::getMonolog();
		// $monolog->pushProcessor(new UidProcessor());
		if (config('app.debug')) {
			\DB::listen ( function ($query) {
				Log::info ( $query->sql );
			} );
		}

        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');
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
