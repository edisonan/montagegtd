<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Thing;
use App\Services\PointGrantService;
use App\Services\ThingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThingController extends Controller
{
    protected $thingService;
    protected $pointGrantService;

    public function __construct(ThingService $thingService, PointGrantService $pointGrantService)
    {
        $this->thingService = $thingService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $pageSize = (int)$request->input('page_size', 10);
        if ($pageSize <= 0) {
            $pageSize = 10;
        }

        $things = $this->thingService->getList($pageSize);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'things' => $things->items(),
            'pagination' => array(
                'current_page' => $things->currentPage(),
                'per_page' => $things->perPage(),
                'total' => $things->total(),
                'last_page' => $things->lastPage(),
                'next_page_url' => $things->nextPageUrl(),
                'prev_page_url' => $things->previousPageUrl(),
                'has_more_pages' => $things->hasMorePages(),
            ),
        )));
    }

    public function show(Request $request, Thing $thing)
    {
        $this->authorize('destroy', $thing);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($thing));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required|max:255',
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s',
        ));

        $startTime = $request->input('start_time', date('Y-m-d H:i:s'));
        $endTime = $request->input('end_time');
        if (!empty($endTime) && strtotime($startTime) > strtotime($endTime)) {
            throw new CustomException('错误的结束时间');
        }

        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            throw new CustomException('用户未认证');
        }

        $thing = new Thing();
        $thing->user_id = $userId;
        $thing->name = $request->input('name');
        $thing->type = (int)$request->input('type', 1);
        $thing->start_time = $startTime;
        if (!empty($endTime)) {
            $thing->end_time = $endTime;
        }
        $thing->save();
        try {
            $this->pointGrantService->grantByEvent(
                (int)$thing->user_id,
                'thing_created',
                'thing',
                (int)$thing->id
            );
        } catch (\Throwable $e) {
            Log::warning('grant points on thing store failed', array(
                'thing_id' => $thing->id,
                'user_id' => $thing->user_id,
                'error' => $e->getMessage(),
            ));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($thing));
    }

    public function update(Request $request, Thing $thing)
    {
        $this->authorize('destroy', $thing);
        $thing->update($request->all());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($thing->fresh()));
    }

    public function destroy(Request $request, Thing $thing)
    {
        $this->authorize('destroy', $thing);
        $thing->delete();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}
