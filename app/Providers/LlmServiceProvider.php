<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LlmPolishService;
use App\Services\LlmConversationService;

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
    }

    public function boot()
    {
        //
    }
}