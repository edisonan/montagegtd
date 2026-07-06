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
		return view ( 'settings.index', [ ] );
	}
	
	/**
	 * 更新.
	 *
	 * @param Request $request        	
	 * @param Setting $setting        	
	 */
	public function update(Request $request, Setting $setting) {
		$this->authorize ( 'destroy', $setting );
		$this->normalizePomoSettingKeys($request, $setting);
		
		$this->validate ( $request, [ 
				'pomo_config' => 'array',
				'pomo_config.day_goal' => 'integer|min:1',
				'pomo_config.week_goal' => 'integer|min:1',
				'pomo_config.month_goal' => 'integer|min:1',
				'pomo_config.focus_minutes' => 'integer|min:10|max:60',
				'pomo_config.rest_minutes' => 'integer|min:1|max:10',
				'is_start_kindle' => 'integer|min:0|max:1',
				'with_image_push' => 'integer|min:0|max:1',
		] );
		
		if($request->is_start_kindle == 1) {
			$this->validate ( $request, [
					'kindle_email' => 'email'
			] );
		}
		
		if ($request->has('pomo_config')) {
			$request->merge([
					'pomo_config' => Setting::normalizePomoConfig($request->input('pomo_config'))
			]);
		}
		$setting->update ( $request->all () );
		
		$redirectPage = '/settings';
		if ($request->has ( 'page_info' ) && $request->page_info == 'kindle_page') {
			$redirectPage = '/kindles';
		}
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), $redirectPage );
	}

	protected function normalizePomoSettingKeys(Request $request, Setting $setting) {
		$config = $setting->getPomoConfigValues();
		$hasConfig = false;
		if ($request->has('pomo_config')) {
			$submitted = $request->input('pomo_config');
			if (!is_array($submitted)) {
				return;
			}
			$config = array_merge($config, $submitted);
			$hasConfig = true;
		}

		$legacyKeys = [
				'day_pomo_goal' => 'day_goal',
				'week_pomo_goal' => 'week_goal',
				'month_pomo_goal' => 'month_goal',
				'pomo_time' => 'focus_minutes',
				'pomo_rest_time' => 'rest_minutes',
				'week_focus_plan' => 'week_goal',
				'month_focus_plan' => 'month_goal'
		];
		foreach ($legacyKeys as $legacyKey => $configKey) {
			if ($request->has($legacyKey)) {
				$config[$configKey] = $request->input($legacyKey);
				$hasConfig = true;
			}
		}

		if ($hasConfig) {
			$request->merge(['pomo_config' => $config]);
		}
	}
}
