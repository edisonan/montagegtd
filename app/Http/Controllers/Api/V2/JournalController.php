<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Journal;
use App\Services\PointGrantService;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JournalController extends Controller
{
    protected $journalService;
    protected $pointGrantService;

    public function __construct(JournalService $journalService, PointGrantService $pointGrantService)
    {
        $this->journalService = $journalService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $this->validate($request, array(
            'page_size' => 'nullable|integer|min:1|max:100',
            'keyword' => 'nullable|string|max:100',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'type' => 'nullable|integer|min:1|max:5',
        ));

        $pageSize = (int)$request->input('page_size', 10);
        if ($pageSize <= 0) {
            $pageSize = 10;
        }

        $filters = array_filter(array(
            'keyword' => trim((string)$request->input('keyword', '')),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'type' => $request->input('type'),
        ), function ($value) {
            return $value !== null && $value !== '';
        });

        $journals = $this->journalService->getList($pageSize, $filters);
        $summary = $this->journalService->getListSummary($filters);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'journals' => $journals->items(),
            'pagination' => array(
                'current_page' => $journals->currentPage(),
                'per_page' => $journals->perPage(),
                'total' => $journals->total(),
                'last_page' => $journals->lastPage(),
                'next_page_url' => $journals->nextPageUrl(),
                'prev_page_url' => $journals->previousPageUrl(),
                'has_more_pages' => $journals->hasMorePages(),
            ),
            'summary' => $summary,
        )));
    }

    public function show(Request $request, Journal $journal)
    {
        $this->authorize('destroy', $journal);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($journal));
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

        $journal = new Journal();
        $journal->user_id = $userId;
        $journal->name = $request->input('name');
        $journal->type = (int)$request->input('type', 1);
        $journal->start_time = $startTime;
        if (!empty($endTime)) {
            $journal->end_time = $endTime;
        }
        $journal->save();
        try {
            $this->pointGrantService->grantByEvent(
                (int)$journal->user_id,
                'journal_created',
                'journal',
                (int)$journal->id
            );
        } catch (\Throwable $e) {
            Log::warning('grant points on journal store failed', array(
                'journal_id' => $journal->id,
                'user_id' => $journal->user_id,
                'error' => $e->getMessage(),
            ));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($journal));
    }

    public function update(Request $request, Journal $journal)
    {
        $this->authorize('destroy', $journal);
        $journal->update($request->all());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($journal->fresh()));
    }

    public function destroy(Request $request, Journal $journal)
    {
        $this->authorize('destroy', $journal);
        $journal->delete();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}
