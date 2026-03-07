<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\PointMallService;
use Illuminate\Http\Request;

class PointMallController extends Controller
{
    protected $pointMallService;

    public function __construct(PointMallService $pointMallService)
    {
        $this->pointMallService = $pointMallService;
    }

    public function entrances(Request $request)
    {
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'entrances' => $this->pointMallService->getEntranceItems(),
        )));
    }

    public function goods(Request $request)
    {
        $scene = (string)$request->input('scene', '');
        $goods = $this->pointMallService->getGoods($scene);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'scene' => $scene,
            'goods' => $goods,
        )));
    }

    public function orders(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $pageSize = (int)$request->input('page_count', 20);
        if ($pageSize <= 0) {
            $pageSize = 20;
        }

        $orders = $this->pointMallService->getOrders($userId, $pageSize);
        $items = array();
        foreach ($orders->items() as $order) {
            $items[] = $this->pointMallService->formatOrder($order);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'orders' => $items,
            'pagination' => array(
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'next_page_url' => $orders->nextPageUrl(),
                'prev_page_url' => $orders->previousPageUrl(),
                'has_more_pages' => $orders->hasMorePages(),
            ),
        )));
    }

    public function purchase(Request $request)
    {
        $this->validate($request, array(
            'goods_id' => 'required|integer|min:1',
            'quantity' => 'nullable|integer|min:1|max:20',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $goodsId = (int)$request->input('goods_id');
        $quantity = (int)$request->input('quantity', 1);

        try {
            $order = $this->pointMallService->purchase($userId, $goodsId, $quantity);
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
                'order' => $this->pointMallService->formatOrder($order),
            )));
        } catch (\Throwable $e) {
            return $this->jsonResponse($request, ResponseDataUtil::genCommonFail($e->getMessage()));
        }
    }
}

