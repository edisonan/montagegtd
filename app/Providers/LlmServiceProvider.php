<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LlmProviderService;
use App\Services\LlmModelService;
use App\Services\LlmProviderCredentialService;
use App\Services\LlmUsageLogService;
use App\Repositories\LlmProviderRepository;
use App\Repositories\LlmModelRepository;
use App\Repositories\LlmProviderCredentialRepository;
use App\Repositories\LlmUsageLogRepository;

class LlmServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 绑定仓库
        $this->app->bind(LlmProviderRepository::class, function ($app) {
            return new LlmProviderRepository();
        });
        
        $this->app->bind(LlmModelRepository::class, function ($app) {
            return new LlmModelRepository();
        });
        
        $this->app->bind(LlmProviderCredentialRepository::class, function ($app) {
            return new LlmProviderCredentialRepository();
        });
        
        $this->app->bind(LlmUsageLogRepository::class, function ($app) {
            return new LlmUsageLogRepository();
        });

        // 绑定服务
        $this->app->bind(LlmProviderService::class, function ($app) {
            return new LlmProviderService($app->make(LlmProviderRepository::class));
        });
        
        $this->app->bind(LlmModelService::class, function ($app) {
            return new LlmModelService($app->make(LlmModelRepository::class));
        });
        
        $this->app->bind(LlmProviderCredentialService::class, function ($app) {
            return new LlmProviderCredentialService($app->make(LlmProviderCredentialRepository::class));
        });
        
        $this->app->bind(LlmUsageLogService::class, function ($app) {
            return new LlmUsageLogService($app->make(LlmUsageLogRepository::class));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}