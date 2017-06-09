<?php
namespace App\Http\Utils;

use App\Http\Utils\OAuth;
use App\Http\Utils\OAuthConsumer;
use App\Http\Utils\OAuthDataStore;
use App\Http\Utils\OAuthNewException;
use App\Http\Utils\OAuthRequest;
use App\Http\Utils\OAuthServer;
use App\Http\Utils\OAuthSignatureMethod_HMAC_SHA1;
use App\Http\Utils\OAuthSignatureMethod_PLAINTEXT;
use App\Http\Utils\OAuthSignatureMethod_RSA_SHA1;
use App\Http\Utils\OAuthSignatureMethod;
use App\Http\Utils\OauthToken;
use App\Http\Utils\OAuthUtil;

/**
 * @ignore
 */
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {
	function get_name() {
		return "HMAC-SHA1";
	}

	public function build_signature($request, $consumer, $token) {
		$base_string = $request->get_signature_base_string();

		//print_r( $base_string );
		$request->base_string = $base_string;

		$key_parts = array(
				$consumer->secret,
				($token) ? $token->secret : ""
		);

		//print_r( $key_parts );
		$key_parts = OAuthUtil::urlencode_rfc3986($key_parts);


		$key = implode('&', $key_parts);

		return base64_encode(hash_hmac('sha1', $base_string, $key, true));
	}
}