<?php

namespace App\Console\Commands;

use App\Services\ArticleAiService;
use Illuminate\Console\Command;

class ArticlesClassifyPending extends Command
{
    protected $signature = 'articles:classify-pending {--limit=20 : Process pending task count} {--backfill=0 : Backfill task count before processing}';
    protected $description = 'Classify pending article AI tasks';

    protected $articleAiService;

    public function __construct(ArticleAiService $articleAiService)
    {
        parent::__construct();
        $this->articleAiService = $articleAiService;
    }

    public function handle()
    {
        $backfill = max(0, (int)$this->option('backfill'));
        $limit = max(1, (int)$this->option('limit'));

        if ($backfill > 0) {
            $created = $this->articleAiService->backfillPendingTasks($backfill);
            $this->line('backfill_created=' . $created);
        }

        $result = $this->articleAiService->processPendingTasks($limit);

        $this->info(sprintf(
            'batch_size=%d processed=%d succeeded=%d failed=%d skipped=%d throttled=%d',
            (int)($result['batch_size'] ?? 0),
            (int)$result['processed'],
            (int)$result['succeeded'],
            (int)$result['failed'],
            (int)$result['skipped'],
            (int)$result['throttled']
        ));

        return 0;
    }
}
