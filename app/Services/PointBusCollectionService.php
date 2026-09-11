<?php

namespace App\Services;

use App\Models\PointAccount;
use Illuminate\Support\Facades\DB;

class PointBusCollectionService
{
    const RARITY_UNLOCK_SCORE = array('N' => 10, 'R' => 25, 'SR' => 60, 'SSR' => 120);
    const RARITY_CHECKIN_COST = array('N' => 2, 'R' => 3, 'SR' => 5, 'SSR' => 8);
    const STATION_SCORE = 3;

    protected $pointRecordService;

    public function __construct(PointRecordService $pointRecordService)
    {
        $this->pointRecordService = $pointRecordService;
    }

    public function getOverview(int $userId): array
    {
        $catalog = DB::table('point_bus_catalog')
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $collections = array();
        $collectionRows = DB::table('point_bus_user_collections')
            ->where('user_id', $userId)
            ->get();
        foreach ($collectionRows as $row) {
            $collections[(int)$row->catalog_id] = $row;
        }

        $lines = array();
        $typeStats = array();
        $collected = 0;
        $completed = 0;
        $totalScore = 0;
        $totalStations = 0;
        $checkedStations = 0;

        foreach ($catalog as $item) {
            $line = $this->formatLine($item, isset($collections[(int)$item->id]) ? $collections[(int)$item->id] : null);
            $lines[] = $line;
            $totalStations += (int)$line['station_count'];
            $checkedStations += (int)$line['progress'];
            if ($line['unlocked']) {
                $collected++;
                $totalScore += (int)$line['score'];
            }
            if ($line['completed']) {
                $completed++;
            }

            $type = $line['type'];
            if (!isset($typeStats[$type])) {
                $typeStats[$type] = array('type' => $type, 'total' => 0, 'collected' => 0, 'completed' => 0);
            }
            $typeStats[$type]['total']++;
            if ($line['unlocked']) {
                $typeStats[$type]['collected']++;
            }
            if ($line['completed']) {
                $typeStats[$type]['completed']++;
            }
        }

        $todayChecked = DB::table('point_bus_checkin_logs')
            ->where('user_id', $userId)
            ->where('created_at', '>=', date('Y-m-d 00:00:00'))
            ->count();

        $recentCheckins = DB::table('point_bus_checkin_logs')
            ->join('point_bus_catalog', 'point_bus_checkin_logs.catalog_id', '=', 'point_bus_catalog.id')
            ->where('point_bus_checkin_logs.user_id', $userId)
            ->orderBy('point_bus_checkin_logs.id', 'desc')
            ->limit(20)
            ->select(
                'point_bus_checkin_logs.id',
                'point_bus_checkin_logs.station_name',
                'point_bus_checkin_logs.ap_cost',
                'point_bus_checkin_logs.reward_ap',
                'point_bus_checkin_logs.created_at',
                'point_bus_catalog.name as line_name',
                'point_bus_catalog.color as line_color'
            )
            ->get();

        $title = $this->resolveTitle($totalScore);

        return array(
            'account' => array('ap_balance' => $this->getApBalance($userId)),
            'lines' => $lines,
            'type_stats' => array_values($typeStats),
            'stats' => array(
                'total_lines' => count($lines),
                'collected_lines' => $collected,
                'completed_lines' => $completed,
                'total_stations' => $totalStations,
                'checked_stations' => $checkedStations,
                'score' => $totalScore,
                'title' => $title['name'],
                'next_title' => $title['next_name'],
                'next_title_score' => $title['next_score'],
                'free_checkin_available' => $todayChecked === 0,
            ),
            'achievements' => $this->getAchievements($userId, $lines, $checkedStations, $totalScore),
            'recent_checkins' => $recentCheckins,
            'leaderboard' => $this->getLeaderboard(10),
        );
    }

