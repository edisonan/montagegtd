<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * 首页控制器
 *
 * @author edison.an
 *        
 */
class IndexController extends Controller {
	
	/**
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware ( 'auth' );
	}
	
	/**
	 * 首页信息展示
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		return view ( 'index.index' );
	}

	/**
	 * Legacy test endpoint (kept to avoid route 500).
	 */
	public function test(Request $request) {
		return $this->jsonResponse($request, array(
			'code' => 9999,
			'msg' => 'ok',
			'result' => array('path' => '/index/test')
		));
	}
}
