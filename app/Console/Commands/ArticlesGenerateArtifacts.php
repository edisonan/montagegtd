<?php

namespace App\Console\Commands;

use App\Models\ArticleSub;
use App\Models\Artifact;
use App\Services\ArtifactService;
use Illuminate\Console\Command;

class ArticlesGenerateArtifacts extends Command
{
    protected $signature = 'articles:generate-artifacts {--limit=10 : Max articles to process this run} {--max-attempts=2 : Max generation attempts per artifact}';
    protected $description = 'Generate visualization/mind-map artifacts for read_later and starred articles (max N attempts)';

    protected $artifactService;

    public function __construct(ArtifactService $artifactService)
    {
        parent::__construct();
        $this->artifactService = $artifactService;
    }

    public function handle()
    {
        $limit = max(1, min(50, (int)$this->option('limit')));
        $maxAttempts = max(1, (int)$this->option('max-attempts'));

        // 取出稍后阅读或收藏的文章订阅，带文章
        $subs = ArticleSub::with('article')
            ->whereIn('status', array('read_later', 'star'))
            ->whereHas('article')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();

        $generated = 0;
        $succeeded = 0;
        $failed = 0;
        $skippedExceeded = 0;
        $skippedDone = 0;
        $errors = array();

        foreach ($subs as $sub) {
            $article = $sub->article;
            if (!$article || empty($article->id)) {
                continue;
            }
            $articleId = (int)$article->id;
            $userId = (int)($sub->user_id ?: 0);

            // 每篇尝试生成两个类型
            foreach (array('visual_reading', 'mind_map') as $type) {
                $artifact = Artifact::where('user_id', $userId)
                    ->where('related_type', 'article')
                    ->where('related_id', $articleId)
                    ->where('artifact_type', $type)
                    ->first();

                // 已成功 → 跳过
                if ($artifact && $artifact->status === Artifact::STATUS_SUCCESS) {
                    $skippedDone++;
                    continue;
                }

                // 尝试次数超限 → 跳过
                $attempts = $artifact ? (int)$artifact->attempt_count : 0;
                if ($attempts >= $maxAttempts) {
                    $skippedExceeded++;
                    continue;
                }

                try {
                    $result = $this->artifactService->ensure($userId, 'article', $articleId, $type, array());
                    if ($result['generated']) {
                        $generated++;
                    }
                    if ($result['artifact'] && $result['artifact']->status === Artifact::STATUS_SUCCESS) {
                        $succeeded++;
                    } else {
                        $failed++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = $articleId . ':' . $type . ' ' . $e->getMessage();
                }
            }
        }

        $this->info(sprintf(
            'batch=%d generated=%d succeeded=%d failed=%d skipped_done=%d skipped_exceeded=%d',
            $subs->count(),
            $generated,
            $succeeded,
            $failed,
            $skippedDone,
            $skippedExceeded
        ));

        foreach (array_slice($errors, 0, 5) as $err) {
            $this->line('  err: ' . $err);
        }

        return 0;
    }
}