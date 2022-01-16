<?php

namespace App\Repositories;

use App\Models\OauthInfo;

/**
 * Oauth Repository
 *
 * @author edison.an
 *        
 */
class OauthInfoRepository {
	
	/**
	 * 获取某用户Oauth所有账户信息列表
	 * 
	 * @param int $userId
	 *        	用户id
	 * @return unknown
	 */
	public function getOauthInfoListByUserId($userId) {
		return OauthInfo::where ( 'user_id', $userId )->orderBy ( 'id', 'desc' )->get ();
	}
	
	/**
	 * 根据第三方uid和第三方类型获取Oauth信息
	 * 
	 * @param string $thirdUid
	 *        	第三方用户id
	 * @param string $driver
	 *        	类型
	 */
	public function getByThirdUidAndDriver(string $thirdUid, string $driver) {
		return OauthInfo::where ( 'third_uid', $thirdUid )->where ( 'driver', $driver )->orderBy ( 'id', 'desc' )->first ();
	}
}
