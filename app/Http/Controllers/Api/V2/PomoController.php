<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Pomo;
use App\Services\PointGrantService;
use App\Services\PomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PomoController extends Controller
{
    protected $pomoService;
    protected $pointGrantService;

    public function __construct(PomoService $pomoService, PointGrantService $pointGrantService)
    {
        $this->pomoService = $pomoService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $pageSize = (int)$request->input('page_count', 20);
        if ($pageSize <= 0) {
            $pageSize = 20;
        }

        $pomos = $this->pomoService->getPomoListWithPagination($pageSize);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'pomos' => $pomos->items(),
            'pagination' => array(
                'current_page' => $pomos->currentPage(),
                'per_page' => $pomos->perPage(),
                'total' => null,
                'last_page' => null,
                'next_page_url' => $pomos->nextPageUrl(),
                'prev_page_url' => $pomos->previousPageUrl(),
                'has_more_pages' => $pomos->hasMorePages(),
            ),
        )));
    }

    public function tabCounts(Request $request)
    {
        $counts = $this->pomoService->getDoneCounts(Auth::id());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'total' => (int)($counts['total'] ?? 0),
            'today' => (int)($counts['today'] ?? 0),
        )));
    }

    public function today(Request $request)
    {
        $pomos = $this->pomoService->getTodayList();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($pomos));
    }

    public function pomostatus(Request $request)
    {
        $currentPomoInfo = $this->pomoService->getRecentFormatPomo();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($currentPomoInfo));
    }

    public function start(Request $request)
    {
        $pomoInfo = $this->pomoService->startPomo();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($pomoInfo));
    }

    public function discard(Request $request, Pomo $pomo)
    {
        $this->authorize('destroy', $pomo);

        $updateParams = array();
        if ($pomo->status == 1) {
            $updateParams['status'] = 3;
        } else {
            $updateParams['rest_status'] = 3;
        }
        $pomo->update($updateParams);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function discardCurrent(Request $request)
    {
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function store(Request $request, Pomo $pomo)
    {
        $this->validate($request, array(
            'name' => 'required|max:255',
        ));

        $this->authorize('destroy', $pomo);

        if (time() < strtotime($pomo->end_time)) {
            throw new CustomException('还未到番茄完成时间');
        }

        if ($pomo->status == 1) {
            $currentPomoInfo = $this->pomoService->store($pomo, $request->input('name'));
            try {
                $this->pointGrantService->grantByEvent(
                    (int)$pomo->user_id,
                    'pomo_completed',
                    'pomo',
                    (int)$pomo->id
                );
            } catch (\Throwable $e) {
                Log::warning('grant points on pomo completion failed', array(
                    'pomo_id' => $pomo->id,
                    'user_id' => $pomo->user_id,
                    'error' => $e->getMessage(),
                ));
            }
        } else {
            $currentPomoInfo = $this->pomoService->getRecentFormatPomo();
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($currentPomoInfo));
    }

    public function update(Request $request, Pomo $pomo)
    {
        $this->validate($request, array(
            'name' => 'required|max:255',
        ));

        $this->authorize('destroy', $pomo);
        $pomo->update(array('name' => $request->input('name')));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($pomo->fresh()));
    }

    public function destroy(Request $request, Pomo $pomo)
    {
        $this->authorize('destroy', $pomo);
        $pomo->delete();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}
