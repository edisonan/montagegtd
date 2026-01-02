<?php

namespace App\Services;

use App\Models\Pomo;
use App\Models\User;
use App\Http\Utils\CommonUtil;
use App\Exceptions\CustomException;
use App\Repositories\PomoRepository;
use Auth;

/**
 * 番茄工作法业务逻辑
 *
 * @author edison.an
 *        
 */
class PomoService {
	
	/**
	 * The thing servie instance.
	 *
	 * @var ThingService
	 */
	protected $thingService;
	
	/**
	 * The pomo repository instance.
	 *
	 * @var PomoRepository
	 */
	protected $pomoRepository;
	
	/**
	 *
	 * @param ThingService $thingService        	
	 * @param PomoRepository $pomoRepository        	
	 */
	public function __construct(ThingService $thingService, PomoRepository $pomoRepository) {
		$this->thingService = $thingService;
		$this->pomoRepository = $pomoRepository;
	}
	
	/**
	 * 获取番茄列表
	 * 
	 * @return \App\Repositories\unknown
	 */
	public function getList() {
		return $this->pomoRepository->getUserListByStatus ( Auth::id (), 2 );
	}
	
	/**
	 * 获取当日番茄列表
	 * 
	 * @return \App\Repositories\unknown
	 */
	public function getTodayList() {
		return $this->pomoRepository->getUserAllListByStatusAndEndTime ( Auth::id (), 2, date ( 'Ymd' ) );
	}
	
	/**
	 * 开始一个番茄
	 * 
	 * @throws CustomException
	 * @return \App\Services\unknown[]|\App\Services\number[]|\App\Models\Pomo[]
	 */
	public function startPomo() {
		$recentPomo = $this->getRecentFormatPomo ();
		
		if ($recentPomo ['current_pomo_status'] != Pomo::STATUS_INIT) {
			throw new CustomException ( "当前存在番茄状态错误，暂不能开启番茄" );
		}
		
		$user = \Auth::user ();
		
		// 获取当前用户设置
		$setting = $user->setting;
		
		$pomoIntervalTime = isset ( $setting->pomo_time ) && ! empty ( $setting->pomo_time ) ? $setting->pomo_time * 60 : Pomo::DEFAULT_INTERVAL;
		$currentTime = time ();
		$startTime = date ( 'Y-m-d H:i:s', $currentTime );
		$endTime = date ( 'Y-m-d H:i:s', time () + $pomoIntervalTime );
		
		$pomo = Pomo::create ( [ 
				'name' => '',
				'status' => 1,
				'user_id' => \Auth::id (),
				'start_time' => $startTime,
				'end_time' => $endTime,
				'rest_status' => 1 
		] );
		
		$currentPomoInfo = $this->formatPomo ( $pomo );
		
		return $currentPomoInfo;
	}
	
	/**
	 * 获取最近一个番茄信息
	 * 
	 * @return \App\Services\unknown[]|\App\Services\number[]|\App\Models\Pomo[]
	 */
	public function getRecentFormatPomo() {
		$pomo = $this->pomoRepository->getUserRecentPomo ( Auth::id () );
		return $this->formatPomo ( $pomo );
	}
	
	/**
	 *
	 * @param unknown $pomo        	
	 * @return unknown[]|number[]|\App\Models\Pomo[]
	 */
	public function formatPomo($pomo) {
		
		// 1默认等待中 2进行中 3已经完成 4休息中 5休息结束
		$formatPomoStatus = Pomo::STATUS_INIT;
		$formatPomoRemain = 0;
		
		$formatInfo = array (
				'active_pomo' => $pomo,
				'current_pomo_status' => $formatPomoStatus,
				'current_pomo_remain' => $formatPomoRemain 
		);
		
		// 如果没有最近番茄，则直接返回此信息
		if (empty ( $pomo )) {
			$formatInfo ['active_pomo'] = new Pomo ();
			return $formatInfo;
		}
		
		if ($pomo->status == 3 || $pomo->rest_status == 3 || $pomo->rest_status == 2) {
			return $formatInfo;
		}
		
		if ($pomo->status == 1) {
			$remain = strtotime ( $pomo->end_time ) - time ();
			if ($remain > 0) {
				$formatInfo ['current_pomo_remain'] = $remain;
				$formatInfo ['current_pomo_status'] = Pomo::STATUS_PROCESSING;
				return $formatInfo;
			} else {
				$formatInfo ['current_pomo_status'] = Pomo::STATUS_FINISHED;
				return $formatInfo;
			}
		} else if ($pomo->status == 2 && $pomo->rest_status == 1) {
			$restRemain = strtotime ( $pomo->rest_end_time ) - time ();
			if ($restRemain > 0) {
				$formatInfo ['current_pomo_remain'] = $restRemain;
				$formatInfo ['current_pomo_status'] = Pomo::STATUS_RESTING;
				return $formatInfo;
			} else {
				$pomo->update ( array (
						'rest_status' => 2 
				) );
			}
		}
		
		return $formatInfo;
	}
	
