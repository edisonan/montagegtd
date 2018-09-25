<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountService;

/**
 * 账户管理Controller
 * @author edison.an
 *
 */
class AccountController extends Controller {
	
	/**
	 * The AccountService instance.
	 *
	 * @var AccountService
	 */
	protected $accountService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param OauthInfoRepository $tasks        	
	 * @return void
	 */
	public function __construct(AccountService $accountService) {
		$this->middleware ( 'auth' );
		
		$this->accountService = $accountService;
	}
	
	/**
	 * 用户Oauth账户信息列表
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		return view ( 'accounts.index', [ 
				'oauths' => $this->accountService->getOauthInfos($request->user ()) 
		] );
	}
}
