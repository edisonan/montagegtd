<?php

namespace App\Console\Commands;

use App\Services\DigestGenerationService;
use Illuminate\Console\Command;

class DigestGenerateScheduled extends Command
{
    protected $signature = 'digest:generate-scheduled {--limit=10 : Process pending task count} {--enqueue-only=0 : Only enqueue due tasks}';
    protected $description = 'Generate scheduled digest pages for whitelisted users';

    protected $digestGenerationService;

    public function __construct(DigestGenerationService $digestGenerationService)
    {
        parent::__construct();
        $this->digestGenerationService = $digestGenerationService;
    }

    public function handle()
    {
        $created = $this->digestGenerationService->enqueueDueTasks(50);
        $this->line('queued=' . $created);

        if ((int)$this->option('enqueue-only') === 1) {
            return 0;
        }

        $result = $this->digestGenerationService->processPendingTasks(max(1, (int)$this->option('limit')));
        $this->info(sprintf(
            'processed=%d succeeded=%d failed=%d skipped=%d',
            (int)$result['processed'],
            (int)$result['succeeded'],
            (int)$result['failed'],
            (int)$result['skipped']
        ));

        return 0;
    }
}
