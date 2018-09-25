<?php

namespace App\Services;

use App\Repositories\OauthInfoRepository;
use App\Models\User;

/**
 * 账号管理Service
 * @author edison.an
 *
 */
class AccountService {
	
	/**
	 * OauthInfoRepository 实例
	 *
	 * @var OauthInfoRepository
	 */
	protected $oauths;
	
	/**
	 * 构造方法
	 *
	 * @param OauthInfoRepository $oauths        	
	 * @return void
	 */
	public function __construct(OauthInfoRepository $oauths) {
		$this->oauths = $oauths;
	}
	
	/**
	 * 获取某用户Oauth账户信息
	 * @param User $user
	 * @return NULL[][]
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
