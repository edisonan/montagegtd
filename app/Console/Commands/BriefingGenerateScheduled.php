<?php

namespace App\Console\Commands;

use App\Services\BriefingGenerationService;
use Illuminate\Console\Command;

class BriefingGenerateScheduled extends Command
{
    protected $signature = 'briefing:generate-scheduled {--limit=50 : Max configs to process} {--check-time= : Force a specific HH:MM check time, defaults to now (Asia/Shanghai)}';
    protected $description = 'Generate scheduled article briefings for enabled configs whose scheduled time has arrived';

    protected $generationService;

    public function __construct(BriefingGenerationService $generationService)
    {
        parent::__construct();
        $this->generationService = $generationService;
    }

    public function handle()
    {
        $checkTime = $this->option('check-time');
        if (!$checkTime) {
            $checkTime = date('H:i', time());
        }
        if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', (string)$checkTime)) {
            $this->error('invalid check-time (expected HH:MM)');
            return 1;
        }

        $result = $this->generationService->generateDueConfigs((string)$checkTime, max(1, (int)$this->option('limit')));
        $this->info(sprintf(
            'generated=%d failed=%d (check_time=%s)',
            (int)$result['generated'],
            (int)$result['failed'],
            (string)$checkTime
        ));

        return 0;
    }
}
