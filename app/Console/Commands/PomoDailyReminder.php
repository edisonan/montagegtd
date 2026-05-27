<?php

namespace App\Console\Commands;

use App\Services\FocusService;
use Illuminate\Console\Command;

/**
 * 专注每日提醒
 *
 * @author edison.an
 *
 */
class FocusDailyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'focus_daily_reminder {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Focus daily Reminder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $w = date("w");
        if ($w == 0 || $w == 6) {
            Log::info('not work time!');
            return;
        }

        $type = $this->argument('type');
        if ($type != 1 || $type != 2) {
            Log::error('focus daily type wrong');
            return;
        }

        /**
         *
         * @var FocusService $focusService
         */
        $focusService = app(FocusService::class);
        $focusService->scheduleFocusDailyReminder($type);
    }
}

