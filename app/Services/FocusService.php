<?php

namespace App\Services;

use App\Models\Focus;
use App\Models\User;
use App\Exceptions\CustomException;
use App\Repositories\FocusRepository;
use Auth;

/**
 * 专注工作法业务逻辑
 *
 * @author edison.an
 *        
 */
class FocusService {
	
	/**
	 * The journal servie instance.
	 *
	 * @var JournalService
	 */
	protected $journalService;
	
	/**
	 * The focus repository instance.
	 *
	 * @var FocusRepository
	 */
	protected $focusRepository;
	
	/**
	 *
	 * @param JournalService $journalService        	
	 * @param FocusRepository $focusRepository        	
	 */
	public function __construct(JournalService $journalService, FocusRepository $focusRepository) {
		$this->journalService = $journalService;
		$this->focusRepository = $focusRepository;
	}
	
	/**
	 * 获取专注列表
	 *
	 * @return \App\Repositories\unknown
	 */
	public function getFocusListWithPagination($pageSize, $extraFilters = array()) {
        $filters = array(
            "status" => 2,
            "user_id" => Auth::id (),
            "page_size" => $pageSize
        );
		$filters = array_merge($filters, $extraFilters);
		return $this->focusRepository->getFocusListWithPagination ( $filters , $pageSize);
	}

	/**
	 * 获取专注列表筛选统计。
	 *
	 * @param array $extraFilters
	 * @return array
	 */
	public function getFocusListSummary($extraFilters = array()) {
		$filters = array_merge(array(
			"status" => 2,
			"user_id" => Auth::id (),
		), $extraFilters);

		return $this->focusRepository->getFocusListSummary($filters);
	}

	/**
	 * 获取专注计数（总完成数 + 今日完成数）
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getDoneCounts($userId) {
		return $this->focusRepository->getUserDoneCounts($userId);
	}
	
	/**
	 * 获取当日专注列表
	 * 
	 * @return \App\Repositories\unknown
	 */
	public function getTodayList() {
		return $this->focusRepository->getUserAllListByStatusAndEndTime ( Auth::id (), 2, date ( 'Ymd' ) );
	}
	
	/**
	 * 开始一个专注
	 * 
	 * @throws CustomException
	 * @return \App\Services\unknown[]|\App\Services\number[]|\App\Models\Focus[]
	 */
	public function startFocus() {
		$recentFocus = $this->getRecentFormatFocus ();
		
		if ($recentFocus ['current_focus_status'] != Focus::STATUS_INIT) {
			throw new CustomException ( "当前存在专注状态错误，暂不能开启专注" );
		}
		
		$user = \Auth::user ();
		
		// 获取当前用户设置
		$setting = $user->setting;
		
		$pomoConfig = $setting->getPomoConfigValues ();
		$focusIntervalTime = ! empty ( $pomoConfig['focus_minutes'] ) ? $pomoConfig['focus_minutes'] * 60 : Focus::DEFAULT_INTERVAL;
		$currentTime = time ();
		$startTime = date ( 'Y-m-d H:i:s', $currentTime );
		$endTime = date ( 'Y-m-d H:i:s', time () + $focusIntervalTime );
		
		$focus = Focus::create ( [ 
				'name' => '',
				'status' => 1,
				'user_id' => \Auth::id (),
				'start_time' => $startTime,
				'end_time' => $endTime,
				'rest_status' => 1 
		] );
		
		$currentFocusInfo = $this->formatFocus ( $focus );
		
		return $currentFocusInfo;
	}
	
	/**
	 * 获取最近一个专注信息
	 * 
	 * @return \App\Services\unknown[]|\App\Services\number[]|\App\Models\Focus[]
	 */
	public function getRecentFormatFocus() {
		$focus = $this->focusRepository->getUserRecentFocus ( Auth::id () );
		return $this->formatFocus ( $focus );
	}
	
