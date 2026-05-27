<?php

namespace App\Services;

use App\Models\PointAccount;
use App\Models\PointMallDeliveryLog;
use App\Models\PointMallEntitlement;
use App\Models\PointMallGood;
use App\Models\PointMallOrder;
use Illuminate\Support\Facades\DB;

class PointMallService
{
    protected $pointRecordService;

    public function __construct(PointRecordService $pointRecordService)
    {
        $this->pointRecordService = $pointRecordService;
    }

    public function getEntranceItems(): array
    {
        return array(
            array(
                'scene' => 'lottery',
                'title' => '积分抽奖',
                'subtitle' => '用积分抽取奖励机会',
                'icon' => 'fas fa-dice',
                'color' => 'from-amber-500 to-orange-600',
            ),
            array(
                'scene' => 'bus',
                'title' => '积分公交车',
                'subtitle' => '兑换车票与出行权益',
                'icon' => 'fas fa-bus',
                'color' => 'from-sky-500 to-blue-600',
            ),
            array(
                'scene' => 'tree',
                'title' => '积分种树',
                'subtitle' => '兑换树苗，累计绿色行动',
                'icon' => 'fas fa-tree',
                'color' => 'from-emerald-500 to-green-600',
            ),
            array(
                'scene' => 'pet',
                'title' => '宠物乐园',
                'subtitle' => '兑换宠物并持续喂养成长',
                'icon' => 'fas fa-paw',
                'color' => 'from-rose-500 to-pink-600',
            ),
            array(
                'scene' => 'pond',
                'title' => '池塘乐园',
                'subtitle' => '兑换鱼苗并投喂升级',
                'icon' => 'fas fa-fish',
                'color' => 'from-cyan-500 to-sky-600',
            ),
        );
    }

    public function getGoods(string $scene = ''): array
    {
        $query = PointMallGood::query()
            ->where('status', 1)
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($scene)) {
            $query->where('scene', $scene);
        }

        $goods = $query->get();
        $result = array();
        foreach ($goods as $item) {
            $result[] = $this->formatGood($item);
        }

