<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointMallGameplaySeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $poolCode = 'lottery_default_pool';
        $pool = DB::table('point_lottery_pools')->where('code', $poolCode)->first();
        if (!$pool) {
            $poolId = DB::table('point_lottery_pools')->insertGetId(array(
                'code' => $poolCode,
                'name' => '好运抽奖池',
                'scene' => 'lottery',
                'cost_ap' => 20,
                'status' => 1,
                'description' => '20 AP 抽一次，概率获得 AP 回馈或权益奖励',
                'created_at' => $now,
                'updated_at' => $now,
            ));
        } else {
            $poolId = (int)$pool->id;
            DB::table('point_lottery_pools')->where('id', $poolId)->update(array(
                'name' => '好运抽奖池',
                'scene' => 'lottery',
                'cost_ap' => 20,
                'status' => 1,
                'description' => '20 AP 抽一次，概率获得 AP 回馈或权益奖励',
                'updated_at' => $now,
            ));
        }

        $items = array(
            array(
                'reward_name' => '10 AP返还',
                'reward_type' => 'ap',
                'reward_payload' => json_encode(array('ap' => 10), JSON_UNESCAPED_UNICODE),
                'weight' => 40,
                'stock' => -1,
            ),
            array(
                'reward_name' => '50 AP返还',
                'reward_type' => 'ap',
                'reward_payload' => json_encode(array('ap' => 50), JSON_UNESCAPED_UNICODE),
                'weight' => 10,
                'stock' => -1,
            ),
            array(
                'reward_name' => '抽奖券*1',
                'reward_type' => 'entitlement',
                'reward_payload' => json_encode(array('entitlement_type' => 'lottery_chance', 'quantity' => 1), JSON_UNESCAPED_UNICODE),
                'weight' => 25,
                'stock' => -1,
            ),
            array(
                'reward_name' => '树苗券*1',
                'reward_type' => 'entitlement',
                'reward_payload' => json_encode(array('entitlement_type' => 'tree_seedling', 'quantity' => 1, 'tree_type' => 'oak'), JSON_UNESCAPED_UNICODE),
                'weight' => 15,
                'stock' => -1,
            ),
            array(
                'reward_name' => '公交券*1',
                'reward_type' => 'entitlement',
                'reward_payload' => json_encode(array('entitlement_type' => 'bus_ticket', 'quantity' => 1), JSON_UNESCAPED_UNICODE),
                'weight' => 10,
                'stock' => -1,
            ),
        );

        foreach ($items as $item) {
            $exists = DB::table('point_lottery_pool_items')
                ->where('pool_id', $poolId)
                ->where('reward_name', $item['reward_name'])
                ->first();
            if ($exists) {
                DB::table('point_lottery_pool_items')->where('id', $exists->id)->update(array(
                    'reward_type' => $item['reward_type'],
                    'reward_payload' => $item['reward_payload'],
                    'weight' => $item['weight'],
                    'stock' => $item['stock'],
                    'status' => 1,
                    'updated_at' => $now,
                ));
            } else {
                DB::table('point_lottery_pool_items')->insert(array(
                    'pool_id' => $poolId,
                    'reward_type' => $item['reward_type'],
                    'reward_name' => $item['reward_name'],
                    'reward_payload' => $item['reward_payload'],
                    'weight' => $item['weight'],
                    'stock' => $item['stock'],
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ));
            }
        }

        $lines = array(
            array(
                'code' => 'bus_line_lake',
                'name' => '湖畔线',
                'color' => '#16a34a',
                'price_ap' => 80,
                'path_payload' => json_encode(array(
                    array('lng' => 116.397428, 'lat' => 39.90923),
                    array('lng' => 116.405285, 'lat' => 39.914935),
                    array('lng' => 116.418261, 'lat' => 39.921984),
                    array('lng' => 116.431255, 'lat' => 39.92768),
                ), JSON_UNESCAPED_UNICODE),
            ),
            array(
                'code' => 'bus_line_city',
                'name' => '城际线',
                'color' => '#0ea5e9',
                'price_ap' => 120,
                'path_payload' => json_encode(array(
                    array('lng' => 121.4737, 'lat' => 31.2304),
                    array('lng' => 121.4823, 'lat' => 31.2387),
                    array('lng' => 121.4951, 'lat' => 31.2479),
                    array('lng' => 121.5092, 'lat' => 31.2556),
                ), JSON_UNESCAPED_UNICODE),
            ),
        );

        foreach ($lines as $line) {
            $exists = DB::table('point_bus_lines')->where('code', $line['code'])->first();
            if ($exists) {
                DB::table('point_bus_lines')->where('id', $exists->id)->update(array(
                    'name' => $line['name'],
                    'color' => $line['color'],
                    'price_ap' => $line['price_ap'],
                    'path_payload' => $line['path_payload'],
                    'status' => 1,
                    'updated_at' => $now,
                ));
            } else {
                DB::table('point_bus_lines')->insert(array(
                    'code' => $line['code'],
                    'name' => $line['name'],
                    'color' => $line['color'],
                    'price_ap' => $line['price_ap'],
                    'path_payload' => $line['path_payload'],
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ));
            }
        }
    }
}

