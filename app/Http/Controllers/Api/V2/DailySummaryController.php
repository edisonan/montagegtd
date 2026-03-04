<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\DailySummary;
use App\Services\DailySummaryService;
use App\Services\PointGrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DailySummaryController extends Controller
{
    protected $dailySummaryService;
    protected $pointGrantService;

    public function __construct(DailySummaryService $dailySummaryService, PointGrantService $pointGrantService)
    {
        $this->dailySummaryService = $dailySummaryService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $dailySummarys = $this->dailySummaryService->getList();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'daily_summarys' => $dailySummarys->items(),
            'pagination' => array(
                'current_page' => $dailySummarys->currentPage(),
                'per_page' => $dailySummarys->perPage(),
                'next_page_url' => $dailySummarys->nextPageUrl(),
                'prev_page_url' => $dailySummarys->previousPageUrl(),
                'has_more_pages' => $dailySummarys->hasMorePages(),
            ),
        )));
    }

    public function show(Request $request, DailySummary $dailySummary)
    {
        $this->authorize('destroy', $dailySummary);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($dailySummary));
    }

    public function getBySummaryDate(Request $request)
    {
        $this->validate($request, array(
            'summary_date' => 'required|date_format:Y-m-d',
        ));

        $summaryDate = $request->input('summary_date');
        $dailySummary = $this->dailySummaryService->getBySummaryDate($summaryDate);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'summary_date' => $summaryDate,
            'daily_summary' => $dailySummary,
        )));
    }

    public function getTipInfos(Request $request)
    {
        $this->validate($request, array(
            'summary_date' => 'required|date_format:Y-m-d',
        ));

        $infos = $this->dailySummaryService->getTipInfos($request->input('summary_date'));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'infos' => $infos,
        )));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'summary_date' => 'required|date_format:Y-m-d',
        ));

        $userId = $this->getAuthUserId($request);
        $dailySummary = new DailySummary();
        $dailySummary->user_id = $userId;
        $dailySummary->summary_date = $request->input('summary_date');
        $dailySummary->work_content = $request->input('work_content', '');
        $dailySummary->life_content = $request->input('life_content', '');
        $dailySummary->save();

        try {
            $this->pointGrantService->grantByEvent(
                (int)$userId,
                'daily_summary_created',
                'daily_summary',
                (int)$dailySummary->id
            );
        } catch (\Throwable $e) {
            Log::warning('grant points on daily summary creation failed', array(
                'daily_summary_id' => $dailySummary->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($dailySummary));
    }

    public function update(Request $request, DailySummary $dailySummary)
    {
        $this->authorize('destroy', $dailySummary);

        $dailySummary->update(array(
            'work_content' => $request->input('work_content', $dailySummary->work_content),
            'life_content' => $request->input('life_content', $dailySummary->life_content),
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($dailySummary->fresh()));
    }

    public function destroy(Request $request, DailySummary $dailySummary)
    {
        $this->authorize('destroy', $dailySummary);
        $dailySummary->update(array('status' => 2));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}
