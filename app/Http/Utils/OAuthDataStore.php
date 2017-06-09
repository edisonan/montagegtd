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
class OAuthDataStore {
	function lookup_consumer($consumer_key) {
		// implement me
	}

	function lookup_token($consumer, $token_type, $token) {
		// implement me
	}

	function lookup_nonce($consumer, $token, $nonce, $timestamp) {
		// implement me
	}

	function new_request_token($consumer) {
		// return a new token attached to this consumer
	}

	function new_access_token($token, $consumer) {
		// return a new access token attached to this consumer
		// for the user associated with this token if the request token
		// is authorized
		// should also invalidate the request token
	}

}