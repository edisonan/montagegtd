<?php

namespace App\Services;

use App\Http\Utils\OAuth1\OAuth;
use App\Http\Utils\OAuth1\FFClient;
use Illuminate\Support\Facades\Session;
use App\Models\Third;
use Illuminate\Support\Facades\Log;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;
use App\Http\Requests\Request;
use App\Repositories\ThirdRepository;

/**
 * 第三方服务业务逻辑
 *
 * @author edison.an
 *        
 */
class ThirdService {
	
	/**
	 *
	 * @var ThirdRepository
	 */
	protected $thirdRepository;
	
	/**
	 *
	 * @param ThirdRepository $thirdRepository        	
	 */
	public function __construct(ThirdRepository $thirdRepository) {
		$this->thirdRepository = $thirdRepository;
	}
	
	/**
	 * 饭否oauth登录请求地址
	 *
	 * @param Request $request        	
	 * @return string
	 */
	public function fanfouRequest($request) {
		$oauth = new OAuth ( config ( "services.fanfou.client_id" ), config ( "services.fanfou.client_secret" ) );
		
		$keys = $oauth->getRequestToken ();
		$url = $oauth->getAuthorizeURL ( $keys ['oauth_token'], false, config ( "services.fanfou.redirect" ) );
		
		$request->session ()->put ( 'thirdFanfouTempKeys', $keys );
		
		return $url;
	}
	
	/**
	 * 饭否oauth登录回调地址
	 *
	 * @param Request $request        	
	 */
	public function fanfouCallback($request) {
		
		// 获取access_token
		$tempKeys = $request->session ()->get ( 'thirdFanfouTempKeys' );
		
		$oauth = new OAuth ( config ( "services.fanfou.client_id" ), config ( "services.fanfou.client_secret" ), $tempKeys ['oauth_token'], $tempKeys ['oauth_token_secret'] );
		$lastKey = $oauth->getAccessToken ( $tempKeys ['oauth_token'] );
		
		// 创造一个新的请求
		$ffClient = new FFClient ( config ( "services.fanfou.client_id" ), config ( "services.fanfou.client_secret" ), $lastKey ['oauth_token'], $lastKey ['oauth_token_secret'] );
		$result = $ffClient->verify_credentials ();
		$resultArr = json_decode ( $result, true );
		
		$third = $this->thirdRepository->getUserThirdByThirdIdAndSource ( $request->user ()->id, $resultArr ['id'], Third::SOURCE_FANFOU );
		if (empty ( $third )) {
			$request->user ()->thirds ()->create ( [ 
					'third_id' => $resultArr ['id'],
					'third_name' => $resultArr ['name'],
					'token' => $last_key ['oauth_token'],
					'token_value' => $last_key ['oauth_token'],
					'token_secret' => $last_key ['oauth_token_secret'],
					'source' => 'fanfou' 
			] );
		} else {
			$third->update ( [ 
					'third_name' => $result_arr ['name'],
					'token' => $last_key ['oauth_token'],
					'token_value' => $last_key ['oauth_token'],
					'token_secret' => $last_key ['oauth_token_secret'] 
			] );
		}
	}
	
	/**
	 * 测试发送饭否消息
	 * 
	 * @param Request $request        	
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function testFave($user, $message) {
		$third = $this->thirdRepository->getUserThirdBySource ( $user->id, Third::SOURCE_FANFOU );
		if (empty ( $third )) {
			throw new CustomException ( ResponseDataUtil::getMessage ( ResponseDataUtil::THIRD_NOT_EXSIT ) );
		}
		return $this->fanfouFave ( $message, $third ['token_value'], $third ['token_secret'] );
	}
	
	/**
	 * 发消息到饭否
	 * 
	 * @param unknown $third        	
	 * @param unknown $message        	
	 */
	private function fanfouFave($message, $tokenValue, $tokenSecret) {
		$ffClient = new FFClient ( config ( "services.fanfou.client_id" ), config ( "services.fanfou.client_secret" ), $tokenValue, $tokenSecret );
		if (is_array ( $message )) {
			foreach ( $message as $msg ) {
				$result = $ffClient->update ( $msg );
			}
		} else {
			$result = $ffClient->update ( $message );
		}
		return $result;
	}
	
	/**
	 * 定时发饭否
	 * FANFOU_ID
	 * FANFOU_MESSAGE
	 *
	 * @return NULL
	 */
	public function sceduleFanfouFave() {
		$third = $this->thirdRepository->getThirdByThirdId ( env ( 'FANFOU_ID' ) );
		if (empty ( $third )) {
			Log::info ( "not found third info for " . env ( 'FANFOU_ID' ) );
			return;
		}
		
		$messageArr = explode ( '|', env ( 'FANFOU_MESSAGE' ) );
		return $this->fanfouFave ( $messageArr, $third ['token_value'], $third ['token_secret'] );
	}
}
