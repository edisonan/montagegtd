<?php

namespace App\Services;

use App\Repositories\PointAccountRepository;

class PointAccountService {

    protected $pointAccountRepository;

    public function __construct(PointAccountRepository $pointAccountRepository) {
        $this->pointAccountRepository = $pointAccountRepository;
    }

    /**
     * 获取或创建账户
     */
    public function getOrCreateAccount(int $userId) {
        $account = $this->pointAccountRepository->getByUserId($userId);

        if (empty($account)) {
            $account = $this->pointAccountRepository->create($userId);
        }

        return $account;
    }

    /**
     * 增加 GP
     */
    public function increaseGP(int $userId, int $amount) {
        $account = $this->getOrCreateAccount($userId);

        $account->gp_balance += $amount;
        $account->save();

        return $account;
    }
}
