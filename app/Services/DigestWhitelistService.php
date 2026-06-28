<?php

namespace App\Services;

use App\Repositories\DigestWhitelistUserRepository;

class DigestWhitelistService
{
    protected $repository;

    public function __construct(DigestWhitelistUserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function isUserEnabled($userId)
    {
        return $this->repository->isEnabledForUser($userId);
    }

    public function addOrUpdateUser($userId, array $data = array())
    {
        $data['user_id'] = $userId;
        if (!isset($data['enabled'])) {
            $data['enabled'] = true;
        }

        return $this->repository->updateOrCreateByUserId($userId, $data);
    }

    public function removeUser($userId)
    {
        return $this->repository->deleteByUserId($userId);
    }
}
