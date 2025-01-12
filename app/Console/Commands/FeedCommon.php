<?php

namespace App\Console\Commands;

use App\Services\FeedService;
use Illuminate\Console\Command;

/**
 * 获取订阅源最新文章
 *
 * @author edison.an
 *
 */
class FeedCommon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed_common {active_level}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Common Feed get New Article';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $activeLevel = $this->argument('active_level');

        /**
         * feedService
         *
         * @var FeedService $feedService
         */
        $feedService = app(FeedService::class);
        $result = $feedService->checkFeedByActiveLevel($activeLevel);
    }
}