    public function unlockLine(int $userId, int $catalogId): array
    {
        return DB::transaction(function () use ($userId, $catalogId) {
            $line = DB::table('point_bus_catalog')->where('id', $catalogId)->lockForUpdate()->first();
            if (!$line || (int)$line->status !== 1) {
                throw new \RuntimeException('线路不存在或已下架');
            }

            $exists = DB::table('point_bus_user_collections')
                ->where('user_id', $userId)
                ->where('catalog_id', $catalogId)
                ->first();
            if ($exists) {
                throw new \RuntimeException('该线路已在收藏馆中');
            }

            $cost = max(0, (int)$line->price_ap);
            $account = $this->lockPointAccount($userId);
            if ($cost > 0 && (int)$account->ap_balance < $cost) {
                throw new \RuntimeException('可用积分不足，无法解锁该线路');
            }
            if ($cost > 0) {
                $account->ap_balance = (int)$account->ap_balance - $cost;
                $account->save();
                $this->pointRecordService->record(
                    $userId,
                    'AP',
                    -$cost,
                    (int)$account->ap_balance,
                    'bus_collection_unlock',
                    $catalogId,
                    '解锁线路：' . $line->name
                );
            }

            $rarity = $this->normalizeRarity($line->rarity);
            $score = isset(self::RARITY_UNLOCK_SCORE[$rarity]) ? self::RARITY_UNLOCK_SCORE[$rarity] : 10;

            $id = DB::table('point_bus_user_collections')->insertGetId(array(
                'user_id' => $userId,
                'catalog_id' => $catalogId,
                'status' => 'active',
                'progress' => 0,
                'score' => $score,
                'unlocked_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            $newAchievements = $this->syncAchievements($userId);

            $collection = DB::table('point_bus_user_collections')->where('id', $id)->first();
            return array(
                'line' => $this->formatLine($line, $collection),
                'ap_balance' => $this->getApBalance($userId),
                'new_achievements' => $newAchievements,
            );
        });
    }

    public function checkinStation(int $userId, int $catalogId): array
    {
        return DB::transaction(function () use ($userId, $catalogId) {
            $line = DB::table('point_bus_catalog')->where('id', $catalogId)->lockForUpdate()->first();
            if (!$line || (int)$line->status !== 1) {
                throw new \RuntimeException('线路不存在或已下架');
            }

            $collection = DB::table('point_bus_user_collections')
                ->where('user_id', $userId)
                ->where('catalog_id', $catalogId)
                ->lockForUpdate()
                ->first();
            if (!$collection) {
                throw new \RuntimeException('请先解锁该线路');
            }

            $stations = $this->decodeStations($line->stations);
            $count = count($stations);
            if ($count === 0) {
                throw new \RuntimeException('线路站点数据缺失');
            }

            $progress = max(0, (int)$collection->progress);
            if ($progress >= $count || (string)$collection->status === 'completed') {
                throw new \RuntimeException('该线路已全线贯通');
            }

            $station = isset($stations[$progress]) ? $stations[$progress] : array('name' => '第' . ($progress + 1) . '站');
            $stationName = is_array($station) && isset($station['name']) ? (string)$station['name'] : (string)$station;

            $todayChecked = DB::table('point_bus_checkin_logs')
                ->where('user_id', $userId)
                ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                ->count();
            $isFree = $todayChecked === 0;

            $rarity = $this->normalizeRarity($line->rarity);
            $cost = $isFree ? 0 : (isset(self::RARITY_CHECKIN_COST[$rarity]) ? self::RARITY_CHECKIN_COST[$rarity] : 2);

            $account = $this->lockPointAccount($userId);
            if ($cost > 0 && (int)$account->ap_balance < $cost) {
                throw new \RuntimeException('可用积分不足，无法打卡');
            }
            if ($cost > 0) {
                $account->ap_balance = (int)$account->ap_balance - $cost;
                $account->save();
                $this->pointRecordService->record(
                    $userId,
                    'AP',
                    -$cost,
                    (int)$account->ap_balance,
                    'bus_checkin',
                    $catalogId,
                    '公交打卡：' . $line->name . ' - ' . $stationName
                );
            }

            $newProgress = $progress + 1;
            $completed = $newProgress >= $count;
            $rewardAp = 0;
            if ($completed) {
                $rewardAp = max(0, (int)$line->reward_ap);
                if ($rewardAp > 0) {
                    $account->ap_balance = (int)$account->ap_balance + $rewardAp;
                    $account->save();
                    $this->pointRecordService->record(
                        $userId,
                        'AP',
                        $rewardAp,
                        (int)$account->ap_balance,
                        'bus_line_complete',
                        $catalogId,
                        '线路贯通奖励：' . $line->name
                    );
                }
            }

            DB::table('point_bus_checkin_logs')->insert(array(
                'user_id' => $userId,
                'catalog_id' => $catalogId,
                'station_index' => $progress,
                'station_name' => mb_substr($stationName, 0, 128),
                'ap_cost' => $cost,
                'reward_ap' => $rewardAp,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            $unlockScore = isset(self::RARITY_UNLOCK_SCORE[$rarity]) ? self::RARITY_UNLOCK_SCORE[$rarity] : 10;
            $score = $unlockScore + ($newProgress * self::STATION_SCORE) + ($completed ? $unlockScore : 0);

            DB::table('point_bus_user_collections')->where('id', $collection->id)->update(array(
                'progress' => $newProgress,
                'score' => $score,
                'status' => $completed ? 'completed' : 'active',
                'completed_at' => $completed ? date('Y-m-d H:i:s') : null,
                'last_checkin_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            $newAchievements = $this->syncAchievements($userId);

            $updated = DB::table('point_bus_user_collections')->where('id', $collection->id)->first();
            return array(
                'line' => $this->formatLine($line, $updated),
                'checkin' => array(
                    'station_name' => $stationName,
                    'station_index' => $progress,
                    'ap_cost' => $cost,
                    'free' => $isFree,
                    'reward_ap' => $rewardAp,
                    'completed' => $completed,
                ),
                'ap_balance' => $this->getApBalance($userId),
                'new_achievements' => $newAchievements,
            );
        });
    }

    public function claimAchievement(int $userId, string $code): array
    {
        return DB::transaction(function () use ($userId, $code) {
            $achievement = DB::table('point_bus_achievements')->where('code', $code)->where('status', 1)->first();
            if (!$achievement) {
                throw new \RuntimeException('成就不存在');
            }

            $userAchievement = DB::table('point_bus_user_achievements')
                ->where('user_id', $userId)
                ->where('achievement_code', $code)
                ->lockForUpdate()
                ->first();
            if (!$userAchievement) {
                throw new \RuntimeException('成就尚未达成');
            }
            if ((int)$userAchievement->claimed === 1) {
                throw new \RuntimeException('奖励已领取');
            }

            $reward = max(0, (int)$achievement->reward_ap);
            if ($reward > 0) {
                $account = $this->lockPointAccount($userId);
                $account->ap_balance = (int)$account->ap_balance + $reward;
                $account->save();
                $this->pointRecordService->record(
                    $userId,
                    'AP',
                    $reward,
                    (int)$account->ap_balance,
                    'bus_achievement',
                    $achievement->id,
                    '公交成就奖励：' . $achievement->name
                );
            }

            DB::table('point_bus_user_achievements')->where('id', $userAchievement->id)->update(array(
                'claimed' => 1,
                'claimed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            return array(
                'achievement' => array(
                    'code' => (string)$achievement->code,
                    'name' => (string)$achievement->name,
                    'reward_ap' => $reward,
                ),
                'ap_balance' => $this->getApBalance($userId),
            );
        });
    }

    public function getLeaderboard(int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $rows = DB::table('point_bus_user_collections as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->select(
                'c.user_id',
                'u.name',
                DB::raw('SUM(c.score) as score'),
                DB::raw('COUNT(*) as collected'),
                DB::raw("SUM(CASE WHEN c.status = 'completed' THEN 1 ELSE 0 END) as completed")
            )
            ->groupBy('c.user_id', 'u.name')
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->get();

        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'user_id' => (int)$row->user_id,
                'name' => (string)$row->name,
                'score' => (int)$row->score,
                'collected' => (int)$row->collected,
                'completed' => (int)$row->completed,
                'title' => $this->resolveTitle((int)$row->score)['name'],
            );
        }
        return $result;
    }

    protected function getAchievements(int $userId, array $lines, int $checkedStations, int $totalScore): array
    {
        $catalog = DB::table('point_bus_achievements')->where('status', 1)->orderBy('sort_order', 'asc')->get();
        $userRows = DB::table('point_bus_user_achievements')->where('user_id', $userId)->get();
        $userMap = array();
        foreach ($userRows as $row) {
            $userMap[(string)$row->achievement_code] = $row;
        }

        $metrics = $this->buildMetrics($lines, $checkedStations, $totalScore);

        $result = array();
        foreach ($catalog as $item) {
            $progress = $this->resolveAchievementProgress($item, $metrics);
            $userRow = isset($userMap[(string)$item->code]) ? $userMap[(string)$item->code] : null;
            $result[] = array(
                'code' => (string)$item->code,
                'name' => (string)$item->name,
                'description' => (string)$item->description,
                'reward_ap' => (int)$item->reward_ap,
                'badge_icon' => (string)$item->badge_icon,
                'current' => $progress['current'],
                'target' => $progress['target'],
                'percent' => $progress['target'] > 0 ? min(100, (int)round($progress['current'] / $progress['target'] * 100)) : 0,
                'unlocked' => $userRow ? true : false,
                'claimed' => $userRow ? ((int)$userRow->claimed === 1) : false,
            );
        }
        return $result;
    }

    protected function buildMetrics(array $lines, int $checkedStations, int $totalScore): array
    {
        $typeTotal = array();
        $typeCollected = array();
        $rarityCollected = array();
        $collected = 0;
        $completed = 0;

        foreach ($lines as $line) {
            $type = $line['type'];
            if (!isset($typeTotal[$type])) {
                $typeTotal[$type] = 0;
                $typeCollected[$type] = 0;
            }
            $typeTotal[$type]++;
            $rarity = $line['rarity'];
            if (!isset($rarityCollected[$rarity])) {
                $rarityCollected[$rarity] = 0;
            }
            if ($line['unlocked']) {
                $collected++;
                $typeCollected[$type]++;
                $rarityCollected[$rarity]++;
            }
            if ($line['completed']) {
                $completed++;
            }
        }

        return array(
            'collected' => $collected,
            'completed' => $completed,
            'checked' => $checkedStations,
            'score' => $totalScore,
            'type_total' => $typeTotal,
            'type_collected' => $typeCollected,
            'rarity_collected' => $rarityCollected,
        );
    }

    protected function resolveAchievementProgress($achievement, array $metrics): array
    {
        $payload = $this->decodePayload($achievement->condition_payload);
        $type = (string)$achievement->condition_type;

        switch ($type) {
            case 'collect_count':
                return array('current' => $metrics['collected'], 'target' => max(1, (int)($payload['target'] ?? 1)));
            case 'complete_count':
                return array('current' => $metrics['completed'], 'target' => max(1, (int)($payload['target'] ?? 1)));
            case 'checkin_count':
                return array('current' => $metrics['checked'], 'target' => max(1, (int)($payload['target'] ?? 1)));
            case 'score':
                return array('current' => $metrics['score'], 'target' => max(1, (int)($payload['target'] ?? 1)));
            case 'category_all':
                $category = isset($payload['type']) ? (string)$payload['type'] : '';
                $total = isset($metrics['type_total'][$category]) ? (int)$metrics['type_total'][$category] : 0;
                $current = isset($metrics['type_collected'][$category]) ? (int)$metrics['type_collected'][$category] : 0;
                return array('current' => $current, 'target' => max(1, $total));
            case 'rarity_collect':
                $rarity = isset($payload['rarity']) ? (string)$payload['rarity'] : 'SSR';
                $current = isset($metrics['rarity_collected'][$rarity]) ? (int)$metrics['rarity_collected'][$rarity] : 0;
                return array('current' => $current, 'target' => max(1, (int)($payload['target'] ?? 1)));
        }

        return array('current' => 0, 'target' => 1);
    }

    protected function syncAchievements(int $userId): array
    {
        $lines = $this->collectUserLines($userId);
        $checked = (int)DB::table('point_bus_checkin_logs')->where('user_id', $userId)->count();
        $totalScore = 0;
        foreach ($lines as $line) {
            $totalScore += (int)$line['score'];
        }

        $metrics = $this->buildMetrics($lines, $checked, $totalScore);
        $catalog = DB::table('point_bus_achievements')->where('status', 1)->get();
        $existing = DB::table('point_bus_user_achievements')
            ->where('user_id', $userId)
            ->pluck('achievement_code')
            ->all();
        $existingMap = array_flip($existing);

        $newly = array();
        foreach ($catalog as $achievement) {
            if (isset($existingMap[(string)$achievement->code])) {
                continue;
            }
            $progress = $this->resolveAchievementProgress($achievement, $metrics);
            if ($progress['current'] >= $progress['target']) {
                DB::table('point_bus_user_achievements')->insert(array(
                    'user_id' => $userId,
                    'achievement_code' => (string)$achievement->code,
                    'claimed' => 0,
                    'unlocked_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
                $newly[] = array(
                    'code' => (string)$achievement->code,
                    'name' => (string)$achievement->name,
                    'description' => (string)$achievement->description,
                    'reward_ap' => (int)$achievement->reward_ap,
                    'badge_icon' => (string)$achievement->badge_icon,
                );
            }
        }
        return $newly;
    }

    protected function collectUserLines(int $userId): array
    {
        $catalog = DB::table('point_bus_catalog')
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        $collections = array();
        $rows = DB::table('point_bus_user_collections')->where('user_id', $userId)->get();
        foreach ($rows as $row) {
            $collections[(int)$row->catalog_id] = $row;
        }

        $lines = array();
        foreach ($catalog as $item) {
            $lines[] = $this->formatLine($item, isset($collections[(int)$item->id]) ? $collections[(int)$item->id] : null);
        }
        return $lines;
    }

    protected function formatLine($item, $collection): array
    {
        $rarity = $this->normalizeRarity($item->rarity);
        $stations = $this->decodeStations($item->stations);
        $stationCount = count($stations);
        if ($stationCount === 0) {
            $stationCount = max(0, (int)$item->station_count);
        }

        $unlocked = $collection ? true : false;
        $progress = $collection ? max(0, min($stationCount, (int)$collection->progress)) : 0;
        $completed = $collection ? ((string)$collection->status === 'completed' || ($stationCount > 0 && $progress >= $stationCount)) : false;

        $unlockScore = isset(self::RARITY_UNLOCK_SCORE[$rarity]) ? self::RARITY_UNLOCK_SCORE[$rarity] : 10;
        $score = 0;
        if ($unlocked) {
            $score = (int)$collection->score;
            $expected = $unlockScore + ($progress * self::STATION_SCORE) + ($completed ? $unlockScore : 0);
            if ($score < $expected) {
                $score = $expected;
            }
        }

        $formattedStations = array();
        foreach ($stations as $index => $station) {
            if (is_array($station)) {
                $formattedStations[] = array(
                    'index' => $index,
                    'name' => isset($station['name']) ? (string)$station['name'] : ('第' . ($index + 1) . '站'),
                    'lng' => isset($station['lng']) ? (float)$station['lng'] : null,
                    'lat' => isset($station['lat']) ? (float)$station['lat'] : null,
                    'checked' => $index < $progress,
                );
            } else {
                $formattedStations[] = array(
                    'index' => $index,
                    'name' => (string)$station,
                    'lng' => null,
                    'lat' => null,
                    'checked' => $index < $progress,
                );
            }
        }

        $nextStation = null;
        if ($unlocked && !$completed && isset($formattedStations[$progress])) {
            $nextStation = $formattedStations[$progress]['name'];
        }

        return array(
            'id' => (int)$item->id,
            'code' => (string)$item->code,
            'name' => (string)$item->name,
            'type' => (string)$item->type,
            'rarity' => $rarity,
            'color' => (string)$item->color,
            'district' => (string)$item->district,
            'price_ap' => (int)$item->price_ap,
            'reward_ap' => (int)$item->reward_ap,
            'distance_km' => (float)$item->distance_km,
            'first_bus' => (string)$item->first_bus,
            'last_bus' => (string)$item->last_bus,
            'station_count' => $stationCount,
            'stations' => $formattedStations,
            'description' => (string)$item->description,
            'is_free' => (int)$item->is_free === 1,
            'checkin_cost' => isset(self::RARITY_CHECKIN_COST[$rarity]) ? self::RARITY_CHECKIN_COST[$rarity] : 2,
            'unlocked' => $unlocked,
            'progress' => $progress,
            'completed' => $completed,
            'score' => $score,
            'base_score' => $unlockScore,
            'next_station' => $nextStation,
            'last_checkin_at' => $collection && !empty($collection->last_checkin_at) ? (string)$collection->last_checkin_at : null,
            'unlocked_at' => $collection && !empty($collection->unlocked_at) ? (string)$collection->unlocked_at : null,
        );
    }

    protected function resolveTitle(int $score): array
    {
        $titles = array(
            array('min' => 1500, 'name' => '城市交通大师'),
            array('min' => 700, 'name' => '京城活地图'),
            array('min' => 300, 'name' => '线路达人'),
            array('min' => 100, 'name' => '公交常客'),
            array('min' => 0, 'name' => '交通萌新'),
        );

        $current = $titles[count($titles) - 1]['name'];
        $nextName = null;
        $nextScore = 0;
        foreach ($titles as $title) {
            if ($score >= $title['min']) {
                $current = $title['name'];
                break;
            }
            $nextName = $title['name'];
            $nextScore = $title['min'];
        }

        return array('name' => $current, 'next_name' => $nextName, 'next_score' => $nextScore);
    }

    protected function getApBalance(int $userId): int
    {
        $account = PointAccount::where('user_id', $userId)->first();
        return $account ? (int)$account->ap_balance : 0;
    }

    protected function lockPointAccount(int $userId): PointAccount
    {
        $account = PointAccount::where('user_id', $userId)->lockForUpdate()->first();
        if (!$account) {
            $account = PointAccount::create(array('user_id' => $userId));
        }
        return $account;
    }

    protected function normalizeRarity($rarity): string
    {
        $rarity = strtoupper((string)$rarity);
        return in_array($rarity, array('N', 'R', 'SR', 'SSR'), true) ? $rarity : 'N';
    }

    protected function decodeStations($payload): array
    {
        if (empty($payload)) {
            return array();
        }
        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : array();
    }

    protected function decodePayload($payload): array
    {
        if (empty($payload)) {
            return array();
        }
        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : array();
    }
}
