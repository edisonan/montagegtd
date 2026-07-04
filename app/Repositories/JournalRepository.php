<?php

namespace App\Repositories;

use App\Models\Journal;
use DB;

class JournalRepository {
	/**
	 * 获取记事列表
	 *
	 * @return Collection
	 */
	public function getUserList($userId) {
		return Journal::where ( 'user_id', $userId )->orderBy ( 'updated_at', 'desc' )->paginate ( 50 );
	}
	
	/**
	 * 获取记事列表(支持分页参数)
	 *
	 * @param array $filters
	 * @param int $pageSize
	 * @return Collection
	 */
	public function getJournalListWithPagination($filters, $pageSize = 10) {
		$journal = $this->buildJournalListQuery($filters);
        return $journal->paginate($pageSize);
	}

	/**
	 * 获取筛选后的手账统计。
	 *
	 * @param array $filters
	 * @return array
	 */
	public function getJournalListSummary($filters = []) {
		$journal = $this->buildJournalListQuery($filters);
		$todayStart = date('Y-m-d 00:00:00');
		$durationJournal = (clone $journal)
			->whereNotNull('end_time')
			->whereRaw('end_time > start_time');

		return array(
			'total' => (int)(clone $journal)->count(),
			'today' => (int)(clone $journal)->where('start_time', '>=', $todayStart)->count(),
			'duration_minutes' => (int)$durationJournal->select(DB::raw('COALESCE(SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)), 0) as aggregate'))->value('aggregate'),
		);
	}

	/**
	 * 构造手账列表筛选查询。
	 *
	 * @param array $filters
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function buildJournalListQuery($filters = []) {
		$journal = Journal::orderBy('updated_at', 'desc');

		if (isset($filters["user_id"])) {
			$journal = $journal->where("user_id", $filters['user_id']);
		}
		if (!empty($filters['keyword'])) {
			$keyword = '%' . $filters['keyword'] . '%';
			$journal = $journal->where('name', 'like', $keyword);
		}
		if (!empty($filters['start_date'])) {
			$journal = $journal->where('start_time', '>=', $filters['start_date'] . ' 00:00:00');
		}
		if (!empty($filters['end_date'])) {
			$journal = $journal->where('start_time', '<=', $filters['end_date'] . ' 23:59:59');
		}
		if (!empty($filters['type'])) {
			$journal = $journal->where('type', (int)$filters['type']);
		}

		return $journal;
	}
	
	/**
	 * 为总结获取记事列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getListForSummary($userId, $startTime, $endTime) {
		return Journal::where ( 'user_id', $userId )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->orderBy ( 'id', 'desc' )->get ();
	}

    /**
     * 通过id获取手账信息
     *
     * @param int $id
     * @return unknown
     */
    public function getJournalById($id) {
        return Journal::where ( 'id', $id )->first ();
    }
}
