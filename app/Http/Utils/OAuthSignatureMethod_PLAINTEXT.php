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
class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod {
	public function get_name() {
		return "PLAINTEXT";
	}

	public function build_signature($request, $consumer, $token) {
		$sig = array(
				OAuthUtil::urlencode_rfc3986($consumer->secret)
		);

		if ($token) {
			array_push($sig, OAuthUtil::urlencode_rfc3986($token->secret));
		} else {
			array_push($sig, '');
		}

		$raw = implode("&", $sig);
		// for debug purposes
		$request->base_string = $raw;

		return OAuthUtil::urlencode_rfc3986($raw);
	}
}