<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\CourseContentService;
use Illuminate\Console\Command;

class CourseGenerateScheduled extends Command
{
    protected $signature = 'course:generate-scheduled {--limit=5}';
    protected $description = 'Generate or fetch due course content into drafts';

    public function handle(CourseContentService $contentService)
    {
        $limit = max(1, min(50, (int)$this->option('limit')));
        $courses = Course::whereNotNull('automation_config')->limit($limit)->get();
        $processed = 0;
        foreach ($courses as $course) {
            $config = is_array($course->automation_config) ? $course->automation_config : array();
            if (empty($config['enabled'])) continue;
            if (!empty($config['next_run_at']) && strtotime($config['next_run_at']) > time()) continue;
            try {
                $contentService->runScheduled($course);
                $processed++;
                $config['next_run_at'] = now()->addMinutes(max(60, (int)($config['frequency_minutes'] ?? 1440)))->toDateTimeString();
                $course->automation_config = $config;
                $course->save();
            } catch (\Throwable $e) {
                $this->error('course ' . $course->id . ': ' . $e->getMessage());
                $config['next_run_at'] = now()->addHours(1)->toDateTimeString();
                $course->automation_config = $config;
                $course->save();
            }
        }
        $this->info('processed=' . $processed);
        return 0;
    }
}
