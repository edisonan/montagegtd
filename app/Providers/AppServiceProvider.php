<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Pagination\BootstrapFourPresenter;
use Illuminate\Pagination\SimpleBootstrapFourPresenter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
		/**
		LengthAwarePaginator::presenter(function (Paginator $paginator) {
    		return new BootstrapFourPresenter($paginator);
    	});
    	
    	// Change the ->simplePaginate() presenter
    	Paginator::presenter(function (PaginatorContract $paginator) {
    		return new BootstrapFourPresenter($paginator);
    	});
		*/
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