	/**
	 * 保存番茄信息
	 * 
	 * @param unknown $pomo        	
	 * @param unknown $name        	
	 * @return number[]|unknown[]|\App\Models\Pomo[]
	 */
	public function store($pomo, $name) {
		// 获取当前用户设置
		$user = \Auth::user ();
		$setting = $user->setting;
		
		$pomoIntervalTime = isset ( $setting->pomo_rest_time ) && ! empty ( $setting->pomo_rest_time ) ? $setting->pomo_rest_time * 60 : Pomo::DEFAULT_REST_INTERVAL;
		$currentTime = time ();
		$startTime = date ( 'Y-m-d H:i:s', $currentTime );
		$endTime = date ( 'Y-m-d H:i:s', time () + $pomoIntervalTime );
		
		$pomo->update ( [ 
				'name' => $name,
				'status' => 2,
				'rest_start_time' => $startTime,
				'rest_end_time' => $endTime 
		] );
		
		$this->thingService->storeThing ( 3, $pomo->name, $pomo->start_time, $pomo->end_time );
		
		return $this->getRecentFormatPomo ();
	}
	
	/**
	 * 依据番茄状态获取信息头提示
	 *
	 * @param unknown $pomo_status        	
	 * @return number[]|string[]
	 */
	public function getTipInfo($pomo_status) {
		$tipType = 0;
		$tipMessage = '';
		
		if ($pomo_status == Pomo::STATUS_FINISHED) {
			$tipType = 1;
			$tipMessage = '您已经完成了一个番茄，快来记录一下吧~';
		} else {
			$hour = date ( 'H' );
			if ($hour < 10 && $hour > 6 && ! isset ( $_COOKIE [date ( 'Ymd' ) . 'morning_tip'] )) {
				$tipType = 2;
				$tipMessage = '一日之计在于晨，写个<a href="' . url ( '/notes', array (
						'add_content',
						'#今日小目标#' 
				) ) . '">今日小目标</a>吧';
			} else if ($hour > 18 && $hour < 22 && ! isset ( $_COOKIE [date ( 'Ymd' ) . 'afternoon_tip'] )) {
				$tipType = 3;
				$tipMessage = '今天过得怎么样，写个<a href="' . url ( '/notes', array (
						'add_content',
						'#每日总结#' 
				) ) . '">每日总结</a>吧';
			}
		}
		
		return array (
				'tip_type' => $tipType,
				'tip_message' => $tipMessage 
		);
	}
	
	/**
	 * 定时任务-每日开始番茄提醒
	 */
	public function schedulePomoDailyReminder($type) {
		// 1 上午番茄提醒 2下午番茄提醒
		if ($type == 1) {
			$date = date ( 'Y-m-d 00:00:00' );
		} else if ($type == 2) {
			$date = date ( 'Y-m-d 12:00:00' );
		}
		
		$users = User::where ( 'last_login', '>', date ( 'Y-m-d H:i:s', strtotime ( $date ) - 3 * 24 * 60 * 60 ) )->get ();
		
		foreach ( $users as $user ) {
			if ($this->pomoRepository->existNewPomoByAfterStartTime ( $date, $user->id )) {
				continue;
			}
			// ifttt通知
			if (isset ( $user->setting->ifttt_notify )) {
				CommonUtil::iftttnotify ( '番茄提醒', '快来开始新的番茄吧~',  config('app.url'), $user->setting->ifttt_notify );
			}
		}
	}
	
	/**
	 * 定时任务-提醒记录番茄
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function schedulePomoRecordReminder($startTime, $endTime) {
		$pomos = $this->pomoRepository->getAllListByBetweenEndTime ( $startTime, $endTime );
		foreach ( $pomos as $pomo ) {
			// ifttt通知
			if (isset ( $pomo->user->setting->ifttt_notify )) {
				CommonUtil::iftttnotify ( '番茄提醒', '您已经完成了一个番茄，快来记录一下吧~',  config('app.url'), $pomo->user->setting->ifttt_notify );
			}
		}
	}
	
	/**
	 * 定时任务-休息后提醒开启新番茄
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function schedulePomoRestedReminder($startTime, $endTime) {
		$pomos = $this->pomoRepository->getAllListByRestBetweenEndTime ( $startTime, $endTime );
		foreach ( $pomos as $pomo ) {
			if ($this->pomoRepository->existNewPomoById ( $pomo->id, $pomo->user_id )) {
				continue;
			}
			if (isset ( $pomo->user->setting->ifttt_notify )) {
				CommonUtil::iftttnotify ( '番茄提醒', '休息完成，快来开始下一个番茄吧~',  config('app.url'), $pomo->user->setting->ifttt_notify );
			}
		}
	}
}

