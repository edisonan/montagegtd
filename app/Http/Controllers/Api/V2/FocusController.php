<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Focus;
use App\Services\PointGrantService;
use App\Services\FocusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FocusController extends Controller
{
    protected $focusService;
    protected $pointGrantService;

    public function __construct(FocusService $focusService, PointGrantService $pointGrantService)
    {
        $this->focusService = $focusService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $pageSize = (int)$request->input('page_count', 20);
        if ($pageSize <= 0) {
            $pageSize = 20;
        }

        $focus_datas = $this->focusService->getFocusListWithPagination($pageSize);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'focuss' => $focus_datas->items(),
            'pagination' => array(
                'current_page' => $focus_datas->currentPage(),
                'per_page' => $focus_datas->perPage(),
                'total' => null,
                'last_page' => null,
                'next_page_url' => $focus_datas->nextPageUrl(),
                'prev_page_url' => $focus_datas->previousPageUrl(),
                'has_more_pages' => $focus_datas->hasMorePages(),
            ),
        )));
    }

    public function tabCounts(Request $request)
    {
        $counts = $this->focusService->getDoneCounts(Auth::id());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'total' => (int)($counts['total'] ?? 0),
            'today' => (int)($counts['today'] ?? 0),
        )));
    }

    public function today(Request $request)
    {
        $focus_datas = $this->focusService->getTodayList();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($focus_datas));
    }

    public function focusstatus(Request $request)
    {
        $currentFocusInfo = $this->focusService->getRecentFormatFocus();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($currentFocusInfo));
    }

    public function start(Request $request)
    {
        $focusInfo = $this->focusService->startFocus();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($focusInfo));
    }

    public function discard(Request $request, Focus $focus)
    {
        $this->authorize('destroy', $focus);

        $updateParams = array();
        if ($focus->status == 1) {
            $updateParams['status'] = 3;
        } else {
            $updateParams['rest_status'] = 3;
        }
        $focus->update($updateParams);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function discardCurrent(Request $request)
    {
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function store(Request $request, Focus $focus)
    {
        $this->validate($request, array(
            'name' => 'required|max:255',
        ));

        $this->authorize('destroy', $focus);

        if (time() < strtotime($focus->end_time)) {
            throw new CustomException('还未到专注完成时间');
        }

        if ($focus->status == 1) {
            $currentFocusInfo = $this->focusService->store($focus, $request->input('name'));
            try {
                $this->pointGrantService->grantByEvent(
                    (int)$focus->user_id,
                    'focus_completed',
                    'focus',
                    (int)$focus->id
                );
            } catch (\Throwable $e) {
                Log::warning('grant points on focus completion failed', array(
                    'focus_id' => $focus->id,
                    'user_id' => $focus->user_id,
                    'error' => $e->getMessage(),
                ));
            }
        } else {
            $currentFocusInfo = $this->focusService->getRecentFormatFocus();
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($currentFocusInfo));
    }

    public function update(Request $request, Focus $focus)
    {
        $this->validate($request, array(
            'name' => 'nullable|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'review_note' => 'nullable|string|max:2000',
        ));

        $this->authorize('destroy', $focus);
        $payload = array();
        if ($request->has('name')) {
            $payload['name'] = $request->input('name');
        }
        if ($request->has('rating')) {
            $payload['rating'] = $request->input('rating');
        }
        if ($request->has('review_note')) {
            $payload['review_note'] = $request->input('review_note');
        }

        if (!empty($payload)) {
            $focus->update($payload);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($focus->fresh()));
    }

    public function destroy(Request $request, Focus $focus)
    {
        $this->authorize('destroy', $focus);
        $focus->delete();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}
