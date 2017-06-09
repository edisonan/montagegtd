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
class OAuthConsumer {
	public $key;
	public $secret;

	function __construct($key, $secret) {
		$this->key = $key;
		$this->secret = $secret;
	}

	function __toString() {
		return "OAuthConsumer[key=$this->key,secret=$this->secret]";
	}
}