	/**
	 *
	 * @param unknown $focus        	
	 * @return unknown[]|number[]|\App\Models\Focus[]
	 */
	public function formatFocus($focus) {
		
		// 1默认等待中 2进行中 3已经完成 4休息中 5休息结束
		$formatFocusStatus = Focus::STATUS_INIT;
		$formatFocusRemain = 0;
		
		$formatInfo = array (
				'active_focus' => $focus,
				'current_focus_status' => $formatFocusStatus,
				'current_focus_remain' => $formatFocusRemain 
		);
		
		// 如果没有最近专注，则直接返回此信息
		if (empty ( $focus )) {
			$formatInfo ['active_focus'] = new Focus ();
			return $formatInfo;
		}
		
		if ($focus->status == 3 || $focus->rest_status == 3 || $focus->rest_status == 2) {
			return $formatInfo;
		}
		
		if ($focus->status == 1) {
			$remain = strtotime ( $focus->end_time ) - time ();
			if ($remain > 0) {
				$formatInfo ['current_focus_remain'] = $remain;
				$formatInfo ['current_focus_status'] = Focus::STATUS_PROCESSING;
				return $formatInfo;
			} else {
				$formatInfo ['current_focus_status'] = Focus::STATUS_FINISHED;
				return $formatInfo;
			}
		} else if ($focus->status == 2 && $focus->rest_status == 1) {
			$restRemain = strtotime ( $focus->rest_end_time ) - time ();
			if ($restRemain > 0) {
				$formatInfo ['current_focus_remain'] = $restRemain;
				$formatInfo ['current_focus_status'] = Focus::STATUS_RESTING;
				return $formatInfo;
			} else {
				$focus->update ( array (
						'rest_status' => 2 
				) );
			}
		}
		
		return $formatInfo;
	}
	
	/**
	 * 保存专注信息
	 * 
	 * @param unknown $focus        	
	 * @param unknown $name        	
	 * @return number[]|unknown[]|\App\Models\Focus[]
	 */
	public function store($focus, $name) {
		// 获取当前用户设置
		$user = \Auth::user ();
		$setting = $user->setting;
		
		$pomoConfig = $setting->getPomoConfigValues ();
		$focusIntervalTime = ! empty ( $pomoConfig['rest_minutes'] ) ? $pomoConfig['rest_minutes'] * 60 : Focus::DEFAULT_REST_INTERVAL;
		$currentTime = time ();
		$startTime = date ( 'Y-m-d H:i:s', $currentTime );
		$endTime = date ( 'Y-m-d H:i:s', time () + $focusIntervalTime );
		
		$focus->update ( [ 
				'name' => $name,
				'status' => 2,
				'rest_start_time' => $startTime,
				'rest_end_time' => $endTime 
		] );
		
		$this->journalService->storeJournal ( 3, $focus->name, $focus->start_time, $focus->end_time );
		
		return $this->getRecentFormatFocus ();
	}
	
	/**
	 * 依据专注状态获取信息头提示
	 *
	 * @param unknown $focus_status        	
	 * @return number[]|string[]
	 */
	public function getTipInfo($focus_status) {
		$tipType = 0;
		$tipMessage = '';
		
		if ($focus_status == Focus::STATUS_FINISHED) {
			$tipType = 1;
			$tipMessage = '您已经完成了一个专注，快来记录一下吧~';
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
	 * 定时任务-每日开始专注提醒
	 */
	public function scheduleFocusDailyReminder($type) {
		// 1 上午专注提醒 2下午专注提醒
		if ($type == 1) {
			$date = date ( 'Y-m-d 00:00:00' );
		} else if ($type == 2) {
			$date = date ( 'Y-m-d 12:00:00' );
		}
		
		$users = User::where ( 'last_login', '>', date ( 'Y-m-d H:i:s', strtotime ( $date ) - 3 * 24 * 60 * 60 ) )->get ();
		
		foreach ( $users as $user ) {
			if ($this->focusRepository->existNewFocusByAfterStartTime ( $date, $user->id )) {
				continue;
			}
			app(NotificationChannelService::class)->sendToUser($user, '专注提醒', '快来开始新的专注吧~', config('app.url'));
		}
	}
	
	/**
	 * 定时任务-提醒记录专注
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function scheduleFocusRecordReminder($startTime, $endTime) {
		$focus_datas = $this->focusRepository->getAllListByBetweenEndTime ( $startTime, $endTime );
		foreach ( $focus_datas as $focus ) {
			app(NotificationChannelService::class)->sendToUser($focus->user, '专注提醒', '您已经完成了一个专注，快来记录一下吧~', config('app.url'));
		}
	}
	
	/**
	 * 定时任务-休息后提醒开启新专注
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function scheduleFocusRestedReminder($startTime, $endTime) {
        $focus_datas = $this->focusRepository->getAllListByRestBetweenEndTime ( $startTime, $endTime );
		foreach ( $focus_datas as $focus ) {
			if ($this->focusRepository->existNewFocusById ( $focus->id, $focus->user_id )) {
				continue;
			}
			app(NotificationChannelService::class)->sendToUser($focus->user, '专注提醒', '休息完成，快来开始下一个专注吧~', config('app.url'));
		}
	}
}
