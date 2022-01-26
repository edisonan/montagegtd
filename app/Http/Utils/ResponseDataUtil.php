<?php

namespace App\Http\Utils;

/**
 * 响应数据工具类
 *
 * @author edison.an
 *
 */
class ResponseDataUtil
{
    const OK = 9999;
    const SYSTEM_ERROR = 1000;
    const COMMON_ERROR = 1001;
    const THIRD_NOT_EXSIT = 2100;

    /**
     * 生成成功响应数据
     *
     * @param string $message
     * @param array $result
     * @return string[]|unknown[]
     */
    public static function genSucc($message, $result = array())
    {
        $response = array();
        $response ['code'] = self::OK;
        $response ['msg'] = $message ? $message : self::getMessage(self::OK);
        $response ['result'] = $result;
        return $response;
    }

    /**
     * 根据code获取message信息
     *
     * @param int $code
     * @return string
     */
    public static function getMessage($code)
    {
        $arr = array(
            self::OK => '操作成功',
            self::SYSTEM_ERROR => '系统异常',
            self::COMMON_ERROR => '操作异常',
            self::THIRD_NOT_EXSIT => '查询第三方服务记录不存在'
        );
        return isset ($arr [$code]) ? $arr [$code] : 'unkonw error:' . $code;
    }

    /**
     * 生成成功响应数据 (只上送result场景使用)
     *
     * @param array $result
     * @return string[]|unknown[]
     */
    public static function genSimpleSucc($result = array())
    {
        $response = array();
        $response ['code'] = self::OK;
        $response ['msg'] = self::getMessage(self::OK);
        $response ['result'] = $result;
        return $response;
    }

    /**
     * 生成失败响应数据（上送code、messsage、result场景）
     *
     * @param int $code
     * @param string $message
     * @param array $result
     * @return unknown[]|string[]
     */
    public static function genFail($code, $message, $result = array())
    {
        $response = array();
        $response ['code'] = $code;
        $response ['msg'] = $message ? $message : self::getMessage($code);
        $response ['result'] = $result;
        return $response;
    }

    /**
     * 生成失败响应数据（只上送code、result场景）
     *
     * @param int $code
     * @param array $result
     * @return unknown[]|string[]
     */
    public static function genSimpleFail($code, $result = array())
    {
        $response = array();
        $response ['code'] = $code;
        $response ['msg'] = self::getMessage($code);
        $response ['result'] = $result;
        return $response;
    }

    /**
     * 生成失败响应数据（只上送messsage、result场景）
     *
     * @param string $message
     * @param array $result
     * @return string[]|unknown[]
     */
    public static function genCommonFail($message, $result = array())
    {
        $response = array();
        $response ['code'] = self::COMMON_ERROR;
        $response ['msg'] = $message ? $message : self::getMessage($code);
        $response ['result'] = $result;
        return $response;
    }
}