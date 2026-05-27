<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider {
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [ 
			'App\Models\Model' => 'App\Policies\ModelPolicy',
			'App\Models\Task' => 'App\Policies\TaskPolicy',
			'App\Models\Focus' => 'App\Policies\FocusPolicy',
			'App\Models\Note' => 'App\Policies\NotePolicy',
			'App\Models\Mind' => 'App\Policies\MindPolicy',
			'App\Models\Setting' => 'App\Policies\SettingPolicy',
			'App\Models\Plan' => 'App\Policies\PlanPolicy',
			'App\Models\Feed' => 'App\Policies\FeedPolicy',
			'App\Models\Category' => 'App\Policies\CategoryPolicy',
			'App\Models\Article' => 'App\Policies\ArticlePolicy',
			'App\Models\Journal' => 'App\Policies\JournalPolicy',
			'App\Models\FeedSub' => 'App\Policies\FeedSubPolicy',
			'App\Models\ArticleSub' => 'App\Policies\ArticleSubPolicy',
			'App\Models\KindleLog' => 'App\Policies\KindleLogPolicy',
			'App\Models\DailySummary' => 'App\Policies\DailySummaryPolicy' 
	];
	
	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->registerPolicies ();
		
		//
	}
}
