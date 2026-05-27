<?php

namespace App\Services;

use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Support\Facades\Auth;

/**
 * 目标管理业务逻辑
 *
 * @author edison.an
 *        
 */
class PlanService {
	
	/**
	 * PlanRepository 实例.
	 *
	 * @var PlanRepository
	 */
	protected $planRepository;
	
	/**
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct(PlanRepository $planRepository) {
		$this->planRepository = $planRepository;
	}
	
	/**
	 *
	 * @param int $status
	 *        	(1,2)
	 * @return array
	 */
	public function getList($status) {
		$plans = $this->planRepository->getUserListBystatus ( Auth::id (), $status );
		return $plans;
	}
	
	/**
	 *
	 * @param string $name        	
	 */
	public function store($name) {
		$plan = new Plan ();
		$plan->user_id = \Auth::id ();
		$plan->name = $name;
		$plan->save ();
	}
}
