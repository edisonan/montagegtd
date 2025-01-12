<?php

namespace App\Services;

use App\Models\OauthInfo;
use Illuminate\Support\Facades\Auth;
use App\Repositories\OauthInfoRepository;

/**
 * 账号管理业务逻辑
 *
 * @author edison.an
 *        
 */
class AccountService {
	
	/**
	 * AccountService 实例.
	 *
	 * @var OauthInfoRepository
	 */
	protected $oauthInfoRepository;
	
	/**
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct(OauthInfoRepository $oauthInfoRepository) {
		$this->oauthInfoRepository = $oauthInfoRepository;
	}
	
	/**
	 * 获取某用户Oauth所有账户信息列表
	 *
	 * @return NULL[][]
	 */
	public function getOauthInfos() {
		$oauths = array (
				'github' => array (),
				'weibo' => array () 
		);
		
		$oauthInfos = $this->oauthInfoRepository->getOauthInfoListByUserId ( Auth::id () );
		foreach ( $oauthInfos as $oauthInfo ) {
			$oauths [$oauthInfo->driver] = array (
					'expire' => $oauthInfo->expire 
			);
		}
		
		return $oauths;
	}
	
	/**
	 * 根据第三方用户信息和类型获取Oatuth账户信息
	 *
	 * @param string $thirdUid
	 *        	第三方用户id
	 * @param string $driver
	 *        	类型
	 */
	public function forByThirdUidAndDriver(string $thirdUid, string $driver) {
		return $this->oauthInfoRepository->getByThirdUidAndDriver ( $thirdUid, $driver );
	}
}
