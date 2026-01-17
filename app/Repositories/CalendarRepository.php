<?php

namespace App\Repositories;

use App\Models\Calendar;

class CalendarRepository {
	
	/**
	 * 根据主题和状态获取日历列表
	 * 
	 * @param string $theme        	
	 * @param string $status        	
	 * @return unknown
	 */
	public function getListByThemeAndStatus(string $theme, string $status) {
		return Calendar::where ( 'status', $status )->where ( 'theme', $theme )->orderBy ( 'id', 'asc' )->get ();
	}
}
