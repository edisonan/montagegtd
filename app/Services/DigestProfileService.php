<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Repositories\UserDigestProfileRepository;

class DigestProfileService
{
    protected $profileRepository;
    protected $whitelistService;

    public function __construct(
        UserDigestProfileRepository $profileRepository,
        DigestWhitelistService $whitelistService
    ) {
        $this->profileRepository = $profileRepository;
        $this->whitelistService = $whitelistService;
    }

    public function getProfileByUserId($userId)
    {
        $this->assertWhitelist($userId);

        return $this->profileRepository->findEnabledByUserId($userId);
    }

    public function saveProfileByUserId($userId, array $data)
    {
        $this->assertWhitelist($userId);

        $payload = array(
            'enabled' => isset($data['enabled']) ? (bool)$data['enabled'] : true,
            'topics_json' => $this->normalizeArrayField($data['topics'] ?? array()),
            'include_keywords_json' => $this->normalizeArrayField($data['include_keywords'] ?? array()),
            'exclude_keywords_json' => $this->normalizeArrayField($data['exclude_keywords'] ?? array()),
            'preferred_categories_json' => $this->normalizeArrayField($data['preferred_categories'] ?? array()),
            'time_window_days' => (int)($data['time_window_days'] ?? 7),
            'frequency' => (string)($data['frequency'] ?? 'daily'),
            'max_articles' => (int)($data['max_articles'] ?? 20),
            'output_style' => isset($data['output_style']) ? (string)$data['output_style'] : null,
        );

        return $this->profileRepository->updateOrCreateEnabledByUserId($userId, $payload);
    }

    public function assertWhitelist($userId)
    {
        if (!$this->whitelistService->isUserEnabled($userId)) {
            throw new CustomException('当前用户未开通汇合页能力');
        }
    }

    protected function normalizeArrayField($value)
    {
        if (!is_array($value)) {
            return array();
        }

        $items = array();
        foreach ($value as $item) {
            $item = trim((string)$item);
            if ($item !== '') {
                $items[] = $item;
            }
        }

        return array_values(array_unique($items));
    }
}
