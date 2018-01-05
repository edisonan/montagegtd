<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
		'App\Task' => 'App\Policies\TaskPolicy',
        'App\Pomo' => 'App\Policies\PomoPolicy',
        'App\Note' => 'App\Policies\NotePolicy',
    	'App\Mind' => 'App\Policies\MindPolicy',
    	'App\Setting' => 'App\Policies\SettingPolicy',
    	'App\Goal' => 'App\Policies\GoalPolicy',
    	'App\Feed' => 'App\Policies\FeedPolicy',
    	'App\Category' => 'App\Policies\CategoryPolicy',
    	'App\Article' => 'App\Policies\ArticlePolicy',
    	'App\Thing' => 'App\Policies\ThingPolicy',
    	'App\FeedSub' => 'App\Policies\FeedSubPolicy',
    	'App\ArticleSub' => 'App\Policies\ArticleSubPolicy',
    	'App\KindleLog' => 'App\Policies\KindleLogPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
