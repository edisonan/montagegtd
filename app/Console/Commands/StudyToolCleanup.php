<?php

namespace App\Console\Commands;

use App\Services\StudyToolService;
use Illuminate\Console\Command;

class StudyToolCleanup extends Command
{
    protected $signature = 'study_tools:cleanup';
    protected $description = '清理过期的学习工具房间与消息';

    protected $studyToolService;

    public function __construct(StudyToolService $studyToolService)
    {
        parent::__construct();
        $this->studyToolService = $studyToolService;
    }

    public function handle()
    {
        $result = $this->studyToolService->cleanupExpired();
        $this->info('study_tools cleanup done: rooms_deleted=' . $result['rooms_deleted']);
        return 0;
    }
}