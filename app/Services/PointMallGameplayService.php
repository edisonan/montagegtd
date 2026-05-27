<?php

namespace App\Services;

use App\Models\PointAccount;
use App\Models\PointMallEntitlement;
use Illuminate\Support\Facades\DB;

class PointMallGameplayService
{
    const TREE_LEVEL_MAP = array(
        1 => 0,
        2 => 120,
        3 => 300,
        4 => 600,
    );

    protected $pointRecordService;

    public function __construct(PointRecordService $pointRecordService)
    {
        $this->pointRecordService = $pointRecordService;
    }

    public function getTreeOverview(int $userId, int $selectedTreeId = 0): array
    {
        $this->applyNaturalGrowth($userId);

        $treeRows = DB::table('point_tree_instances')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        $trees = array();
        foreach ($treeRows as $row) {
            $trees[] = $this->formatTree($row);
        }

        $seedlings = DB::table('point_mall_entitlements')
            ->where('user_id', $userId)
            ->where('entitlement_type', 'tree_seedling')
            ->whereIn('status', array('pending_planting', 'active'))
            ->orderBy('id', 'desc')
            ->get();

        $leaderboard = DB::table('point_tree_instances')
            ->where('status', 'alive')
            ->orderBy('growth_value', 'desc')
            ->orderBy('updated_at', 'asc')
            ->limit(10)
            ->get(array('id', 'user_id', 'name', 'species', 'growth_value', 'stage'));

        $selected = $this->resolveCurrentTree($trees, $selectedTreeId);
        $nextTreeId = $this->resolveNextTreeId($trees, $selected ? (int)$selected['id'] : 0);

        return array(
            'trees' => $trees,
            'seedlings' => $seedlings,
            'season' => $this->resolveSeasonInfo(),
            'leaderboard' => $leaderboard,
            'current_tree' => $selected,
            'next_tree_id' => $nextTreeId,
        );
    }

