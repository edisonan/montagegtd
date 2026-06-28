<?php

namespace App\Console\Commands;

use App\Services\DigestProfileService;
use Illuminate\Console\Command;

class DigestUpsertProfileCommand extends Command
{
    protected $signature = 'digest:upsert-profile {user_id : User id} {--topics=} {--include=} {--exclude=} {--categories=} {--window=7} {--frequency=daily} {--max-articles=20} {--style=} {--disabled=0}';
    protected $description = 'Create or update digest profile for a user';

    protected $digestProfileService;

    public function __construct(DigestProfileService $digestProfileService)
    {
        parent::__construct();
        $this->digestProfileService = $digestProfileService;
    }

    public function handle()
    {
        $userId = (int)$this->argument('user_id');
        $profile = $this->digestProfileService->saveProfileByUserId($userId, array(
            'enabled' => (int)$this->option('disabled') !== 1,
            'topics' => $this->csvOption('topics'),
            'include_keywords' => $this->csvOption('include'),
            'exclude_keywords' => $this->csvOption('exclude'),
            'preferred_categories' => $this->csvOption('categories'),
            'time_window_days' => (int)$this->option('window'),
            'frequency' => (string)$this->option('frequency'),
            'max_articles' => (int)$this->option('max-articles'),
            'output_style' => $this->option('style') ?: null,
        ));

        $this->info(sprintf(
            'profile_id=%d user_id=%d frequency=%s window=%d max_articles=%d',
            $profile->id,
            $profile->user_id,
            $profile->frequency,
            $profile->time_window_days,
            $profile->max_articles
        ));

        return 0;
    }

    protected function csvOption($name)
    {
        $raw = trim((string)$this->option($name));
        if ($raw === '') {
            return array();
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw)), function ($item) {
            return $item !== '';
        }));
    }
}
