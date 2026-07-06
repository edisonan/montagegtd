<?php

namespace App\Services;

use App\Models\Setting;
use App\Repositories\SettingRepository;

/**
 * 设置业务逻辑
 *
 * @author edison.an
 *        
 */
class SettingService {
	/**
	 *
	 * @var SettingRepository
	 */
	protected $settingRepository;
	
	/**
	 *
	 * @param SettingRepository $settingRepository        	
	 */
	public function __construct(SettingRepository $settingRepository) {
		$this->settingRepository = $settingRepository;
	}
	
	/**
	 * 获取用户设置信息
	 * 
	 * @param string $needReturnEmpty
	 *        	如果设置为空是否进行创建
	 * @return \App\Models\Setting
	 */
	public function getSettingInfo($needReturnEmpty = false) {
		$userId = \Auth::id ();
		$settingInfo = $this->settingRepository->getUserSettingInfo ( $userId );
		
		if ($needReturnEmpty && empty ( $settingInfo )) {
			$settingInfo = $this->createDefaultSetting ( $userId );
		}
		return $settingInfo;
	}
	
	/**
	 * 获取开启kindle推送的设置信息列表
	 */
	public function getStartKindleAllList() {
		return $this->settingRepository->getStartKindleAllList ();
	}
	
	/**
	 * 创建用户默认设置信息
	 * 
	 * @param unknown $userId
	 *        	用户id
	 * @return \App\Models\Setting
	 */
	public function createDefaultSetting($userId) {
		$setting = new Setting ();
		$setting->user_id = $userId;
		$setting->pomo_config = Setting::defaultPomoConfig ();
		$setting->save ();
		return $setting;
	}
}
