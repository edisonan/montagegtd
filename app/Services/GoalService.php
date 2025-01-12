<?php

namespace App\Services;

use App\Models\Goal;
use App\Repositories\GoalRepository;
use Illuminate\Support\Facades\Auth;

/**
 * 目标管理业务逻辑
 *
 * @author edison.an
 *        
 */
class GoalService {
	
	/**
	 * GoalRepository 实例.
	 *
	 * @var GoalRepository
	 */
	protected $goalRepository;
	
	/**
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct(GoalRepository $goalRepository) {
		$this->goalRepository = $goalRepository;
	}
	
	/**
	 *
	 * @param int $status
	 *        	(1,2)
	 * @return array
	 */
	public function getList($status) {
		$goals = $this->goalRepository->getUserListBystatus ( Auth::id (), $status );
		return $goals;
	}
	
	/**
	 *
	 * @param string $name        	
	 */
	public function store($name) {
		$goal = new Goal ();
		$goal->user_id = \Auth::id ();
		$goal->name = $name;
		$goal->save ();
	}
}
