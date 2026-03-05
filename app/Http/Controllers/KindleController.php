<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\SettingService;
use App\Services\KindleService;

/**
 * Kindle相关控制器
 *
 * @author edison.an
 *        
 */
class KindleController extends Controller {
	
	/**
	 * SettingService 实例.
	 *
	 * @var SettingService
	 */
	protected $settingService;
	
	/**
	 * KindleService 实例.
	 *
	 * @var KindleService
	 */
	protected $kindleService;
	
	/**
	 * 构造方法
	 *
	 * @param SettingService $settingService        	
	 * @return void
	 */
	public function __construct(SettingService $settingService, KindleService $kindleService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome' 
				] 
		] );
		
		$this->settingService = $settingService;
		$this->kindleService = $kindleService;
	}
	
	/**
	 * 首页
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		return view ( 'kindles.index', [ ] );
	}
	
	/**
	 * 测试kindle邮箱
	 *
	 * @param Request $request        	
	 */
	public function test(Request $request) {
		$this->kindleService->test ();
		echo 'success!';
		exit ();
	}
}
