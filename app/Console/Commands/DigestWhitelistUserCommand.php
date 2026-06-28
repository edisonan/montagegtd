<?php

namespace App\Console\Commands;

use App\Services\DigestWhitelistService;
use Illuminate\Console\Command;

class DigestWhitelistUserCommand extends Command
{
    protected $signature = 'digest:whitelist-user {user_id : User id} {--disable=0 : Disable instead of enable} {--expires-at= : Expire time Y-m-d H:i:s} {--remark= : Remark text}';
    protected $description = 'Enable or disable digest whitelist for a user';

    protected $digestWhitelistService;

    public function __construct(DigestWhitelistService $digestWhitelistService)
    {
        parent::__construct();
        $this->digestWhitelistService = $digestWhitelistService;
    }

    public function handle()
    {
        $userId = (int)$this->argument('user_id');
        $disable = (int)$this->option('disable') === 1;

        $record = $this->digestWhitelistService->addOrUpdateUser($userId, array(
            'enabled' => !$disable,
            'expires_at' => $this->option('expires-at') ?: null,
            'remark' => $this->option('remark') ?: null,
        ));

        $this->info(sprintf(
            'user_id=%d enabled=%s expires_at=%s',
            $userId,
            $record->enabled ? '1' : '0',
            $record->expires_at ? $record->expires_at->toDateTimeString() : 'NULL'
        ));

        return 0;
    }
}
