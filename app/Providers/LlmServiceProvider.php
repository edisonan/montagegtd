<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LlmPolishService;
use App\Services\LlmConversationService;
use App\Services\LlmAgentService;
use App\Services\LlmAgentVersionService;

class LlmServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 绑定LlmPolishService
        $this->app->singleton(LlmPolishService::class, function ($app) {
            return new LlmPolishService(
                $app->make('db'),
                $app->make('log'),
                $app->make('config')
            );
        });

        // 绑定LlmConversationService
        $this->app->singleton(LlmConversationService::class, function ($app) {
            return new LlmConversationService(
                $app->make('App\Repositories\LlmConversationRepository')
            );
        });
        
        // 绑定LlmAgentService
        $this->app->singleton(LlmAgentService::class, function ($app) {
            return new LlmAgentService(
                $app->make('App\Repositories\LlmAgentRepository'),
                $app->make('App\Repositories\LlmAgentVersionRepository')
            );
        });
        
        // 绑定LlmAgentVersionService
        $this->app->singleton(LlmAgentVersionService::class, function ($app) {
            return new LlmAgentVersionService(
                $app->make('App\Repositories\LlmAgentVersionRepository'),
                $app->make('App\Repositories\LlmAgentRepository')
            );
        });
    }

    public function boot()
    {
        //
    }
}