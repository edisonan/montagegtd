<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository {
	/**
	 * 获取该用户的设置信息
	 * 
	 * @param int $userId        	
	 */
	public function getUserSettingInfo(int $userId) {
		return Setting::where ( 'user_id', $userId )->first ();
	}
	
	/**
	 * 通过日历token获取设置信息
	 * 
	 * @param string $calToken        	
	 * @return unknown
	 */
	public function getSettingInfoByCalToken(string $calToken) {
		return Setting::where ( 'cal_token', $calToken )->first ();
	}
	
	/**
	 * 获取开启kindle推送的设置信息列表
	 */
	public function getStartKindleAllList() {
		return Setting::where ( 'is_start_kindle', 1 )->get ();
	}
	
	// /**
	// * Get all of the notes for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user) {
	// return Setting::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'desc' )->first ();
	// }
	
	// /**
	// * Get Setting By cal_token
	// *
	// * @param string $cal_token
	// */
	// public function forCalToken(string $cal_token) {
	// return Setting::where ( 'cal_token', $cal_token )->first ();
	// }
	
	// /**
	// * Get all of the notes for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forStatus($status) {
	// return Setting::where ( 'status', $status )->orderBy ( 'created_at', 'desc' )->get ();
	// }
	// public function getStartList() {
	// return Setting::where ( 'is_start_kindle', 1 )->get ();
	// }
}