    public function plantTree(int $userId, int $entitlementId, string $name = '我的树')
    {
        return DB::transaction(function () use ($userId, $entitlementId, $name) {
            $entitlement = PointMallEntitlement::where('id', $entitlementId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            if (!$entitlement) {
                throw new \RuntimeException('树苗券不存在');
            }
            if (!in_array($entitlement->status, array('pending_planting', 'active'), true)) {
                throw new \RuntimeException('树苗券不可使用');
            }

            $meta = $this->decodePayload($entitlement->meta_payload);
            $species = $this->resolveTreeSpeciesFromMeta($meta);

            $treeId = DB::table('point_tree_instances')->insertGetId(array(
                'user_id' => $userId,
                'name' => mb_substr($name ?: '我的树', 0, 64),
                'species' => $species,
                'growth_value' => 0,
                'stage' => 'sapling',
                'health' => 100,
                'status' => 'alive',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            $currentQty = max(1, (int)$entitlement->quantity);
            if ($currentQty > 1) {
                $entitlement->quantity = $currentQty - 1;
                $entitlement->status = 'active';
            } else {
                $entitlement->quantity = 0;
                $entitlement->status = 'used';
            }
            $entitlement->save();

            return $this->formatTree(DB::table('point_tree_instances')->where('id', $treeId)->first());
        });
    }

    public function waterTree(int $userId, int $treeId, int $pointCost = 10)
    {
        return DB::transaction(function () use ($userId, $treeId, $pointCost) {
            $tree = DB::table('point_tree_instances')
                ->where('id', $treeId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            if (!$tree) {
                throw new \RuntimeException('树不存在');
            }
            if ((string)$tree->status !== 'alive') {
                throw new \RuntimeException('当前树状态不可浇水');
            }

            if (!empty($tree->last_watered_at) && substr((string)$tree->last_watered_at, 0, 10) === date('Y-m-d')) {
                throw new \RuntimeException('今天已经浇过水了');
            }

            $tier = $this->resolveCareTier($pointCost);
            $account = $this->lockPointAccount($userId);
            if ((int)$account->ap_balance < $tier['cost']) {
                throw new \RuntimeException('可用积分不足，无法浇水');
            }
            $account->ap_balance = (int)$account->ap_balance - $tier['cost'];
            $account->save();
            $this->pointRecordService->record(
                $userId,
                'AP',
                -$tier['cost'],
                (int)$account->ap_balance,
                'tree_water',
                $treeId,
                '浇水消耗（' . $tier['tier'] . '）'
            );

            $inc = $tier['growth'];
            $growth = (int)$tree->growth_value + $inc;
            $stage = $this->resolveTreeStage($growth);
            $health = min(100, (int)$tree->health + 2);

            DB::table('point_tree_instances')->where('id', $treeId)->update(array(
                'growth_value' => $growth,
                'stage' => $stage,
                'health' => $health,
                'last_watered_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            DB::table('point_tree_water_logs')->insert(array(
                'tree_id' => $treeId,
                'user_id' => $userId,
                'water_value' => $inc,
                'point_cost' => $tier['cost'],
                'water_tier' => $tier['tier'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            return $this->formatTree(DB::table('point_tree_instances')->where('id', $treeId)->first());
        });
    }

    public function decorateTree(int $userId, int $treeId, array $decoration): object
    {
        DB::table('point_tree_instances')
            ->where('id', $treeId)
            ->where('user_id', $userId)
            ->update(array(
                'decoration_payload' => json_encode($decoration, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

        $tree = DB::table('point_tree_instances')
            ->where('id', $treeId)
            ->where('user_id', $userId)
            ->first();
        if (!$tree) {
            throw new \RuntimeException('树不存在');
        }
        return $tree;
    }

    public function getPetOverview(int $userId): array
    {
        $pets = DB::table('point_pet_instances')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        $petData = array();
        foreach ($pets as $pet) {
            $petData[] = $this->formatPet($pet);
        }

        $entitlements = DB::table('point_mall_entitlements')
            ->where('user_id', $userId)
            ->where('entitlement_type', 'pet_companion')
            ->whereIn('status', array('pending_adoption', 'active'))
            ->orderBy('id', 'desc')
            ->get();

        return array(
            'pets' => $petData,
            'companions' => $entitlements,
        );
    }

    public function adoptPet(int $userId, int $entitlementId, string $name = '我的宠物')
    {
        return DB::transaction(function () use ($userId, $entitlementId, $name) {
            $entitlement = PointMallEntitlement::where('id', $entitlementId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            if (!$entitlement) {
                throw new \RuntimeException('宠物权益不存在');
            }
            if (!in_array((string)$entitlement->status, array('pending_adoption', 'active'), true)) {
                throw new \RuntimeException('宠物权益不可使用');
            }

            $meta = $this->decodePayload($entitlement->meta_payload);
            $species = !empty($meta['pet_type']) ? (string)$meta['pet_type'] : 'cat';
            $petId = DB::table('point_pet_instances')->insertGetId(array(
                'user_id' => $userId,
                'name' => mb_substr($name ?: '我的宠物', 0, 64),
                'species' => $species,
                'growth_value' => 0,
                'level' => 1,
                'health' => 100,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            $this->consumeEntitlementUnit($entitlement, 'pending_adoption');

            return $this->formatPet(DB::table('point_pet_instances')->where('id', $petId)->first());
        });
    }

    public function feedPet(int $userId, int $petId, int $pointCost = 10)
    {
        return DB::transaction(function () use ($userId, $petId, $pointCost) {
            $pet = DB::table('point_pet_instances')
                ->where('id', $petId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            if (!$pet) {
                throw new \RuntimeException('宠物不存在');
            }
            if ((string)$pet->status !== 'active') {
                throw new \RuntimeException('当前宠物状态不可喂养');
            }

            $tier = $this->resolveCareTier($pointCost);
            $account = $this->lockPointAccount($userId);
            if ((int)$account->ap_balance < $tier['cost']) {
                throw new \RuntimeException('可用积分不足，无法喂养');
            }
            $account->ap_balance = (int)$account->ap_balance - $tier['cost'];
            $account->save();
            $this->pointRecordService->record(
                $userId,
                'AP',
                -$tier['cost'],
                (int)$account->ap_balance,
                'pet_feed',
                $petId,
                '宠物喂养消耗（' . $tier['tier'] . '）'
            );

            $growth = (int)$pet->growth_value + $tier['growth'];
            $level = $this->resolveLevelByGrowth($growth);
            $health = min(100, (int)$pet->health + 2);
            DB::table('point_pet_instances')->where('id', $petId)->update(array(
                'growth_value' => $growth,
                'level' => $level,
                'health' => $health,
                'last_fed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            DB::table('point_pet_feed_logs')->insert(array(
                'pet_id' => $petId,
                'user_id' => $userId,
                'feed_value' => $tier['growth'],
                'point_cost' => $tier['cost'],
                'feed_tier' => $tier['tier'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            return $this->formatPet(DB::table('point_pet_instances')->where('id', $petId)->first());
        });
    }

    public function getPondOverview(int $userId): array
    {
        $fishes = DB::table('point_fish_instances')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        $fishData = array();
        foreach ($fishes as $fish) {
            $fishData[] = $this->formatFish($fish);
        }

        $entitlements = DB::table('point_mall_entitlements')
            ->where('user_id', $userId)
            ->where('entitlement_type', 'fish_fry')
            ->whereIn('status', array('pending_release', 'active'))
            ->orderBy('id', 'desc')
            ->get();

        return array(
            'fishes' => $fishData,
            'fry' => $entitlements,
        );
    }

    public function releaseFish(int $userId, int $entitlementId, string $name = '我的鱼')
    {
        return DB::transaction(function () use ($userId, $entitlementId, $name) {
            $entitlement = PointMallEntitlement::where('id', $entitlementId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            if (!$entitlement) {
                throw new \RuntimeException('鱼苗权益不存在');
            }
            if (!in_array((string)$entitlement->status, array('pending_release', 'active'), true)) {
                throw new \RuntimeException('鱼苗权益不可使用');
            }

            $meta = $this->decodePayload($entitlement->meta_payload);
            $species = !empty($meta['fish_type']) ? (string)$meta['fish_type'] : 'goldfish';
            $fishId = DB::table('point_fish_instances')->insertGetId(array(
                'user_id' => $userId,
                'name' => mb_substr($name ?: '我的鱼', 0, 64),
                'species' => $species,
                'growth_value' => 0,
                'level' => 1,
                'health' => 100,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            $this->consumeEntitlementUnit($entitlement, 'pending_release');

            return $this->formatFish(DB::table('point_fish_instances')->where('id', $fishId)->first());
        });
    }

    public function feedFish(int $userId, int $fishId, int $pointCost = 10)
    {
        return DB::transaction(function () use ($userId, $fishId, $pointCost) {
            $fish = DB::table('point_fish_instances')
                ->where('id', $fishId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            if (!$fish) {
                throw new \RuntimeException('鱼不存在');
            }
            if ((string)$fish->status !== 'active') {
                throw new \RuntimeException('当前鱼状态不可投喂');
            }

            $tier = $this->resolveCareTier($pointCost);
            $account = $this->lockPointAccount($userId);
            if ((int)$account->ap_balance < $tier['cost']) {
                throw new \RuntimeException('可用积分不足，无法投喂');
            }
            $account->ap_balance = (int)$account->ap_balance - $tier['cost'];
            $account->save();
            $this->pointRecordService->record(
                $userId,
                'AP',
                -$tier['cost'],
                (int)$account->ap_balance,
                'fish_feed',
                $fishId,
                '池塘投喂消耗（' . $tier['tier'] . '）'
            );

            $growth = (int)$fish->growth_value + $tier['growth'];
            $level = $this->resolveLevelByGrowth($growth);
            $health = min(100, (int)$fish->health + 2);
            DB::table('point_fish_instances')->where('id', $fishId)->update(array(
                'growth_value' => $growth,
                'level' => $level,
                'health' => $health,
                'last_fed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            DB::table('point_fish_feed_logs')->insert(array(
                'fish_id' => $fishId,
                'user_id' => $userId,
                'feed_value' => $tier['growth'],
                'point_cost' => $tier['cost'],
                'feed_tier' => $tier['tier'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            return $this->formatFish(DB::table('point_fish_instances')->where('id', $fishId)->first());
        });
    }

    public function getLotteryOverview(int $userId): array
    {
        $pools = DB::table('point_lottery_pools')->where('status', 1)->orderBy('id', 'desc')->get();
        $logs = DB::table('point_lottery_draw_logs')->where('user_id', $userId)->orderBy('id', 'desc')->limit(30)->get();
        $pity = array();
        $poolItems = array();
        foreach ($pools as $pool) {
            $pity[(int)$pool->id] = $this->countContinuousApDraws($userId, (int)$pool->id);
            $items = DB::table('point_lottery_pool_items')
                ->where('pool_id', (int)$pool->id)
                ->where('status', 1)
                ->orderBy('id', 'asc')
                ->get(array('id', 'reward_name', 'reward_type', 'weight', 'stock'));
            $totalWeight = 0;
            foreach ($items as $item) {
                if ((int)$item->stock === 0) {
                    continue;
                }
                $totalWeight += max(0, (int)$item->weight);
            }
            $poolItems[(int)$pool->id] = array();
            foreach ($items as $item) {
                $weight = max(0, (int)$item->weight);
                $probability = $totalWeight > 0 && (int)$item->stock !== 0
                    ? round(($weight / $totalWeight) * 100, 2)
                    : 0;
                $poolItems[(int)$pool->id][] = array(
                    'id' => (int)$item->id,
                    'reward_name' => (string)$item->reward_name,
                    'reward_type' => (string)$item->reward_type,
                    'weight' => $weight,
                    'stock' => (int)$item->stock,
                    'probability' => $probability,
                    'rarity' => $this->resolveRarity($probability),
                );
            }
        }

        return array(
            'pools' => $pools,
            'logs' => $logs,
            'pity' => $pity,
            'pool_items' => $poolItems,
        );
    }

    public function drawLottery(int $userId, int $poolId): array
    {
        $batch = $this->drawLotteryMany($userId, $poolId, 1);
        $first = isset($batch['results'][0]) ? $batch['results'][0] : array();
        return $first;
    }

    public function drawLotteryMany(int $userId, int $poolId, int $times = 1): array
    {
        if ($times <= 0 || $times > 10) {
            throw new \RuntimeException('抽奖次数不合法');
        }

        return DB::transaction(function () use ($userId, $poolId, $times) {
            $pool = DB::table('point_lottery_pools')->where('id', $poolId)->lockForUpdate()->first();
            if (!$pool || (int)$pool->status !== 1) {
                throw new \RuntimeException('奖池不可用');
            }

            $account = PointAccount::where('user_id', $userId)->lockForUpdate()->first();
            if (!$account) {
                $account = PointAccount::create(array('user_id' => $userId));
            }

            $items = DB::table('point_lottery_pool_items')
                ->where('pool_id', $poolId)
                ->where('status', 1)
                ->orderBy('id', 'asc')
                ->get();
            if ($items->count() === 0) {
                throw new \RuntimeException('奖池暂无奖品');
            }

            $costEach = (int)$pool->cost_ap;
            $costTotal = $costEach * $times;
            if ((int)$account->ap_balance < $costTotal) {
                throw new \RuntimeException('可用积分不足，无法抽奖');
            }

            $account->ap_balance = (int)$account->ap_balance - $costTotal;
            $account->save();
            $this->pointRecordService->record(
                $userId,
                'AP',
                -$costTotal,
                (int)$account->ap_balance,
                'lottery_draw',
                $poolId,
                '积分抽奖消耗 x' . $times
            );

            $results = array();
            $continuousApDraws = $this->countContinuousApDraws($userId, $poolId);

            for ($i = 0; $i < $times; $i++) {
                $forceNonAp = ($continuousApDraws >= 29);
                $selected = $forceNonAp ? $this->weightedPick($items->all(), true) : $this->weightedPick($items->all(), false);
                if (!$selected) {
                    $selected = $this->weightedPick($items->all(), false);
                }
                if (!$selected) {
                    continue;
                }
                $result = $this->applyLotteryReward($userId, $poolId, $selected, $account, $costEach);
                $results[] = $result;
                if (($result['reward_type'] ?? '') === 'ap') {
                    $continuousApDraws++;
                } else {
                    $continuousApDraws = 0;
                }
            }

            if (empty($results)) {
                throw new \RuntimeException('抽奖失败，请重试');
            }

            return array(
                'times' => $times,
                'results' => $results,
                'summary' => $times > 1 ? ('已完成' . $times . '次抽奖') : '已完成1次抽奖',
                'pity_progress' => $continuousApDraws,
            );
        });
    }

    public function getBusOverview(int $userId): array
    {
        $lines = DB::table('point_bus_lines')->where('status', 1)->orderBy('id', 'desc')->get();
        $owned = DB::table('point_user_bus_lines')
            ->join('point_bus_lines', 'point_user_bus_lines.line_id', '=', 'point_bus_lines.id')
            ->where('point_user_bus_lines.user_id', $userId)
            ->where('point_user_bus_lines.status', 'active')
            ->select(
                'point_user_bus_lines.id as user_line_id',
                'point_user_bus_lines.bought_at',
                'point_bus_lines.id as line_id',
                'point_bus_lines.code',
                'point_bus_lines.name',
                'point_bus_lines.color',
                'point_bus_lines.price_ap',
                'point_bus_lines.path_payload'
            )
            ->orderBy('point_user_bus_lines.id', 'desc')
            ->get();

        $runs = DB::table('point_bus_run_logs')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $runCount = (int)DB::table('point_bus_run_logs')->where('user_id', $userId)->count();
        $arrivedCount = (int)DB::table('point_bus_run_logs')->where('user_id', $userId)->where('run_status', 'arrived')->count();
        $rewardedCount = (int)DB::table('point_bus_run_logs')
            ->where('user_id', $userId)
            ->whereRaw("JSON_EXTRACT(meta_payload, '$.reward_granted') = 'true'")
            ->count();

        return array(
            'lines' => $lines,
            'owned_lines' => $owned,
            'recent_runs' => $runs,
            'stats' => array(
                'total_runs' => $runCount,
                'arrived_runs' => $arrivedCount,
                'rewarded_runs' => $rewardedCount,
                'total_reward_ap' => $rewardedCount * 2,
            ),
        );
    }

    public function buyBusLine(int $userId, int $lineId)
    {
        return DB::transaction(function () use ($userId, $lineId) {
            $line = DB::table('point_bus_lines')->where('id', $lineId)->lockForUpdate()->first();
            if (!$line || (int)$line->status !== 1) {
                throw new \RuntimeException('线路不存在或已下架');
            }

            $exists = DB::table('point_user_bus_lines')
                ->where('user_id', $userId)
                ->where('line_id', $lineId)
                ->where('status', 'active')
                ->first();
            if ($exists) {
                throw new \RuntimeException('该线路已购买');
            }

            $account = PointAccount::where('user_id', $userId)->lockForUpdate()->first();
            if (!$account) {
                $account = PointAccount::create(array('user_id' => $userId));
            }

            $cost = (int)$line->price_ap;
            if ((int)$account->ap_balance < $cost) {
                throw new \RuntimeException('可用积分不足');
            }

            $account->ap_balance = (int)$account->ap_balance - $cost;
            $account->save();

            $this->pointRecordService->record(
                $userId,
                'AP',
                -$cost,
                (int)$account->ap_balance,
                'bus_line_buy',
                $lineId,
                '购买公交线路：' . $line->name
            );

            $id = DB::table('point_user_bus_lines')->insertGetId(array(
                'user_id' => $userId,
                'line_id' => $lineId,
                'status' => 'active',
                'bought_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            return DB::table('point_user_bus_lines')->where('id', $id)->first();
        });
    }

    public function startBusRun(int $userId, int $userLineId)
    {
        $owned = DB::table('point_user_bus_lines')
            ->join('point_bus_lines', 'point_user_bus_lines.line_id', '=', 'point_bus_lines.id')
            ->where('point_user_bus_lines.id', $userLineId)
            ->where('point_user_bus_lines.user_id', $userId)
            ->select(
                'point_user_bus_lines.id as user_line_id',
                'point_bus_lines.id as line_id',
                'point_bus_lines.name',
                'point_bus_lines.color',
                'point_bus_lines.path_payload'
            )
            ->first();
        if (!$owned) {
            throw new \RuntimeException('线路未购买');
        }

        $logId = DB::table('point_bus_run_logs')->insertGetId(array(
            'user_id' => $userId,
            'user_line_id' => $userLineId,
            'run_status' => 'running',
            'progress' => 0,
            'meta_payload' => json_encode(array(
                'line_name' => $owned->name,
                'color' => $owned->color,
                'path' => $this->decodePayload($owned->path_payload),
            ), JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));

        return DB::table('point_bus_run_logs')->where('id', $logId)->first();
    }

    public function tickBusRun(int $userId, int $runId)
    {
        $run = DB::table('point_bus_run_logs')
            ->where('id', $runId)
            ->where('user_id', $userId)
            ->first();
        if (!$run) {
            throw new \RuntimeException('运行记录不存在');
        }
        if ((string)$run->run_status !== 'running') {
            return $run;
        }

        $progress = min(100, (int)$run->progress + mt_rand(8, 16));
        $status = $progress >= 100 ? 'arrived' : 'running';
        $meta = $this->decodePayload($run->meta_payload);
        $rewardGranted = !empty($meta['reward_granted']);

        if ($status === 'arrived' && !$rewardGranted) {
            $account = PointAccount::where('user_id', $userId)->lockForUpdate()->first();
            if (!$account) {
                $account = PointAccount::create(array('user_id' => $userId));
            }
            $account->ap_balance = (int)$account->ap_balance + 2;
            $account->save();
            $this->pointRecordService->record(
                $userId,
                'AP',
                2,
                (int)$account->ap_balance,
                'bus_run_reward',
                $runId,
                '公交线路到站奖励'
            );
            $meta['reward_granted'] = true;
            $meta['reward_ap'] = 2;
        }

        DB::table('point_bus_run_logs')->where('id', $runId)->update(array(
            'progress' => $progress,
            'run_status' => $status,
            'meta_payload' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
        ));

        return DB::table('point_bus_run_logs')->where('id', $runId)->first();
    }

    public function getTreeLeaderboard(int $limit = 20)
    {
        $limit = max(1, min(100, $limit));
        $rows = DB::table('point_tree_instances')
            ->where('status', 'alive')
            ->orderBy('growth_value', 'desc')
            ->orderBy('updated_at', 'asc')
            ->limit($limit)
            ->get(array('id', 'user_id', 'name', 'species', 'growth_value', 'stage', 'updated_at'));
        $result = array();
        foreach ($rows as $row) {
            $result[] = $this->formatTree($row);
        }
        return $result;
    }

    protected function applyNaturalGrowth(int $userId): void
    {
        $today = date('Y-m-d');
        $trees = DB::table('point_tree_instances')
            ->where('user_id', $userId)
            ->where('status', 'alive')
            ->get();

        foreach ($trees as $tree) {
            $updatedDate = !empty($tree->updated_at) ? substr((string)$tree->updated_at, 0, 10) : '';
            if ($updatedDate === $today) {
                continue;
            }

            $daysWithoutWater = 0;
            if (!empty($tree->last_watered_at)) {
                $daysWithoutWater = (int)floor((time() - strtotime($tree->last_watered_at)) / 86400);
            } else {
                $daysWithoutWater = 999;
            }

            $season = $this->resolveSeasonInfo();
            $baseGrowth = mt_rand(1, 3) + (int)($season['growth_bonus'] ?? 0);
            $baseGrowth = max(0, $baseGrowth);
            $growth = (int)$tree->growth_value + $baseGrowth;

            $healthDelta = $daysWithoutWater >= 2 ? -2 : 1;
            $health = max(30, min(100, (int)$tree->health + $healthDelta));
            $stage = $this->resolveTreeStage($growth);

            DB::table('point_tree_instances')->where('id', $tree->id)->update(array(
                'growth_value' => $growth,
                'health' => $health,
                'stage' => $stage,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        }
    }

    protected function resolveSeasonInfo(): array
    {
        $month = (int)date('n');
        if (in_array($month, array(3, 4, 5), true)) {
            return array('name' => '春季', 'growth_bonus' => 1, 'hint' => '万物生长，成长加成 +1');
        }
        if (in_array($month, array(6, 7, 8), true)) {
            return array('name' => '夏季', 'growth_bonus' => 2, 'hint' => '阳光充足，成长加成 +2');
        }
        if (in_array($month, array(9, 10, 11), true)) {
            return array('name' => '秋季', 'growth_bonus' => 1, 'hint' => '稳步成长，成长加成 +1');
        }
        return array('name' => '冬季', 'growth_bonus' => 0, 'hint' => '生长缓慢，注意保温与浇水');
    }

    protected function countContinuousApDraws(int $userId, int $poolId): int
    {
        $recentLogs = DB::table('point_lottery_draw_logs')
            ->where('user_id', $userId)
            ->where('pool_id', $poolId)
            ->orderBy('id', 'desc')
            ->limit(29)
            ->get(array('result_payload'));
        $continuousApDraws = 0;
        foreach ($recentLogs as $log) {
            $payload = $this->decodePayload($log->result_payload);
            if (($payload['reward_type'] ?? '') === 'ap') {
                $continuousApDraws++;
                continue;
            }
            break;
        }
        return $continuousApDraws;
    }

    protected function resolveRarity(float $probability): string
    {
        if ($probability <= 3) {
            return 'SSR';
        }
        if ($probability <= 8) {
            return 'SR';
        }
        if ($probability <= 18) {
            return 'R';
        }
        return 'N';
    }

    protected function weightedPick(array $items, bool $nonApOnly = false)
    {
        $total = 0;
        foreach ($items as $item) {
            if ((int)$item->stock === 0) {
                continue;
            }
            if ($nonApOnly && (string)$item->reward_type === 'ap') {
                continue;
            }
            $w = max(0, (int)$item->weight);
            $total += $w;
        }
        if ($total <= 0) {
            return null;
        }

        $rand = mt_rand(1, $total);
        $cursor = 0;
        foreach ($items as $item) {
            if ((int)$item->stock === 0) {
                continue;
            }
            if ($nonApOnly && (string)$item->reward_type === 'ap') {
                continue;
            }
            $w = max(0, (int)$item->weight);
            if ($w <= 0) {
                continue;
            }
            $cursor += $w;
            if ($rand <= $cursor) {
                return $item;
            }
        }
        return null;
    }

    protected function applyLotteryReward(int $userId, int $poolId, $selected, PointAccount $account, int $cost): array
    {
        if ((int)$selected->stock > 0) {
            DB::table('point_lottery_pool_items')->where('id', $selected->id)->update(array(
                'stock' => (int)$selected->stock - 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        }

        $payload = $this->decodePayload($selected->reward_payload);
        if ((string)$selected->reward_type === 'ap') {
            $bonus = isset($payload['ap']) ? (int)$payload['ap'] : 0;
            if ($bonus > 0) {
                $account->ap_balance = (int)$account->ap_balance + $bonus;
                $account->save();
                $this->pointRecordService->record(
                    $userId,
                    'AP',
                    $bonus,
                    (int)$account->ap_balance,
                    'lottery_reward',
                    $poolId,
                    '抽奖奖励：' . $selected->reward_name
                );
            }
        } else {
            $entitlementType = !empty($payload['entitlement_type']) ? (string)$payload['entitlement_type'] : 'lottery_chance';
            $qty = max(1, (int)($payload['quantity'] ?? 1));
            PointMallEntitlement::create(array(
                'order_id' => 0,
                'user_id' => $userId,
                'entitlement_type' => $entitlementType,
                'quantity' => $qty,
                'status' => 'active',
                'meta_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ));
        }

        DB::table('point_lottery_draw_logs')->insert(array(
            'user_id' => $userId,
            'pool_id' => $poolId,
            'item_id' => (int)$selected->id,
            'cost_ap' => $cost,
            'result_payload' => json_encode(array(
                'reward_name' => $selected->reward_name,
                'reward_type' => $selected->reward_type,
                'reward_payload' => $payload,
            ), JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));

        return array(
            'reward_name' => $selected->reward_name,
            'reward_type' => $selected->reward_type,
            'reward_payload' => $payload,
        );
    }

    protected function resolveTreeStage(int $growth): string
    {
        $level = $this->resolveLevelByGrowth($growth);
        if ($level >= 4) {
            return 'giant';
        }
        if ($level >= 3) {
            return 'mature';
        }
        if ($level >= 2) {
            return 'young';
        }
        return 'sapling';
    }

    protected function resolveLevelByGrowth(int $growth): int
    {
        if ($growth >= self::TREE_LEVEL_MAP[4]) {
            return 4;
        }
        if ($growth >= self::TREE_LEVEL_MAP[3]) {
            return 3;
        }
        if ($growth >= self::TREE_LEVEL_MAP[2]) {
            return 2;
        }
        return 1;
    }

    protected function resolveCareTier(int $pointCost): array
    {
        if ($pointCost >= 50) {
            return array('cost' => 50, 'growth' => 65, 'tier' => 'premium');
        }
        if ($pointCost >= 30) {
            return array('cost' => 30, 'growth' => 35, 'tier' => 'standard');
        }
        return array('cost' => 10, 'growth' => 10, 'tier' => 'basic');
    }

    protected function resolveCurrentTree(array $trees, int $selectedTreeId)
    {
        if (empty($trees)) {
            return null;
        }

        if ($selectedTreeId > 0) {
            foreach ($trees as $tree) {
                if ((int)$tree['id'] === $selectedTreeId) {
                    return $tree;
                }
            }
        }

        return $trees[0];
    }

    protected function resolveNextTreeId(array $trees, int $currentTreeId): int
    {
        if (count($trees) <= 1 || $currentTreeId <= 0) {
            return 0;
        }

        $count = count($trees);
        for ($i = 0; $i < $count; $i++) {
            if ((int)$trees[$i]['id'] === $currentTreeId) {
                $next = ($i + 1) % $count;
                return (int)$trees[$next]['id'];
            }
        }

        return (int)$trees[0]['id'];
    }

    protected function formatTree($tree): array
    {
        $species = (string)($tree->species ?? 'oak');
        $growth = (int)($tree->growth_value ?? 0);
        $level = $this->resolveLevelByGrowth($growth);

        return array(
            'id' => (int)$tree->id,
            'user_id' => (int)$tree->user_id,
            'name' => (string)$tree->name,
            'species' => $species,
            'growth_value' => $growth,
            'level' => $level,
            'stage' => $this->resolveTreeStage($growth),
            'health' => (int)($tree->health ?? 100),
            'status' => (string)($tree->status ?? 'alive'),
            'last_watered_at' => !empty($tree->last_watered_at) ? (string)$tree->last_watered_at : null,
            'image_url' => $this->resolveTreeImage($species, $level),
            'level_thresholds' => self::TREE_LEVEL_MAP,
            'updated_at' => !empty($tree->updated_at) ? (string)$tree->updated_at : null,
            'created_at' => !empty($tree->created_at) ? (string)$tree->created_at : null,
        );
    }

    protected function formatPet($pet): array
    {
        $species = (string)($pet->species ?? 'cat');
        $growth = (int)($pet->growth_value ?? 0);
        $level = $this->resolveLevelByGrowth($growth);

        return array(
            'id' => (int)$pet->id,
            'user_id' => (int)$pet->user_id,
            'name' => (string)$pet->name,
            'species' => $species,
            'growth_value' => $growth,
            'level' => $level,
            'health' => (int)($pet->health ?? 100),
            'status' => (string)($pet->status ?? 'active'),
            'last_fed_at' => !empty($pet->last_fed_at) ? (string)$pet->last_fed_at : null,
            'image_url' => $this->resolvePetImage($species, $level),
            'updated_at' => !empty($pet->updated_at) ? (string)$pet->updated_at : null,
            'created_at' => !empty($pet->created_at) ? (string)$pet->created_at : null,
        );
    }

    protected function formatFish($fish): array
    {
        $species = (string)($fish->species ?? 'goldfish');
        $growth = (int)($fish->growth_value ?? 0);
        $level = $this->resolveLevelByGrowth($growth);

        return array(
            'id' => (int)$fish->id,
            'user_id' => (int)$fish->user_id,
            'name' => (string)$fish->name,
            'species' => $species,
            'growth_value' => $growth,
            'level' => $level,
            'health' => (int)($fish->health ?? 100),
            'status' => (string)($fish->status ?? 'active'),
            'last_fed_at' => !empty($fish->last_fed_at) ? (string)$fish->last_fed_at : null,
            'image_url' => $this->resolveFishImage($species, $level),
            'updated_at' => !empty($fish->updated_at) ? (string)$fish->updated_at : null,
            'created_at' => !empty($fish->created_at) ? (string)$fish->created_at : null,
        );
    }

    protected function lockPointAccount(int $userId): PointAccount
    {
        $account = PointAccount::where('user_id', $userId)->lockForUpdate()->first();
        if (!$account) {
            $account = PointAccount::create(array('user_id' => $userId));
        }
        return $account;
    }

    protected function consumeEntitlementUnit(PointMallEntitlement $entitlement, string $remainStatus = 'active'): void
    {
        $currentQty = max(1, (int)$entitlement->quantity);
        if ($currentQty > 1) {
            $entitlement->quantity = $currentQty - 1;
            $entitlement->status = $remainStatus;
        } else {
            $entitlement->quantity = 0;
            $entitlement->status = 'used';
        }
        $entitlement->save();
    }

    protected function resolveTreeImage(string $species, int $level): string
    {
        $species = preg_replace('/[^a-z0-9_\\-]/i', '', strtolower($species));
        $level = max(1, min(4, $level));
        return '/assets/game/tree/' . $species . '-lv' . $level . '.png';
    }

    protected function resolveTreeSpeciesFromMeta(array $meta): string
    {
        $species = !empty($meta['tree_type']) ? (string)$meta['tree_type'] : 'oak';
        if ($species !== 'mixed') {
            return $species;
        }
        $pool = array('oak', 'pine', 'sakura', 'maple');
        return $pool[array_rand($pool)];
    }

    protected function resolvePetImage(string $species, int $level): string
    {
        $species = preg_replace('/[^a-z0-9_\\-]/i', '', strtolower($species));
        $level = max(1, min(4, $level));
        return '/assets/game/pet/' . $species . '-lv' . $level . '.png';
    }

    protected function resolveFishImage(string $species, int $level): string
    {
        $species = preg_replace('/[^a-z0-9_\\-]/i', '', strtolower($species));
        $level = max(1, min(4, $level));
        return '/assets/game/fish/' . $species . '-lv' . $level . '.png';
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
