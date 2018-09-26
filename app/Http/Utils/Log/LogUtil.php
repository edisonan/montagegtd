<?php
namespace App\Http\Utils\Log;

class LogUtil
{
    // 请求token
    public static $requestToken;

    /**
     * 获取请求token
     *
     */
    public static function getRequestToken()
    {
        if (! self::$requestToken) {
            self::$requestToken = substr($_SERVER['SERVER_ADDR'], - 3) . '-' . uniqid();
        }
        
        return self::$requestToken;
    }

    /**
     * 重置请求token
     *
     */
    public static function resetRequestToken()
    {
        self::$requestToken = substr($_SERVER['SERVER_ADDR'], - 3) . '-' . uniqid();
    }
}