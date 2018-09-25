<?php

namespace App\Services;

use App\Repositories\OauthInfoRepository;
use App\Models\User;

/**
 * 账户管理Controller
 * @author edison.an
 *
 */
class AccountService {
	
	/**
	 * The oauthInfo repository instance.
	 *
	 * @var OauthInfoRepository
	 */
	protected $oauths;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param OauthInfoRepository $oauths        	
	 * @return void
	 */
	public function __construct(OauthInfoRepository $oauths) {
		$this->oauths = $oauths;
	}
	
	/**
	 * 获取某用户Oauth账户信息
	 *
	 */
	public function getOauthInfos(User $user) {
		
		$oauths = array (
				'github' => array (),
				'weibo' => array () 
		);
		
		$oauthinfos = $this->oauths->forUser ( $user, false );
		foreach ( $oauthinfos as $oauthinfo ) {
			$oauths [$oauthinfo->driver] = array (
					'expire' => $oauthinfo->expire 
			);
		}
		
		return $oauths;
	}
}
