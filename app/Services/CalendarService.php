<?php

namespace App\Services;

use App\Http\Utils\ICSUtil;
use App\Models\User;
use App\Repositories\CalendarRepository;
use App\Repositories\SettingRepository;
use App\Repositories\TaskRepository;
use App\Exceptions\CustomException;
use App\Http\Utils\CommonUtil;
use Illuminate\Support\Facades\Auth;

/**
 * 日历订阅相关Service
 *
 * @author edison.an
 *        
 */
class CalendarService {
	/**
	 *
	 * @var CalendarRepository
	 */
	protected $calendarRepository;
	
	/**
	 *
	 * @var SettingRepository
	 */
	protected $settingRepository;
	
	/**
	 *
	 * @var TaskRepository
	 */
	protected $taskRepository;
	
	/**
	 *
	 * @param CalendarRepository $calRepository
	 * @param SettingRepository $settingRepository        	
	 * @param TaskRepository $taskRepository        	
	 */
	public function __construct(CalendarRepository $calRepository, SettingRepository $settingRepository, TaskRepository $taskRepository) {
		$this->calendarRepository = $calRepository;
		$this->settingRepository = $settingRepository;
		$this->taskRepository = $taskRepository;
	}
	
	/**
	 * 获取个人日历订阅地址
	 *
	 * @param User $user        	
	 * @return string
	 */
	public function getPersonCalUrl() {
		$userId = Auth::id ();
		
		$setting = $this->settingRepository->getUserSettingInfo ( $userId );
		if (isset ( $setting ['cal_token'] ) && ! empty ( $setting ['cal_token'] )) {
			$calToken = $setting ['cal_token'];
		} else {
			$calToken = md5 ( $userId . '_' . time () );
			$setting->update ( array (
					'cal_token' => $calToken 
			) );
		}
		
		$host = CommonUtil::getUrlHost ( config ( 'app.url' ) );
		return 'webcal://' . $host . '/taskics/' . $calToken;
	}
	
	/**
	 * 根据主题获取相关日历订阅
	 *
	 * @param User $user        	
	 * @return string
	 */
	public function getIcsByTheme($theme) {
		date_default_timezone_set ( "Asia/Shanghai" );
		
		$cals = $this->calendarRepository->getListByThemeAndStatus ( $theme, 1 );
		
		$taskProps = array ();
		foreach ( $cals as $cal ) {
			$taskProps [] = array (
					'description' => $cal->desc,
					'dtend' => $cal->dtend,
					'dtstart' => $cal->dtstart,
					'location' => $cal->location,
					'summary' => $cal->summary,
					'url' => $cal->url 
			);
		}
		
		$ics = new ICSUtil ( $taskProps );
		$ics->cal_name = $theme;
		$ics_file_contents = $ics->to_string ();
		
		// TODO 换用封装方式存储 方便后续更换为S3等地方
		$file_name = 'task_ics_' . md5 ( $theme );
		$this->tryWriteIcsFile($file_name, $ics_file_contents);
		
		return array (
				'file_name' => $file_name,
				'file_content' => $ics_file_contents 
		);
	}
	
	/**
	 * 根据日历token获取个人日历订阅
	 *
	 * @param string $calToken        	
	 * @return string[]
	 */
	public function getIcsByCalToken($calToken) {
		date_default_timezone_set ( "Asia/Shanghai" );
		
		// 获取该用户user_id
		$setting = $this->settingRepository->getSettingInfoByCalToken ( $calToken );
		if (! empty ( $setting )) {
			$userId = $setting ['user_id'];
		} else {
			throw new CustomException ( "错误的日历token" );
		}
		
		// 根据开始时间和结束时间，查询需要提醒内容
		$startTime = date ( 'Y-m-d H:i:s', time () - 15768000 );
		$endTime = date ( 'Y-m-d H:i:s', strtotime ( $startTime ) + 31536000 );
		$tasks = $this->taskRepository->getListByRemindTime ( $userId, $startTime, $endTime );
		
		$taskProps = array ();
		foreach ( $tasks as $task ) {
			$taskProps [] = array (
					'dtend' => $task->remindtime,
					'dtstart' => $task->remindtime,
					'due' => $task->remindtime,
					'completed' => $task->remindtime,
					'summary' => $task->name,
					'repeat' => 1,
					'status' => 'NEEDS-ACTION' 
			);
		}
		
		$ics = new ICSUtil ( $taskProps );
		$ics_file_contents = $ics->to_string ();
		
		// TODO 换用封装方式存储 方便后续更换为S3等地方
		$this->tryWriteIcsFile('task_ics_' . $userId, $ics_file_contents);
		
		return array (
				'file_name' => "task_ics_" . $userId,
				'file_content' => $ics_file_contents 
		);
	}

	/**
	 * 尝试将ics文件写入本地，失败时仅忽略（API仍可直接返回file_content）
	 *
	 * @param string $fileName
	 * @param string $content
	 */
	private function tryWriteIcsFile($fileName, $content) {
		$storagePath = rtrim(config("app.storage_path"), '/');
		if (empty($storagePath)) {
			return;
		}

		$fullPath = $storagePath . '/' . $fileName;
		$dir = dirname($fullPath);
		if (!is_dir($dir)) {
			@mkdir($dir, 0777, true);
		}

		@file_put_contents($fullPath, $content);
	}
}
