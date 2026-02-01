<?php

namespace App\Services;

use App\Models\User;
use App\Models\Thing;
use App\Repositories\ThingRepository;
use Auth;

/**
 * 记事管理业务逻辑
 *
 * @author edison.an
 *        
 */
class ThingService {
	/**
	 *
	 * @var ThingRepository
	 */
	protected $thingRepository;
	
	/**
	 *
	 * @param
	 *        	ThingRepository ThingRepository
	 */
	public function __construct(ThingRepository $thingRepository) {
		$this->thingRepository = $thingRepository;
	}
	
	/**
	 * 获取记事列表
	 *
	 * @param int $pageSize
	 * @return Collection
	 */
	public function getList($pageSize = 10) {
        $filters = [
            "user_id" => Auth::id()
        ];
		return $this->thingRepository->getThingListWithPagination($filters, $pageSize);
	}
	
	/**
	 * 保存记事
	 * 
	 * @param unknown $type        	
	 * @param unknown $name        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 * @return \App\Models\Thing
	 */
	public function storeThing($type, $name, $startTime, $endTime) {
		$thing = new Thing ();
		$thing->user_id = \Auth::id ();
		$thing->type = $type;
		$thing->name = $name;
		$thing->start_time = $startTime;
		$thing->end_time = $endTime;
		$thing->save ();
		return $thing;
	}
}
