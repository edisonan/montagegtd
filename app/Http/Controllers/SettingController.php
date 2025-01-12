<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\SettingService;
use App\Http\Utils\ResponseDataUtil;

/**
 * 设置控制器
 *
 * @author edison.an
 *        
 */
class SettingController extends Controller {
	
	/**
	 * The settings service instance.
	 *
	 * @var SettingService
	 */
	protected $settingService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param SettingService $settingService        	
	 * @return void
	 */
	public function __construct(SettingService $settingService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome' 
				] 
		] );
		
		$this->settingService = $settingService;
	}
	
	/**
	 * 首页.
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		$page_params = array ();
		
		$setting = $this->settingService->getSettingInfo ( true );
		
		return view ( 'settings.index', [ 
				'setting' => $setting 
		] );
	}
	
	/**
	 * 更新.
	 *
	 * @param Request $request        	
	 * @param Setting $setting        	
	 */
	public function update(Request $request, Setting $setting) {
		$this->authorize ( 'destroy', $setting );
		
		$this->validate ( $request, [ 
				'day_pomo_goal' => 'integer|min:1',
				'week_pomo_goal' => 'integer|min:1',
				'month_pomo_goal' => 'integer|min:1',
				'pomo_time' => 'integer|min:10|max:60',
				'pomo_rest_time' => 'integer|min:1|max:10',
				'is_start_kindle' => 'integer|min:0|max:1',
				'with_image_push' => 'integer|min:0|max:1',
		] );
		
		if($request->is_start_kindle == 1) {
			$this->validate ( $request, [
					'kindle_email' => 'email'
			] );
		}
		
		$setting->update ( $request->all () );
		
		$redirectPage = '/settings';
		if ($request->has ( 'page_info' ) && $request->page_info == 'kindle_page') {
			$redirectPage = '/kindles';
		}
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), $redirectPage );
	}
}
