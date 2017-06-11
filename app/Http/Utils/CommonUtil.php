<?php
namespace App\Http\Utils;

class CommonUtil{
	public static function page_title($url) {
        $fp = @file_get_contents($url);
        if (!$fp) {
        	\Log::info("can not open".$url);
        	return '';
        }

        $res = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
        if (!$res) {
        	\Log::info("can not preg".$url);
        	return '';
        }
            

        // Clean up title: remove EOL's and excessive whitespace.
        $title = preg_replace('/\s+/', ' ', $title_matches[1]);
        $title = trim($title);
        return $title;
    }
    
    public static function isUrl($s)
    {
    	return preg_match('/^http[s]?:\/\/'.
    			'(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184
    			'|'. // 允许IP和DOMAIN（域名）
    			'([0-9a-z_!~*\'()-]+\.)*'. // 三级域验证- www.
    			'([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域验证
    			'[a-z]{2,6})'.  // 顶级域验证.com or .museum
    			'(:[0-9]{1,4})?'.  // 端口- :80
    			'((\/\?)|'.  // 如果含有文件对文件部分进行校验
    			'(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/',
    			$s) == 1;
    }
}