        return $result;
    }

    public function getOrders(int $userId, int $pageSize = 20)
    {
        return PointMallOrder::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->simplePaginate($pageSize);
    }

    public function purchase(int $userId, int $goodsId, int $quantity = 1): PointMallOrder
    {
        if ($quantity <= 0 || $quantity > 20) {
            throw new \RuntimeException('购买数量不合法');
        }

        return DB::transaction(function () use ($userId, $goodsId, $quantity) {
            $account = PointAccount::where('user_id', $userId)->lockForUpdate()->first();
            if (!$account) {
                $account = PointAccount::create(array('user_id' => $userId));
            }

            $good = PointMallGood::where('id', $goodsId)->lockForUpdate()->first();
            if (!$good || (int)$good->status !== 1) {
                throw new \RuntimeException('商品不存在或已下架');
            }

            $stock = (int)$good->stock;
            if ($stock >= 0 && $stock < $quantity) {
                throw new \RuntimeException('库存不足');
            }

            $costEach = (int)$good->point_cost;
            $costTotal = $costEach * $quantity;
            if ($costTotal <= 0) {
                throw new \RuntimeException('商品积分配置错误');
            }

            if ((int)$account->ap_balance < $costTotal) {
                throw new \RuntimeException('可用积分不足');
            }

            $account->ap_balance = (int)$account->ap_balance - $costTotal;
            $account->save();

            if ($stock >= 0) {
                $good->stock = $stock - $quantity;
                $good->save();
            }

            $snapshot = array(
                'id' => (int)$good->id,
                'code' => (string)$good->code,
                'name' => (string)$good->name,
                'scene' => (string)$good->scene,
                'delivery_type' => (string)$good->delivery_type,
                'point_cost' => (int)$good->point_cost,
            );

            $order = PointMallOrder::create(array(
                'order_no' => $this->generateOrderNo($userId),
                'user_id' => $userId,
                'goods_id' => (int)$good->id,
                'goods_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'quantity' => $quantity,
                'point_cost_each' => $costEach,
                'point_cost_total' => $costTotal,
                'status' => 'paid',
                'delivery_status' => 'pending',
                'delivery_type' => (string)$good->delivery_type,
                'paid_at' => date('Y-m-d H:i:s'),
            ));

            $this->pointRecordService->record(
                $userId,
                'AP',
                -$costTotal,
                (int)$account->ap_balance,
                'point_mall_order',
                (int)$order->id,
                '积分商城兑换：' . $good->name
            );

            $this->fulfillOrder($order, $good, $quantity);

            return $order->fresh();
        });
    }

    public function formatOrder(PointMallOrder $order): array
    {
        return array(
            'id' => (int)$order->id,
            'order_no' => (string)$order->order_no,
            'goods_id' => (int)$order->goods_id,
            'goods_snapshot' => $this->decodeJson($order->goods_snapshot),
            'quantity' => (int)$order->quantity,
            'point_cost_each' => (int)$order->point_cost_each,
            'point_cost_total' => (int)$order->point_cost_total,
            'status' => (string)$order->status,
            'delivery_status' => (string)$order->delivery_status,
            'delivery_type' => (string)$order->delivery_type,
            'delivery_message' => (string)$order->delivery_message,
            'delivery_payload' => $this->decodeJson($order->delivery_payload),
            'paid_at' => $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : null,
            'fulfilled_at' => $order->fulfilled_at ? $order->fulfilled_at->format('Y-m-d H:i:s') : null,
            'created_at' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null,
        );
    }

    protected function fulfillOrder(PointMallOrder $order, PointMallGood $good, int $quantity): void
    {
        $deliveryType = (string)$good->delivery_type;
        $goodsPayload = $this->decodeJson($good->payload);
        $grantEach = max(1, (int)($goodsPayload['quantity'] ?? 1));
        $grantQty = $grantEach * $quantity;

        if ($deliveryType === 'lottery') {
            $this->createEntitlement($order, 'lottery_chance', $grantQty, 'active', array(
                'scene' => 'lottery',
                'note' => '已发放抽奖次数',
                'goods_payload' => $goodsPayload,
            ));
            $this->markOrderFulfilled($order, '抽奖次数已到账');
            $this->writeDeliveryLog($order->id, 'lottery_handler', 'success', 'lottery chance granted');
            return;
        }

        if ($deliveryType === 'bus') {
            $this->createEntitlement($order, 'bus_ticket', $grantQty, 'active', array(
                'scene' => 'bus',
                'note' => '已发放公交权益',
                'goods_payload' => $goodsPayload,
            ));
            $this->markOrderFulfilled($order, '公交权益已到账');
            $this->writeDeliveryLog($order->id, 'bus_handler', 'success', 'bus ticket granted');
            return;
        }

        if ($deliveryType === 'tree') {
            $this->createEntitlement($order, 'tree_seedling', $grantQty, 'pending_planting', array(
                'scene' => 'tree',
                'note' => '待种植确认',
                'tree_type' => (string)($goodsPayload['tree_type'] ?? 'oak'),
                'goods_payload' => $goodsPayload,
            ));
            $order->delivery_status = 'processing';
            $order->delivery_message = '树苗已到账，待你手动种植';
            $order->fulfilled_at = date('Y-m-d H:i:s');
            $order->save();
            $this->writeDeliveryLog($order->id, 'tree_handler', 'success', 'tree seedlings granted');
            return;
        }

        if ($deliveryType === 'pet') {
            $this->createEntitlement($order, 'pet_companion', $grantQty, 'pending_adoption', array(
                'scene' => 'pet',
                'note' => '待领养',
                'pet_type' => (string)($goodsPayload['pet_type'] ?? 'cat'),
                'goods_payload' => $goodsPayload,
            ));
            $order->delivery_status = 'processing';
            $order->delivery_message = '宠物已到账，待你手动领养';
            $order->fulfilled_at = date('Y-m-d H:i:s');
            $order->save();
            $this->writeDeliveryLog($order->id, 'pet_handler', 'success', 'pet entitlement granted');
            return;
        }

        if ($deliveryType === 'pond') {
            $this->createEntitlement($order, 'fish_fry', $grantQty, 'pending_release', array(
                'scene' => 'pond',
                'note' => '待放养',
                'fish_type' => (string)($goodsPayload['fish_type'] ?? 'goldfish'),
                'goods_payload' => $goodsPayload,
            ));
            $order->delivery_status = 'processing';
            $order->delivery_message = '鱼苗已到账，待你手动放养';
            $order->fulfilled_at = date('Y-m-d H:i:s');
            $order->save();
            $this->writeDeliveryLog($order->id, 'pond_handler', 'success', 'fish entitlement granted');
            return;
        }

        $order->delivery_status = 'processing';
        $order->delivery_message = '订单已创建，等待人工发货';
        $order->save();
        $this->writeDeliveryLog($order->id, 'manual_handler', 'processing', 'manual fulfillment required');
    }

    protected function createEntitlement(
        PointMallOrder $order,
        string $type,
        int $quantity,
        string $status,
        array $meta = array()
    ): void {
        PointMallEntitlement::create(array(
            'order_id' => (int)$order->id,
            'user_id' => (int)$order->user_id,
            'entitlement_type' => $type,
            'quantity' => $quantity,
            'status' => $status,
            'meta_payload' => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ));
    }

    protected function markOrderFulfilled(PointMallOrder $order, string $message): void
    {
        $order->delivery_status = 'fulfilled';
        $order->delivery_message = $message;
        $order->fulfilled_at = date('Y-m-d H:i:s');
        $order->save();
    }

    protected function writeDeliveryLog(int $orderId, string $handler, string $status, string $message): void
    {
        PointMallDeliveryLog::create(array(
            'order_id' => $orderId,
            'handler' => $handler,
            'status' => $status,
            'message' => $message,
        ));
    }

    protected function formatGood(PointMallGood $item): array
    {
        return array(
            'id' => (int)$item->id,
            'code' => (string)$item->code,
            'name' => (string)$item->name,
            'scene' => (string)$item->scene,
            'delivery_type' => (string)$item->delivery_type,
            'image_url' => (string)$item->image_url,
            'description' => (string)$item->description,
            'point_cost' => (int)$item->point_cost,
            'stock' => (int)$item->stock,
            'status' => (int)$item->status,
            'sort' => (int)$item->sort,
            'payload' => $this->decodeJson($item->payload),
        );
    }

    protected function decodeJson($content)
    {
        if (empty($content)) {
            return array();
        }
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : array();
    }

    protected function generateOrderNo(int $userId): string
    {
        return 'PM' . date('YmdHis') . str_pad((string)($userId % 1000), 3, '0', STR_PAD_LEFT) . mt_rand(100, 999);
    }
}
