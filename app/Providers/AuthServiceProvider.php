<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
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
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        //
    }
}
