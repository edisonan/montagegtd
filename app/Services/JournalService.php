<?php

namespace App\Services;

use App\Models\User;
use App\Models\Journal;
use App\Repositories\JournalRepository;
use Auth;

/**
 * 记事管理业务逻辑
 *
 * @author edison.an
 *        
 */
class JournalService {
	/**
	 *
	 * @var JournalRepository
	 */
	protected $journalRepository;
	
	/**
	 *
	 * @param
	 *        	JournalRepository JournalRepository
	 */
	public function __construct(JournalRepository $journalRepository) {
		$this->journalRepository = $journalRepository;
	}
	
	/**
	 * 获取记事列表
	 *
	 * @param int $pageSize
	 * @return Collection
	 */
	public function getList($pageSize = 10, $extraFilters = []) {
        $filters = [
            "user_id" => Auth::id()
        ];
		$filters = array_merge($filters, $extraFilters);
		return $this->journalRepository->getJournalListWithPagination($filters, $pageSize);
	}

	/**
	 * 获取手账列表统计。
	 *
	 * @param array $extraFilters
	 * @return array
	 */
	public function getListSummary($extraFilters = []) {
		$filters = array_merge([
			"user_id" => Auth::id()
		], $extraFilters);

		return $this->journalRepository->getJournalListSummary($filters);
	}
	
	/**
	 * 保存记事
	 * 
	 * @param unknown $type        	
	 * @param unknown $name        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 * @return \App\Models\Journal
	 */
	public function storeJournal($type, $name, $startTime, $endTime) {
		$journal = new Journal ();
		$journal->user_id = \Auth::id ();
		$journal->type = $type;
		$journal->name = $name;
		$journal->start_time = $startTime;
		$journal->end_time = $endTime;
		$journal->save ();
		return $journal;
	}
}
