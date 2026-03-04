<?php

namespace App\Support;

class AuthContext
{
    const TYPE_GUEST = 'guest';
    const TYPE_SESSION = 'session';
    const TYPE_PERSONAL_TOKEN = 'pat';
    const TYPE_USER_TOKEN = 'user_token';

    public $authType;
    public $userId;
    public $user;
    public $tokenId;
    public $capabilities;

    public function __construct($authType, $user = null, $tokenId = null, array $capabilities = array())
    {
        $this->authType = $authType;
        $this->user = $user;
        $this->userId = $user ? $user->id : null;
        $this->tokenId = $tokenId;
        $this->capabilities = $capabilities;
    }

    public static function guest()
    {
        return new self(self::TYPE_GUEST, null, null, array());
    }

    public function hasCapability($capability)
    {
        if (in_array('*', $this->capabilities, true)) {
            return true;
        }

        return in_array($capability, $this->capabilities, true);
    }
}
