<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Services\HelpService;
use App\Http\Utils\ResponseDataUtil;

/**
 * 帮助控制器
 *
 * @author edison.an
 *        
 */
class HelpController extends Controller {
	/**
	 * HelpService 实例
	 *
	 * @var HelpService
	 */
	protected $helpService;
	
	/**
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct(HelpService $helpService) {
		$this->middleware ( 'auth' );
		
		$this->helpService = $helpService;
	}
	
	/**
	 * 反馈页
	 *
	 * @param Request $request        	
	 * @return
	 *
	 */
	public function feedback(Request $request) {
		return view ( 'help.feedback', [ 
				'from' => $request->input ( 'from', '' ) 
		] );
	}
	
	/**
	 * 提交反馈
	 *
	 * @param Request $request        	
	 * @return
	 *
	 */
	public function feedbackStore(Request $request) {
		$this->validate ( $request, [ 
				'content' => 'required' 
		] );
		
		$from = $request->input ( 'from', '' );
		$content = $request->content;
		
		$this->helpService->storeFeedback ( $from, $content );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/index' );
	}
}
