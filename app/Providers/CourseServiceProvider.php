<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CourseService;
use App\Repositories\CourseRepository;
use App\Repositories\UserCourseRepository;
use App\Repositories\CourseItemRepository;

class CourseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 绑定仓库
        $this->app->bind(CourseRepository::class, function ($app) {
            return new CourseRepository();
        });
        
        $this->app->bind(UserCourseRepository::class, function ($app) {
            return new UserCourseRepository();
        });
        
        $this->app->bind(CourseItemRepository::class, function ($app) {
            return new CourseItemRepository();
        });

        // 绑定服务
        $this->app->bind(CourseService::class, function ($app) {
            return new CourseService(
                $app->make(CourseRepository::class),
                $app->make(UserCourseRepository::class),
                $app->make(CourseItemRepository::class)
            );
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